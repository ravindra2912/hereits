<?php

namespace App\Observers;

use App\Mail\User\UserWelcomeMail;
use App\Models\User;
use App\Models\Order;
use App\Models\BusinessCustomer;
use App\Models\AppointmentBooking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        try {
            if ($user->email) {
                Mail::to($user->email)->send(new UserWelcomeMail($user));
            }
        } catch (\Throwable $e) {
            Log::warning('UserWelcomeMail failed for user ' . $user->id . ': ' . $e->getMessage());
        }

        $this->linkUserToExistingRecordsByContact($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if ($user->wasChanged('contact') && !empty($user->contact)) {
            $this->linkUserToExistingRecordsByContact($user);
        }
    }

    /**
     * Link user to existing orders, business_customers, and appointment_bookings by contact number.
     */
    private function linkUserToExistingRecordsByContact(User $user): void
    {
        $rawContact = trim((string)($user->contact ?? ''));
        if (empty($rawContact)) {
            return;
        }

        $digitsOnly = preg_replace('/[^0-9]/', '', $rawContact);
        $tenDigit = strlen($digitsOnly) >= 10 ? substr($digitsOnly, -10) : $digitsOnly;

        if (empty($tenDigit)) {
            return;
        }

        $contactVariants = array_values(array_unique(array_filter([
            $rawContact,
            $digitsOnly,
            $tenDigit,
            '+91' . $tenDigit,
            '+91 ' . $tenDigit,
            '+91-' . $tenDigit,
            '91' . $tenDigit,
            '0' . $tenDigit,
        ])));

        // 1. Update matching Orders where customer_id is null/0
        Order::where(function ($q) {
                $q->whereNull('customer_id')
                  ->orWhere('customer_id', 0);
            })
            ->where(function ($q) use ($contactVariants, $tenDigit) {
                $q->whereIn('customer_contact', $contactVariants)
                  ->orWhere('customer_contact', 'like', '%' . $tenDigit);
            })
            ->update(['customer_id' => $user->id]);

        // 2. Update matching AppointmentBookings where user_id is null/0
        AppointmentBooking::where(function ($q) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', 0);
            })
            ->where(function ($q) use ($contactVariants, $tenDigit) {
                $q->whereIn('user_contact', $contactVariants)
                  ->orWhere('user_contact', 'like', '%' . $tenDigit);
            })
            ->update(['user_id' => $user->id]);

        // 3. Update matching BusinessCustomer records where customer_id is null/0
        BusinessCustomer::where(function ($q) {
                $q->whereNull('customer_id')
                  ->orWhere('customer_id', 0);
            })
            ->where(function ($q) use ($contactVariants, $tenDigit) {
                $q->whereIn('contact', $contactVariants)
                  ->orWhere('contact', 'like', '%' . $tenDigit);
            })
            ->update(['customer_id' => $user->id]);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
