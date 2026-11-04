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
use App\Services\FirebaseNotificationService;
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
                'expert:id,user_id,slug,is_appointment_book_with_time_slot',
                'expert.user:id,first_name,last_name',
                'business:id,owner_id,name,slug,address',
                'user:id,first_name,email,fcm_token'
            ])
            ->find($appointmentBooking->id);

        if ($appointment_details) {
            if ($appointment_details->user_id != null) {
                if ($appointment_details->expert->is_appointment_book_with_time_slot) {
                    if ($appointment_details->status == 'pending') {
                    } else if ($appointment_details->status == 'confirmed') {
                        // Mail::to($appointment_details->user->email)->send(new AppointmentConfirmationMail($appointment_details));
                    }
                } else {
                    if ($appointment_details->status == 'pending') {
                    } else if ($appointment_details->status == 'confirmed') {
                        // Mail::to($appointment_details->user->email)->send(new TokenConfirmationMail($appointment_details));
                    }
                }
            }

            // 1. Send push to Business Owner
            if ($appointment_details->business) {
                FirebaseNotificationService::sendToBusiness(
                    business: $appointment_details->business,
                    title: 'New Appointment Booking',
                    message: "Booking #{$appointment_details->token_number} received from {$appointment_details->user_name}",
                    redirectUrl: route('business.appointment.bookings.index'),
                    data: ['event_type' => 'appointment_booking', 'id' => (string) $appointment_details->id]
                );
            }

            // 2. Send push to Admins
            // FirebaseNotificationService::sendToAdmins(
            //     title: 'New System Booking',
            //     message: "Booking #{$appointment_details->token_number} created for {$appointment_details->business->name}",
            //     redirectUrl: route('admin.dashboard'),
            //     data: ['event_type' => 'appointment_booking', 'id' => (string) $appointment_details->id]
            // );

            // 3. Send push to Customer
            // if ($appointment_details->user) {
            //     FirebaseNotificationService::sendToUser(
            //         user: $appointment_details->user,
            //         title: 'Appointment Received',
            //         message: "Your booking #{$appointment_details->token_number} has been recorded.",
            //         redirectUrl: route('account.booking.details', $appointment_details->id),
            //         data: ['event_type' => 'appointment_booking', 'id' => (string) $appointment_details->id]
            //     );
            // }
        }

        // BusinessSetting::find($appointment_details->business_id)->decrement('credit', 1);
    }

    /**
     * Handle the AppointmentBooking "updated" event.
     */
    public function updated(AppointmentBooking $appointmentBooking): void
    {
        $insert = $appointmentBooking;

        if ($insert->wasChanged('status')) {
            $changes = $appointmentBooking->getChanges();
            if (in_array($changes['status'], ['confirmed', 'in_progress', 'completed', 'cancel', 'cancel_by_user'])) {
                $appointment_details = AppointmentBooking::query()
                    ->select('id', 'token_number', 'business_id', 'expert_id', 'user_id', 'user_name', 'user_contact', 'slot_start_time', 'slot_end_time', 'booking_date', 'note', 'status')
                    ->with([
                        'expert:id,user_id,slug,is_appointment_book_with_time_slot',
                        'expert.user:id,first_name,last_name',
                        'business:id,owner_id,name,slug,address',
                        'user:id,first_name,email,notification_token,fcm_token'
                    ])
                    ->find($insert->id);

                if ($appointment_details) {
                    $specialistName = $appointment_details->expert->user?->name ?? 'our specialist';
                    $hasTimeSlot = (bool) $appointment_details->expert?->is_appointment_book_with_time_slot;
                    $userEmail = $appointment_details->user?->email;

                    // 1. ACTION FROM USER SIDE -> Notify BUSINESS PANEL
                    if ($changes['status'] === 'cancel_by_user') {
                        if ($appointment_details->business) {
                            FirebaseNotificationService::sendToBusiness(
                                business: $appointment_details->business,
                                title: 'Booking Cancelled by Customer',
                                message: "Booking #{$appointment_details->token_number} for {$appointment_details->user_name} was cancelled by the customer.",
                                redirectUrl: route('business.appointment.bookings.index'),
                                data: [
                                    'event_type' => 'booking_cancelled_by_user',
                                    'id' => (string) $appointment_details->id,
                                    'token_number' => (string) $appointment_details->token_number,
                                ]
                            );
                        }

                        // Send cancellation confirmation email to user
                        if (!empty($userEmail)) {
                            if ($hasTimeSlot) {
                                // Mail::to($userEmail)->send(new AppointmentCancelledMail($appointment_details));
                            } else {
                                // Mail::to($userEmail)->send(new TokenCancelledMail($appointment_details));
                            }
                        }
                    }

                    // 2. ACTIONS TAKEN FROM BUSINESS SIDE -> Notify USER
                    if ($appointment_details->user) {
                        if ($changes['status'] === 'confirmed') {
                            FirebaseNotificationService::sendToUser(
                                user: $appointment_details->user,
                                title: 'Appointment Confirmed',
                                message: "Your appointment with {$specialistName} has been confirmed.",
                                redirectUrl: route('account.booking.details', $appointment_details->id),
                                data: [
                                    'event_type' => 'appointment_confirmed',
                                    'id' => (string) $appointment_details->id,
                                ]
                            );

                            if (!empty($userEmail)) {
                                if ($hasTimeSlot) {
                                    // Mail::to($userEmail)->send(new AppointmentConfirmationMail($appointment_details));
                                } else {
                                    // Mail::to($userEmail)->send(new TokenConfirmationMail($appointment_details));
                                }
                            }
                        } elseif ($changes['status'] === 'completed') {
                            FirebaseNotificationService::sendToUser(
                                user: $appointment_details->user,
                                title: 'Appointment Completed',
                                message: "Your appointment with {$specialistName} has been completed.",
                                redirectUrl: route('account.booking.details', $appointment_details->id),
                                data: [
                                    'event_type' => 'appointment_completed',
                                    'id' => (string) $appointment_details->id,
                                ]
                            );

                            if (!empty($userEmail)) {
                                if ($hasTimeSlot) {
                                    // Mail::to($userEmail)->send(new AppointmentComplitedMail($appointment_details));
                                } else {
                                    // Mail::to($userEmail)->send(new TokenComplitedMail($appointment_details));
                                }
                            }
                        } elseif ($changes['status'] === 'cancel') {
                            FirebaseNotificationService::sendToUser(
                                user: $appointment_details->user,
                                title: 'Appointment Cancelled',
                                message: "Your appointment with {$specialistName} has been cancelled by the business.",
                                redirectUrl: route('account.booking.details', $appointment_details->id),
                                data: [
                                    'event_type' => 'appointment_cancelled_by_business',
                                    'id' => (string) $appointment_details->id,
                                ]
                            );

                            if (!empty($userEmail)) {
                                if ($hasTimeSlot) {
                                    // Mail::to($userEmail)->send(new AppointmentCancelledMail($appointment_details));
                                } else {
                                    // Mail::to($userEmail)->send(new TokenCancelledMail($appointment_details));
                                }
                            }
                        } elseif ($changes['status'] === 'in_progress') {
                            FirebaseNotificationService::sendToUser(
                                user: $appointment_details->user,
                                title: 'Your Turn Is Now',
                                message: "Your turn is now! Please proceed to meet {$specialistName} immediately.",
                                redirectUrl: route('account.booking.details', $appointment_details->id),
                                data: [
                                    'event_type' => 'appointment_in_progress',
                                    'id' => (string) $appointment_details->id,
                                ]
                            );
                        }
                    }

                    // 3. Notify next user in line if status changed to in_progress
                    if ($changes['status'] === 'in_progress' && $appointment_details->expert) {
                        $expert = Expert::select('id', 'is_appointment_book_with_time_slot')
                            ->find($appointment_details->expert->id);

                        if ($expert) {
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
                                    'expert:id,user_id,slug,is_appointment_book_with_time_slot',
                                    'expert.user:id,first_name,last_name',
                                    'business:id,owner_id,name,slug,address',
                                    'user:id,first_name,email,notification_token,fcm_token',
                                ])
                                ->whereDate('booking_date', Carbon::today())
                                ->where('expert_id', $expert->id)
                                ->where('status', 'confirmed')
                                ->where('id', '!=', $insert->id);

                            $orderByField = $expert->is_appointment_book_with_time_slot ? 'slot_start_time' : 'token_number';
                            $nextBooking = $nextBookingQuery->orderBy($orderByField)->first();

                            if ($nextBooking && $nextBooking->user) {
                                FirebaseNotificationService::sendToUser(
                                    user: $nextBooking->user,
                                    title: 'Hello ' . $nextBooking->user->first_name,
                                    message: 'Just a heads-up! You’re next for your appointment with ' . ($nextBooking->expert->user?->name ?? 'our specialist'),
                                    redirectUrl: route('account.booking.details', $nextBooking->id),
                                    data: [
                                        'event_type' => 'appointment_turn',
                                        'id' => (string) $nextBooking->id,
                                    ]
                                );
                            }
                        }
                    }
                }
            }
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
