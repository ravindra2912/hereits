<?php

namespace App\Exports;

use App\Models\BusinessCustomer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;

class CustomersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $request;
    protected $businessId;
    protected $showCustomerGroup;
    protected $showOrders;
    protected $showQuotations;
    protected $showAppointments;

    public function __construct($request = null)
    {
        $this->request = $request;
        $this->businessId = getBusinessId();
        $businessSetting = getBusinessSettings($this->businessId);

        $user = Auth::user();
        $isBusiness = $user && $user->role === 'Business';

        // Conditional column visibility according to enabled modules and permissions
        $this->showCustomerGroup = !empty($businessSetting->is_offer_with_customer_group);

        $this->showOrders = !empty($businessSetting->is_ecommerce_system)
            && ($isBusiness || checkPosPermission('view_orders') || checkPosPermission('view_all_orders') || checkBusinessPermission('view_products'));

        $this->showQuotations = (!empty($businessSetting->is_ecommerce_system) || !empty($businessSetting->is_pos_access) || !empty($businessSetting->is_create_quotation_on_inquiry))
            && ($isBusiness || checkPosPermission('view_quotation') || checkPosPermission('view_all_quotations') || checkBusinessPermission('view_products'));

        $this->showAppointments = !empty($businessSetting->is_appointment_system)
            && ($isBusiness || checkBusinessPermission('view_appointments'));
    }

    public function query(): Builder|EloquentBuilder|Relation
    {
        $businessId = $this->businessId;
        $request = $this->request;

        $select = [
            'business_customers.id',
            'business_customers.business_id',
            'business_customers.customer_id',
            'business_customers.customer_role_id',
            'business_customers.business_name',
            'business_customers.customer_name',
            'business_customers.contact as customer_contact',
            'users.first_name',
            'users.last_name',
            'users.email',
            'users.contact as user_contact',
        ];

        if ($this->showCustomerGroup) {
            $select[] = 'business_customer_roles.role_name as customer_role_name';
        }

        $query = BusinessCustomer::query()
            ->where('business_customers.business_id', $businessId)
            ->leftJoin('users', 'users.id', '=', 'business_customers.customer_id');

        if ($this->showCustomerGroup) {
            $query->leftJoin('business_customer_roles', function ($join) use ($businessId) {
                $join->on('business_customer_roles.id', '=', 'business_customers.customer_role_id')
                    ->where('business_customer_roles.business_id', '=', $businessId);
            });
        }

        if ($this->showOrders) {
            $query->selectSub(function ($q) use ($businessId) {
                $q->from('orders')
                    ->selectRaw('COUNT(*)')
                    ->where('orders.business_id', $businessId)
                    ->whereNull('orders.deleted_at')
                    ->where(function ($sub) {
                        $sub->whereColumn('orders.business_customer_id', 'business_customers.id')
                            ->orWhere(function ($userSub) {
                                $userSub->whereNotNull('business_customers.customer_id')
                                    ->whereColumn('orders.customer_id', 'business_customers.customer_id');
                            });
                    });
            }, 'total_orders');

            $query->selectSub(function ($q) use ($businessId) {
                $q->from('orders')
                    ->selectRaw('MAX(orders.created_at)')
                    ->where('orders.business_id', $businessId)
                    ->whereNull('orders.deleted_at')
                    ->where(function ($sub) {
                        $sub->whereColumn('orders.business_customer_id', 'business_customers.id')
                            ->orWhere(function ($userSub) {
                                $userSub->whereNotNull('business_customers.customer_id')
                                    ->whereColumn('orders.customer_id', 'business_customers.customer_id');
                            });
                    });
            }, 'last_order_date');
        }

        if ($this->showQuotations) {
            $query->selectSub(function ($q) use ($businessId) {
                $q->from('quotations')
                    ->selectRaw('COUNT(*)')
                    ->where('quotations.business_id', $businessId)
                    ->whereNull('quotations.deleted_at')
                    ->where(function ($sub) {
                        $sub->whereColumn('quotations.business_customer_id', 'business_customers.id')
                            ->orWhere(function ($userSub) {
                                $userSub->whereNotNull('business_customers.customer_id')
                                    ->whereExists(function ($ex) {
                                        $ex->from('business_customers as bc_inner')
                                            ->whereColumn('bc_inner.id', 'quotations.business_customer_id')
                                            ->whereColumn('bc_inner.customer_id', 'business_customers.customer_id');
                                    });
                            });
                    });
            }, 'total_quotations');

            $query->selectSub(function ($q) use ($businessId) {
                $q->from('quotations')
                    ->selectRaw('MAX(quotations.created_at)')
                    ->where('quotations.business_id', $businessId)
                    ->whereNull('quotations.deleted_at')
                    ->where(function ($sub) {
                        $sub->whereColumn('quotations.business_customer_id', 'business_customers.id')
                            ->orWhere(function ($userSub) {
                                $userSub->whereNotNull('business_customers.customer_id')
                                    ->whereExists(function ($ex) {
                                        $ex->from('business_customers as bc_inner')
                                            ->whereColumn('bc_inner.id', 'quotations.business_customer_id')
                                            ->whereColumn('bc_inner.customer_id', 'business_customers.customer_id');
                                    });
                            });
                    });
            }, 'last_quotation_date');
        }

        if ($this->showAppointments) {
            $query->selectSub(function ($q) use ($businessId) {
                $q->from('appointment_bookings')
                    ->selectRaw('COUNT(*)')
                    ->where('appointment_bookings.business_id', $businessId)
                    ->whereNull('appointment_bookings.deleted_at')
                    ->whereNotNull('business_customers.customer_id')
                    ->whereColumn('appointment_bookings.user_id', 'business_customers.customer_id');
            }, 'total_appointments');
        }

        $query->addSelect($select);

        // Filter by group_id
        if ($request && $request->filled('group_id')) {
            if ($request->group_id === 'none') {
                $query->whereNull('business_customers.customer_role_id');
            } else {
                $query->where('business_customers.customer_role_id', $request->group_id);
            }
        }

        // Filter by search keyword
        if ($request && $request->filled('search')) {
            $keyword = trim($request->search);
            $query->where(function ($q) use ($keyword) {
                $q->where('business_customers.customer_name', 'like', "%{$keyword}%")
                    ->orWhere('business_customers.business_name', 'like', "%{$keyword}%")
                    ->orWhere('business_customers.contact', 'like', "%{$keyword}%")
                    ->orWhere('users.first_name', 'like', "%{$keyword}%")
                    ->orWhere('users.last_name', 'like', "%{$keyword}%")
                    ->orWhere('users.contact', 'like', "%{$keyword}%")
                    ->orWhere('users.email', 'like', "%{$keyword}%")
                    ->orWhereRaw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) LIKE ?", ["%{$keyword}%"]);
            });
        }

        return $query->orderBy('business_customers.id', 'desc');
    }

    public function headings(): array
    {
        $headings = [
            'Name',
            'Business Name',
            'Contact',
        ];

        if ($this->showCustomerGroup) {
            $headings[] = 'Group Name';
        }

        $headings[] = 'Email';

        if ($this->showOrders) {
            $headings[] = 'Total Order';
            $headings[] = 'Last Order Date';
        }

        if ($this->showQuotations) {
            $headings[] = 'Total Quotation';
            $headings[] = 'Last Quotation Date';
        }

        if ($this->showAppointments) {
            $headings[] = 'Total Appointment';
        }

        return $headings;
    }

    public function map(mixed $row): array
    {
        $name = $row->customer_name ?: trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
        $contact = $row->customer_contact ?: ($row->user_contact ?? '');

        $data = [
            $name ?: 'N/A',
            $row->business_name ?? '',
            $contact ?: '',
        ];

        if ($this->showCustomerGroup) {
            $data[] = $row->customer_role_name ?: 'No Group';
        }

        $data[] = $row->email ?? '';

        if ($this->showOrders) {
            $data[] = (int) ($row->total_orders ?? 0);
            $data[] = !empty($row->last_order_date) ? Carbon::parse($row->last_order_date)->format('d-m-Y') : '-';
        }

        if ($this->showQuotations) {
            $data[] = (int) ($row->total_quotations ?? 0);
            $data[] = !empty($row->last_quotation_date) ? Carbon::parse($row->last_quotation_date)->format('d-m-Y') : '-';
        }

        if ($this->showAppointments) {
            $data[] = (int) ($row->total_appointments ?? 0);
        }

        return $data;
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
