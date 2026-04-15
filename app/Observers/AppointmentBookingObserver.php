<?php

namespace App\Observers;

use Carbon\Carbon;
use App\Models\Business;
use App\Models\Expert;
use App\Mail\TokenCancelledMail;
use App\Mail\TokenComplitedMail;
use App\Models\AppointmentBooking;
use App\Mail\TokenConfirmationMail;
use App\Jobs\PuhsNotificationToUser;
use Illuminate\Support\Facades\Mail;
use App\Mail\AppointmentCancelledMail;
use App\Mail\AppointmentComplitedMail;
use App\Mail\AppointmentConfirmationMail;
use App\Models\BusinessSetting;
use PHPUnit\Framework\TestStatus\Notice;

class AppointmentBookingObserver
{
    /**
     * Handle the AppointmentBooking "created" event.
     */
    public function created(AppointmentBooking $appointmentBooking): void
    {
        // dd($appointmentBooking->status);

        $appointment_details = AppointmentBooking::query()
            ->select('id', 'token_number', 'business_id', 'expert_id', 'user_id', 'user_name', 'user_contact', 'slot_start_time', 'slot_end_time', 'booking_date', 'note', 'status')
            ->with([
                'expert:id,expert_name,slug,is_appointment_book_with_time_slot',
                'business:id,name,slug,address',
                'user:id,first_name,email'
            ])
            ->find($appointmentBooking->id);

        if ($appointment_details && $appointment_details->user_id != null) {
            if ($appointment_details->expert->is_appointment_book_with_time_slot) {
                if ($appointment_details->status == 'pending') {
                } else if ($appointment_details->status == 'confirmed') {
                    Mail::to($appointment_details->user->email)->send(new AppointmentConfirmationMail($appointment_details));
                }
            } else {
                if ($appointment_details->status == 'pending') {
                } else if ($appointment_details->status == 'confirmed') {
                    Mail::to($appointment_details->user->email)->send(new TokenConfirmationMail($appointment_details));
                }
            }
        }

        // BusinessSetting::find($appointment_details->business_id)->decrement('credit', 1);
    }

    /**
     * Handle the AppointmentBooking "updated" event.
     */
    public function updated(AppointmentBooking $appointmentBooking): void
    {
        $insert = $appointmentBooking;
        $notification = '';

        //send mail
        if ($insert->wasChanged('status')) {
            $changes = $appointmentBooking->getChanges();
            if (in_array($changes['status'], ['confirmed', 'in_progress', 'completed', 'cancel'])) {
                $appointment_details = AppointmentBooking::query()
                    ->select('id', 'token_number', 'business_id', 'expert_id', 'user_id', 'user_name', 'user_contact', 'slot_start_time', 'slot_end_time', 'booking_date', 'note', 'status')
                    ->with([
                        'expert:id,expert_name,slug,is_appointment_book_with_time_slot',
                        'business:id,name,slug,address',
                        'user:id,first_name,email,notification_token'
                    ])
                    ->find($insert->id);

                if ($appointment_details && $appointment_details->user_id != null) {
                    if ($appointment_details->expert->is_appointment_book_with_time_slot) {
                        if ($changes['status'] == 'confirmed') {
                            Mail::to($appointment_details->user->email)->send(new AppointmentConfirmationMail($appointment_details));

                            if ($appointment_details->user->notification_token) {
                                $notification = [
                                    'include_player_ids' => [$appointment_details->user->notification_token],
                                    'title' => 'Hello ' . $appointment_details->user->first_name,
                                    'message' => 'Your appointment with ' . $appointment_details->expert->expert_name . ' has been confirmed.',
                                    // 'data' => [],
                                    'url' =>  route('account.booking.details',  $appointment_details->id),
                                    // 'schedule' => now()->addMinutes(1)
                                ];
                            }
                        } else if ($changes['status'] == 'in_progress') {
                            if ($appointment_details->user->notification_token) {
                                $notification = [
                                    'include_player_ids' => [$appointment_details->user->notification_token],
                                    'title' => 'Hello ' . $appointment_details->user->first_name,
                                    'message' => 'Your turn is now! Please proceed to meet ' . $appointment_details->expert->expert_name . ' immediately.',
                                    // 'data' => [],
                                    'url' =>  route('account.booking.details',  $appointment_details->id),
                                    // 'schedule' => now()->addMinutes(1)
                                ];
                            }
                        } else if ($changes['status'] == 'completed') {
                            if ($appointment_details->user->notification_token) {
                                $notification = [
                                    'include_player_ids' => [$appointment_details->user->notification_token],
                                    'title' => 'Hello ' . $appointment_details->user->first_name,
                                    'message' => 'Your appointment with ' . $appointment_details->expert->expert_name . ' has been completed.',
                                    // 'data' => [],
                                    'url' =>  route('account.booking.details',  $appointment_details->id),
                                    // 'schedule' => now()->addMinutes(1)
                                ];
                            }
                            Mail::to($appointment_details->user->email)->send(new AppointmentComplitedMail($appointment_details));
                        } else if ($changes['status'] == 'cancel' || $changes['status'] == 'cancel_by_user') {
                            $notification = [
                                'include_player_ids' => [$appointment_details->user->notification_token],
                                'title' => 'Hello ' . $appointment_details->user->first_name,
                                'message' => 'Your appointment with ' . $appointment_details->expert->expert_name . ' has been cancelled.',
                                // 'data' => [],
                                'url' =>  route('account.booking.details',  $appointment_details->id),
                                // 'schedule' => now()->addMinutes(1)
                            ];
                            Mail::to($appointment_details->user->email)->send(new AppointmentCancelledMail($appointment_details));
                        }
                    } else {
                        if ($changes['status'] == 'confirmed') {
                            $notification = [
                                'include_player_ids' => [$appointment_details->user->notification_token],
                                'title' => 'Hello ' . $appointment_details->user->first_name,
                                'message' => 'Your appointment with ' . $appointment_details->expert->expert_name . ' has been confirmed.',
                                // 'data' => [],
                                'url' =>  route('account.booking.details',  $appointment_details->id),
                                // 'schedule' => now()->addMinutes(1)
                            ];
                            Mail::to($appointment_details->user->email)->send(new TokenConfirmationMail($appointment_details));
                        } else if ($changes['status'] == 'in_progress') {
                            if ($appointment_details->user->notification_token) {
                                $notification = [
                                    'include_player_ids' => [$appointment_details->user->notification_token],
                                    'title' => 'Hello ' . $appointment_details->user->first_name,
                                    'message' => 'Your turn is now! Please proceed to meet ' . $appointment_details->expert->expert_name . ' immediately.',
                                    // 'data' => [],
                                    'url' =>  route('account.booking.details',  $appointment_details->id),
                                    // 'schedule' => now()->addMinutes(1)
                                ];
                            }
                        } else if ($changes['status'] == 'completed') {

                            if ($appointment_details->user->notification_token) {
                                $notification = [
                                    'include_player_ids' => [$appointment_details->user->notification_token],
                                    'title' => 'Hello ' . $appointment_details->user->first_name,
                                    'message' => 'Your appointment with ' . $appointment_details->expert->expert_name . ' has been completed.',
                                    // 'data' => [],
                                    'url' =>  route('account.booking.details',  $appointment_details->id),
                                    // 'schedule' => now()->addMinutes(1)
                                ];
                            }

                            Mail::to($appointment_details->user->email)->send(new TokenComplitedMail($appointment_details));
                        } else if ($changes['status'] == 'cancel' || $changes['status'] == 'cancel_by_user') {

                            $notification = [
                                'include_player_ids' => [$appointment_details->user->notification_token],
                                'title' => 'Hello ' . $appointment_details->user->first_name,
                                'message' => 'Your appointment with ' . $appointment_details->expert->expert_name . ' has been cancelled.',
                                // 'data' => [],
                                'url' =>  route('account.booking.details',  $appointment_details->id),
                                // 'schedule' => now()->addMinutes(1)
                            ];

                            Mail::to($appointment_details->user->email)->send(new TokenCancelledMail($appointment_details));
                        }
                    }

                    // notify to next turn
                    if ($changes['status'] == 'in_progress') {
                        $expert = Expert::select('id', 'is_appointment_book_with_time_slot')
                            ->find($appointment_details->expert->id);

                        // Prepare base query for next booking
                        $nextBookingQuery = AppointmentBooking::select(
                            'id',
                            'token_number',
                            'business_id',
                            'expert_id',
                            'user_id',
                            'user_name',
                            'user_contact',
                            'slot_start_time',
                            'slot_end_time',
                            'booking_date',
                            'note',
                            'status'
                        )
                            ->with([
                                'expert:id,expert_name,slug,is_appointment_book_with_time_slot',
                                'business:id,name,slug,address',
                                'user:id,first_name,email,notification_token',
                            ])
                            ->whereDate('booking_date', Carbon::today())
                            ->where('expert_id', $expert->id)
                            ->where('status', 'confirmed')
                            ->where('id', '!=', $insert->id);

                        // Order by appropriate field based on expert settings
                        $orderByField = $expert->is_appointment_book_with_time_slot ? 'slot_start_time' : 'token_number';

                        // Get the next booking
                        $nextBooking = $nextBookingQuery->orderBy($orderByField)->first();

                        if ($nextBooking && $nextBooking->user->notification_token) {
                            $notification2 = [
                                'include_player_ids' => [$nextBooking->user->notification_token],
                                'title' => 'Hello ' . $nextBooking->user->first_name,
                                'message' => 'Just a heads-up! You’re next for your appointment with  ' . $nextBooking->expert->expert_name,
                                // 'data' => [],
                                'url' =>  route('account.booking.details',  $nextBooking->id),
                                // 'schedule' => now()->addMinutes(1)
                            ];
                            PuhsNotificationToUser::dispatch($notification2);
                        }
                    }
                }
            }
        }
        if (!empty($notification)) {
            PuhsNotificationToUser::dispatch($notification);
        }
    }

    /**
     * Handle the AppointmentBooking "deleted" event.
     */
    public function deleted(AppointmentBooking $appointmentBooking): void
    {
        //
    }

    /**
     * Handle the AppointmentBooking "restored" event.
     */
    public function restored(AppointmentBooking $appointmentBooking): void
    {
        //
    }

    /**
     * Handle the AppointmentBooking "force deleted" event.
     */
    public function forceDeleted(AppointmentBooking $appointmentBooking): void
    {
        //
    }
}
