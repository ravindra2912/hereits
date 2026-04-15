<?php

namespace App\Observers;

use App\Mail\Business\BusinessWelcomeMail;
use App\Models\Business;
use App\Mail\Business\BusinessBannedMail;
use App\Mail\Business\BusinessApprovedMail;
use Illuminate\Support\Facades\Mail;

class BusinessObserver
{
    /**
     * Handle the Business "created" event.
     */
    public function created(Business $business): void
    {
        $business = Business::select('id', 'name', 'owner_id')
            ->with(['owner:id,first_name,email'])
            ->find($business->id);

        if (isset($business->owner) && !empty($business->owner->email)) {
            Mail::to($business->owner->email)->send(new BusinessWelcomeMail($business->owner, $business));
        }
    }

    /**
     * Handle the Business "updated" event.
     */
    public function updated(Business $business): void
    {
        $isChange = $business;

        if ($isChange->wasChanged('status')) {
            $changes = $business->getChanges();
            if (in_array($changes['status'], ['active', 'baned', 'in-active'])) {
                $business = Business::select('id', 'name', 'owner_id')
                    ->with(['owner:id,first_name,email'])
                    ->find($business->id);

                if (isset($business->owner) && !empty($business->owner->email)) {
                    if ($changes['status'] == 'active') {
                        Mail::to($business->owner->email)->send(new BusinessApprovedMail($business->owner, $business));
                    } else if ($changes['status'] == 'in-active') {
                        Mail::to($business->owner->email)->send(new BusinessBannedMail($business->owner, $business));
                    } else if ($changes['status'] == 'baned') {
                        Mail::to($business->owner->email)->send(new BusinessBannedMail($business->owner, $business));
                    }
                }
            }
        }
    }

    /**
     * Handle the Business "deleted" event.
     */
    public function deleted(Business $business): void
    {
        //
    }

    /**
     * Handle the Business "restored" event.
     */
    public function restored(Business $business): void
    {
        //
    }

    /**
     * Handle the Business "force deleted" event.
     */
    public function forceDeleted(Business $business): void
    {
        //
    }
}
