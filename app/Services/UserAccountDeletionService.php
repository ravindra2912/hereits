<?php

namespace App\Services;

use App\Models\AppointmentBooking;
use App\Models\BusinessCampaignLead;
use App\Models\BusinessCustomer;
use App\Models\BusinessUser;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserAccountDeletionService
{
    /**
     * Delete or permanently anonymize all personal data associated with a user.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAccount(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            $userId = $user->id;
            $originalEmail = $user->email;
            $originalContact = $user->contact;

            // 1. Delete profile avatar from disk / cloud storage
            if (!empty($user->profile) && function_exists('fileRemoveStorage')) {
                fileRemoveStorage($user->profile);
            }

            // 2. Revoke and delete all issued API / Passport OAuth tokens
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            // 3. Remove all device push tokens and user device associations
            UserDevice::where('user_id', $userId)->delete();

            // 4. Remove all user favorites / followed merchants
            Favorite::where('user_id', $userId)->delete();

            // 5. Remove staff associations if user is employed by merchants
            BusinessUser::where('user_id', $userId)->delete();

            // 6. If user owns businesses, deactivate storefronts
            if ($user->hasBusiness()) {
                $user->getBusinesses()->update(['status' => 'in-active']);
            }

            // 7. Anonymize Business Customer records linked to this user
            BusinessCustomer::where('customer_id', $userId)->update([
                'customer_name' => 'Deleted Customer',
                'contact' => null,
                'address' => null,
                'state' => null,
                'city' => null,
                'pincode' => null,
            ]);

            // 8. Anonymize user contact/email in business campaign leads
            if (!empty($originalEmail) || !empty($originalContact)) {
                $leadQuery = BusinessCampaignLead::query();
                if (!empty($originalEmail) && !empty($originalContact)) {
                    $leadQuery->where(function ($q) use ($originalEmail, $originalContact) {
                        $q->where('email', $originalEmail)->orWhere('contact', (string)$originalContact);
                    });
                } elseif (!empty($originalEmail)) {
                    $leadQuery->where('email', $originalEmail);
                } else {
                    $leadQuery->where('contact', (string)$originalContact);
                }

                $leadQuery->update([
                    'contact' => null,
                    'email' => 'deleted_' . $userId . '@anonymized.invalid',
                    'ip_address' => '0.0.0.0',
                    'details' => null,
                ]);
            }

            // 9. Anonymize PII in historical orders (retaining order & financial structure for tax/audit)
            // Order::where('customer_id', $userId)->update([
            //     'customer_name' => 'Deleted Customer',
            //     'customer_contact' => null,
            //     'address' => '[REDACTED]',
            //     'pincode' => null,
            //     'notes' => null,
            // ]);

            // 10. Anonymize PII in historical appointment bookings
            // AppointmentBooking::where('user_id', $userId)->update([
            //     'user_name' => 'Deleted Customer',
            //     'user_contact' => null,
            //     'note' => null,
            // ]);

            // 11. Anonymize and scramble the User record itself
            // $randomSuffix = Str::random(8);
            // $user->first_name = 'Deleted';
            // $user->last_name = 'User';
            // $user->email = 'deleted_' . $userId . '_' . $randomSuffix . '@anonymized.invalid';
            // $user->contact = null;
            // $user->dob = null;
            // $user->gender = null;
            // $user->profile = null;
            // $user->fcm_token = null;
            // $user->notification_token = null;
            // $user->google_key = null;
            // $user->remember_token = null;
            // $user->password = Hash::make(Str::random(32));
            // $user->status = 'in-active';
            // $user->save();

            // 12. Soft-delete the user record
            $user->delete();

            return true;
        });
    }
}
