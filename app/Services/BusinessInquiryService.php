<?php

namespace App\Services;

use App\Models\BusinessInquiry;
use App\Models\BusinessInquiryLog;
use App\Models\BusinessInquirySource;
use App\Models\User;
use DomainException;
use Exception;
use Illuminate\Support\Facades\DB;

class BusinessInquiryService
{
    public function __construct(
        protected CreditService $creditService
    ) {}

    /**
     * Create a new inquiry (e.g. from frontend or API).
     *
     * @param array $data
     * @param array $sources Array of items with keys: ['source_id' => int, 'source_type' => 'product'|'service']
     * @return BusinessInquiry
     */
    public function createInquiry(array $data, array $sources = []): BusinessInquiry
    {
        $sources = !empty($sources) ? $sources : ($data['sources'] ?? []);

        return DB::transaction(function () use ($data, $sources) {
            $inquiry = BusinessInquiry::create([
                'business_id'      => $data['business_id'],
                'user_id'          => $data['user_id'] ?? null,
                'assigned_to'      => $data['assigned_to'] ?? null,
                'name'             => $data['name'],
                'contact'          => $data['contact'],
                'email'            => $data['email'] ?? null,
                'message'          => $data['message'] ?? null,
                'inquiry_type'     => $data['inquiry_type'] ?? BusinessInquiry::TYPE_CONTACT_US,
                'system_status'    => BusinessInquiry::SYSTEM_STATUS_PENDING,
                'business_status'  => BusinessInquiry::BUSINESS_STATUS_NEW,
                'credit_deducted'  => false,
                'credit_deducted_id' => null,
            ]);

            foreach ($sources as $source) {
                if (!empty($source['source_id']) && !empty($source['source_type'])) {
                    BusinessInquirySource::create([
                        'inquiry_id'  => $inquiry->id,
                        'source_id'   => $source['source_id'],
                        'source_type' => $source['source_type'],
                    ]);
                }
            }

            BusinessInquiryLog::create([
                'inquiry_id' => $inquiry->id,
                'user_id'    => $data['user_id'] ?? null,
                'action'     => 'created',
                'old_value'  => null,
                'new_value'  => BusinessInquiry::SYSTEM_STATUS_PENDING,
                'remarks'    => $data['remarks'] ?? 'Inquiry submitted',
            ]);

            return $inquiry->fresh(['sources', 'logs']);
        });
    }

    /**
     * Accept a pending inquiry.
     * Deducts credit and transitions system_status to accepted and business_status to new.
     *
     * @param BusinessInquiry $inquiry
     * @param string|null $remarks
     * @param int|null $userId
     * @return BusinessInquiry
     * @throws DomainException|Exception
     */
    public function acceptInquiry(BusinessInquiry $inquiry, ?string $remarks = null, ?int $userId = null): BusinessInquiry
    {
        if ($inquiry->system_status === BusinessInquiry::SYSTEM_STATUS_ACCEPTED) {
            throw new DomainException('Inquiry is already accepted.');
        }

        if ($inquiry->system_status !== BusinessInquiry::SYSTEM_STATUS_PENDING) {
            throw new DomainException('Only pending inquiries can be accepted.');
        }

        return DB::transaction(function () use ($inquiry, $remarks, $userId) {
            // Lock inquiry record for update to prevent race conditions
            $inquiry = BusinessInquiry::where('id', $inquiry->id)->lockForUpdate()->firstOrFail();

            if ($inquiry->system_status !== BusinessInquiry::SYSTEM_STATUS_PENDING) {
                throw new DomainException('Only pending inquiries can be accepted.');
            }

            // 1. Check sufficient credit balance
            $inquiryType = $inquiry->inquiry_type ?? BusinessInquiry::TYPE_CONTACT_US;
            $this->creditService->checkInquiryCredit($inquiry->business_id, $inquiryType, $inquiry);

            // 2. Deduct credit
            $creditHistory = $this->creditService->deductInquiryCharge(
                $inquiry->business_id,
                $inquiryType,
                $inquiry->id,
                $inquiry
            );

            // 3. Update inquiry
            $oldSystemStatus = $inquiry->system_status;
            $inquiry->system_status = BusinessInquiry::SYSTEM_STATUS_ACCEPTED;
            $inquiry->business_status = BusinessInquiry::BUSINESS_STATUS_NEW;
            $inquiry->credit_deducted = true;
            $inquiry->credit_deducted_id = $creditHistory?->id;
            $inquiry->accepted_at = now();
            $inquiry->save();

            // 4. Record logs
            BusinessInquiryLog::create([
                'inquiry_id' => $inquiry->id,
                'user_id'    => $userId,
                'action'     => 'accepted',
                'old_value'  => $oldSystemStatus,
                'new_value'  => BusinessInquiry::SYSTEM_STATUS_ACCEPTED,
                'remarks'    => $remarks ?? 'Inquiry accepted by business',
            ]);

            BusinessInquiryLog::create([
                'inquiry_id' => $inquiry->id,
                'user_id'    => $userId,
                'action'     => 'business_status_changed',
                'old_value'  => null,
                'new_value'  => BusinessInquiry::BUSINESS_STATUS_NEW,
                'remarks'    => 'Inquiry entered sales pipeline as new',
            ]);

            return $inquiry->fresh(['sources', 'logs', 'assignedTo', 'creditDeducted']);
        });
    }

    /**
     * Reject a pending inquiry.
     * Does NOT deduct credit.
     *
     * @param BusinessInquiry $inquiry
     * @param string|null $remarks
     * @param int|null $userId
     * @return BusinessInquiry
     * @throws DomainException
     */
    public function rejectInquiry(BusinessInquiry $inquiry, ?string $remarks = null, ?int $userId = null): BusinessInquiry
    {
        if ($inquiry->system_status !== BusinessInquiry::SYSTEM_STATUS_PENDING) {
            throw new DomainException('Only pending inquiries can be rejected.');
        }

        return DB::transaction(function () use ($inquiry, $remarks, $userId) {
            $inquiry = BusinessInquiry::where('id', $inquiry->id)->lockForUpdate()->firstOrFail();

            if ($inquiry->system_status !== BusinessInquiry::SYSTEM_STATUS_PENDING) {
                throw new DomainException('Only pending inquiries can be rejected.');
            }

            $oldStatus = $inquiry->system_status;
            $inquiry->system_status = BusinessInquiry::SYSTEM_STATUS_REJECTED;
            $inquiry->save();

            BusinessInquiryLog::create([
                'inquiry_id' => $inquiry->id,
                'user_id'    => $userId,
                'action'     => 'rejected',
                'old_value'  => $oldStatus,
                'new_value'  => BusinessInquiry::SYSTEM_STATUS_REJECTED,
                'remarks'    => $remarks ?? 'Inquiry rejected by business',
            ]);

            return $inquiry->fresh(['sources', 'logs']);
        });
    }

    /**
     * Mark an inquiry as not fulfilled.
     * Does NOT deduct credit.
     *
     * @param BusinessInquiry $inquiry
     * @param string|null $remarks
     * @param int|null $userId
     * @return BusinessInquiry
     * @throws DomainException
     */
    public function markNotFulfilled(BusinessInquiry $inquiry, ?string $remarks = null, ?int $userId = null): BusinessInquiry
    {
        if ($inquiry->system_status !== BusinessInquiry::SYSTEM_STATUS_PENDING) {
            throw new DomainException('Only pending inquiries can be marked as not fulfilled.');
        }

        return DB::transaction(function () use ($inquiry, $remarks, $userId) {
            $inquiry = BusinessInquiry::where('id', $inquiry->id)->lockForUpdate()->firstOrFail();

            if ($inquiry->system_status !== BusinessInquiry::SYSTEM_STATUS_PENDING) {
                throw new DomainException('Only pending inquiries can be marked as not fulfilled.');
            }

            $oldStatus = $inquiry->system_status;
            $inquiry->system_status = BusinessInquiry::SYSTEM_STATUS_NOT_FULFILLED;
            $inquiry->save();

            BusinessInquiryLog::create([
                'inquiry_id' => $inquiry->id,
                'user_id'    => $userId,
                'action'     => 'not_fulfilled',
                'old_value'  => $oldStatus,
                'new_value'  => BusinessInquiry::SYSTEM_STATUS_NOT_FULFILLED,
                'remarks'    => $remarks ?? 'Inquiry cannot be fulfilled',
            ]);

            return $inquiry->fresh(['sources', 'logs']);
        });
    }

    /**
     * Update business sales workflow status.
     * Only accepted inquiries can enter the business workflow.
     * Business status changes NEVER deduct credit.
     *
     * @param BusinessInquiry $inquiry
     * @param string $newStatus
     * @param array $payload Optional fields: remarks, next_follow_up_at
     * @param int|null $userId
     * @return BusinessInquiry
     * @throws DomainException
     */
    public function updateBusinessStatus(BusinessInquiry $inquiry, string $newStatus, array $payload = [], ?int $userId = null): BusinessInquiry
    {
        if ($inquiry->system_status !== BusinessInquiry::SYSTEM_STATUS_ACCEPTED) {
            throw new DomainException('Only accepted inquiries can enter the sales workflow.');
        }

        $validStatuses = [
            BusinessInquiry::BUSINESS_STATUS_NEW,
            BusinessInquiry::BUSINESS_STATUS_CONTACTED,
            BusinessInquiry::BUSINESS_STATUS_FOLLOW_UP,
            BusinessInquiry::BUSINESS_STATUS_SCHEDULED,
            BusinessInquiry::BUSINESS_STATUS_CONVERTED,
            BusinessInquiry::BUSINESS_STATUS_CLOSED,
        ];

        if (!in_array($newStatus, $validStatuses, true)) {
            throw new DomainException("Invalid business status: {$newStatus}");
        }

        return DB::transaction(function () use ($inquiry, $newStatus, $payload, $userId) {
            $inquiry = BusinessInquiry::where('id', $inquiry->id)->lockForUpdate()->firstOrFail();

            $oldStatus = $inquiry->business_status;
            $inquiry->business_status = $newStatus;

            if ($newStatus === BusinessInquiry::BUSINESS_STATUS_CONTACTED && !$inquiry->contacted_at) {
                $inquiry->contacted_at = now();
            }

            if ($newStatus === BusinessInquiry::BUSINESS_STATUS_CONVERTED && !$inquiry->converted_at) {
                $inquiry->converted_at = now();
            }

            if ($newStatus === BusinessInquiry::BUSINESS_STATUS_CLOSED && !$inquiry->closed_at) {
                $inquiry->closed_at = now();
            }

            $oldFollowUp = $inquiry->next_follow_up_at ? $inquiry->next_follow_up_at->toIso8601String() : null;
            if (array_key_exists('next_follow_up_at', $payload)) {
                $inquiry->next_follow_up_at = $payload['next_follow_up_at'];
            }

            $inquiry->save();

            // Log business status change
            if ($oldStatus !== $newStatus) {
                BusinessInquiryLog::create([
                    'inquiry_id' => $inquiry->id,
                    'user_id'    => $userId,
                    'action'     => 'business_status_changed',
                    'old_value'  => $oldStatus,
                    'new_value'  => $newStatus,
                    'remarks'    => $payload['remarks'] ?? null,
                ]);
            }

            // Log follow up update if changed
            $newFollowUp = $inquiry->next_follow_up_at ? $inquiry->next_follow_up_at->toIso8601String() : null;
            if ($oldFollowUp !== $newFollowUp) {
                BusinessInquiryLog::create([
                    'inquiry_id' => $inquiry->id,
                    'user_id'    => $userId,
                    'action'     => 'follow_up_updated',
                    'old_value'  => $oldFollowUp,
                    'new_value'  => $newFollowUp,
                    'remarks'    => $payload['remarks'] ?? 'Next follow up updated',
                ]);
            }

            return $inquiry->fresh(['sources', 'logs', 'assignedTo']);
        });
    }

    /**
     * Assign or reassign an inquiry to a staff employee.
     *
     * @param BusinessInquiry $inquiry
     * @param int|null $assignedTo
     * @param string|null $remarks
     * @param int|null $userId
     * @return BusinessInquiry
     */
    public function assignEmployee(BusinessInquiry $inquiry, ?int $assignedTo, ?string $remarks = null, ?int $userId = null): BusinessInquiry
    {
        return DB::transaction(function () use ($inquiry, $assignedTo, $remarks, $userId) {
            $inquiry = BusinessInquiry::where('id', $inquiry->id)->lockForUpdate()->firstOrFail();

            $oldAssignedId = $inquiry->assigned_to;
            if ($oldAssignedId == $assignedTo) {
                return $inquiry;
            }

            $oldUser = $oldAssignedId ? User::select('id', 'first_name', 'last_name')->find($oldAssignedId) : null;
            $newUser = $assignedTo ? User::select('id', 'first_name', 'last_name')->find($assignedTo) : null;

            $oldName = $oldUser ? $oldUser->name : 'Unassigned';
            $newName = $newUser ? $newUser->name : 'Unassigned';

            $inquiry->assigned_to = $assignedTo;
            $inquiry->save();

            BusinessInquiryLog::create([
                'inquiry_id' => $inquiry->id,
                'user_id'    => $userId,
                'action'     => $oldAssignedId ? 'reassigned' : 'assigned',
                'old_value'  => $oldName,
                'new_value'  => $newName,
                'remarks'    => $remarks ?? ($oldAssignedId ? "Reassigned from {$oldName} to {$newName}" : "Assigned to {$newName}"),
            ]);

            return $inquiry->fresh(['sources', 'logs', 'assignedTo']);
        });
    }

    /**
     * Add note / remarks to the inquiry history.
     *
     * @param BusinessInquiry $inquiry
     * @param string $remarks
     * @param int|null $userId
     * @return BusinessInquiryLog
     */
    public function addNote(BusinessInquiry $inquiry, string $remarks, ?int $userId = null): BusinessInquiryLog
    {
        return BusinessInquiryLog::create([
            'inquiry_id' => $inquiry->id,
            'user_id'    => $userId,
            'action'     => 'note_added',
            'old_value'  => null,
            'new_value'  => null,
            'remarks'    => $remarks,
        ]);
    }
}

