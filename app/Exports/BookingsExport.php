<?php

namespace App\Exports;

use App\Models\AppointmentBooking;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class BookingsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $request = $this->request;
        $query = AppointmentBooking::with(['department' => function ($q) {
            $q->select('id', 'department_name');
        }, 'expert' => function ($q) {
            $q->select('id', 'expert_name');
        }])
            ->where('appointment_bookings.business_id', getBusinessId());

        // Date Filters
        if ($request->filter_type == 'custom' && $request->filled('start_date')) {
            if ($request->filled('end_date')) {
                $query->whereBetween('booking_date', [$request->start_date, $request->end_date]);
            } else {
                $query->whereDate('booking_date', '>=', $request->start_date);
            }
        } else {
            if ($request->filled('date') && !$request->filled('filter_type')) {
                $query->whereDate('booking_date', $request->date);
            } else {
                $query->whereDate('booking_date', Carbon::today());
            }
        }

        // Other Filters
        if (isset($request->department_id) && !empty($request->department_id)) {
            $query->where('department_id', $request->department_id);
        }
        if (isset($request->expert_id) && !empty($request->expert_id)) {
            $query->where('expert_id', $request->expert_id);
        }
        if (isset($request->status) && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        return $query->orderBy('booking_date', 'desc');
    }

    public function headings(): array
    {
        return ['Token', 'Department', 'Expert', 'User Name', 'User Contact', 'Date', 'Time Slot', 'Status', 'Amount', 'Payment Type', 'Note', 'Expert Note'];
    }

    public function map($row): array
    {
        $timeSlot = '';
        if (!empty($row->slot_start_time)) {
            $timeSlot = Carbon::parse($row->slot_start_time)->format('h:i a');
            if (!empty($row->slot_end_time)) {
                $timeSlot .= ' - ' . Carbon::parse($row->slot_end_time)->format('h:i a');
            }
        }

        return [
            $row->token_number,
            $row->department ? $row->department->department_name : '',
            $row->expert ? $row->expert->expert_name : '',
            $row->user_name,
            $row->user_contact,
            $row->booking_date,
            $timeSlot,
            ucwords(str_replace('_', ' ', $row->status)),
            $row->amount,
            $row->payment_type,
            $row->note,
            $row->expert_note
        ];
    }
}
