<?php

namespace App\Repositories\Eloquent;

use App\Models\LuckyDraw;
use App\Models\LuckyDrawEntry;
use App\Models\LuckyDrawPrize;
use App\Services\CreditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LuckyDrawRepository
{
    public function __construct(
        protected CreditService $creditService
    ) {}

    /**
     * Get LuckyDraw query for DataTables listing.
     */
    public function getDrawsQuery(int $businessId)
    {
        return LuckyDraw::where('business_id', $businessId)
            ->withCount(['prizes', 'entries'])
            ->latest();
    }

    /**
     * Get all active LuckyDraw campaigns for a business.
     */
    public function getActiveDrawsForBusiness(int $businessId)
    {
        return LuckyDraw::where('business_id', $businessId)
            ->where('status', 'active')
            ->with(['prizes' => function ($q) {
                $q->where('win_percentage', '>', 0);
            }])
            ->get();
    }

    /**
     * Get a LuckyDraw by ID for a business.
     */
    public function getDrawById(int $businessId, int $id, array $with = []): LuckyDraw
    {
        $query = LuckyDraw::where('business_id', $businessId);

        if (!empty($with)) {
            $query->with($with);
        }

        return $query->findOrFail($id);
    }

    /**
     * Create a new LuckyDraw campaign.
     */
    public function createDraw(int $businessId, array $data): LuckyDraw
    {
        return LuckyDraw::create([
            'business_id'  => $businessId,
            'title'        => $data['title'],
            'description'  => $data['description'] ?? null,
            'type'         => isset($data['type']) && in_array($data['type'], ['scratch', 'spinner']) ? $data['type'] : 'scratch',
            'start_date'   => $data['start_date'] ?? null,
            'end_date'     => $data['end_date'] ?? null,
            'status'       => isset($data['status']) ? $data['status'] : 'active',
        ]);
    }

    /**
     * Update an existing LuckyDraw campaign.
     */
    public function updateDraw(LuckyDraw $draw, array $data): bool
    {
        $updateData = [
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'start_date'  => $data['start_date'] ?? null,
            'end_date'    => $data['end_date'] ?? null,
            'status'      => isset($data['status']) ? $data['status'] : $draw->status,
        ];

        if (isset($data['type']) && in_array($data['type'], ['scratch', 'spinner'])) {
            $updateData['type'] = $data['type'];
        }

        return $draw->update($updateData);
    }

    /**
     * Delete a LuckyDraw campaign along with prizes and entries.
     */
    public function deleteDraw(LuckyDraw $draw): bool
    {
        $draw->load('prizes');

        foreach ($draw->prizes as $prize) {
            if (!empty($prize->image_path)) {
                $isUsedElsewhere = LuckyDrawPrize::where('image_path', $prize->image_path)
                    ->where('id', '!=', $prize->id)
                    ->exists();

                if (!$isUsedElsewhere) {
                    fileRemoveStorage($prize->image_path);
                }
            }
        }

        $draw->prizes()->delete();
        $draw->entries()->delete();

        return (bool) $draw->delete();
    }

    /**
     * Get Prizes query for DataTables.
     */
    public function getPrizesQuery(int $drawId)
    {
        return LuckyDrawPrize::where('lucky_draw_id', $drawId)->latest();
    }

    /**
     * Get other campaigns of the business that have prizes for import.
     */
    public function getAvailableCampaignsForImport(int $businessId, int $currentDrawId)
    {
        return LuckyDraw::where('business_id', $businessId)
            ->where('id', '!=', $currentDrawId)
            ->whereHas('prizes')
            ->select('id', 'title', 'type', 'status')
            ->withCount('prizes')
            ->latest()
            ->get();
    }

    /**
     * Get prizes of a source campaign for preview in import modal.
     */
    public function getSourceCampaignPrizes(int $businessId, int $sourceDrawId)
    {
        $sourceDraw = LuckyDraw::where('business_id', $businessId)->findOrFail($sourceDrawId);

        return LuckyDrawPrize::where('lucky_draw_id', $sourceDraw->id)
            ->select('id', 'lucky_draw_id', 'name', 'win_percentage', 'image_path')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($prize) {
                return [
                    'id'             => $prize->id,
                    'name'           => $prize->name,
                    'win_percentage' => $prize->win_percentage,
                    'image_url'      => $prize->image_url,
                    'has_image'      => !empty($prize->image_path),
                ];
            });
    }

    /**
     * Copy / Import prizes from one campaign into target campaign.
     */
    public function importPrizesFromCampaign(LuckyDraw $targetDraw, int $businessId, int $sourceDrawId, ?array $selectedPrizeIds = null): array
    {
        $sourceDraw = LuckyDraw::where('business_id', $businessId)
            ->where('id', '!=', $targetDraw->id)
            ->findOrFail($sourceDrawId);

        $query = LuckyDrawPrize::where('lucky_draw_id', $sourceDraw->id);

        if (!empty($selectedPrizeIds)) {
            $query->whereIn('id', $selectedPrizeIds);
        }

        $sourcePrizes = $query->get();

        if ($sourcePrizes->isEmpty()) {
            throw new \Exception('No prizes found to import from the selected campaign.');
        }

        $importedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($sourcePrizes as $sourcePrize) {
                LuckyDrawPrize::create([
                    'lucky_draw_id'  => $targetDraw->id,
                    'name'           => $sourcePrize->name,
                    'win_percentage' => $sourcePrize->win_percentage,
                    'image_path'     => $sourcePrize->image_path,
                    'total_won'      => 0,
                ]);

                $importedCount++;
            }

            DB::commit();

            $updatedPrizes = LuckyDrawPrize::where('lucky_draw_id', $targetDraw->id)->get();

            return [
                'imported_count'    => $importedCount,
                'total_prizes'      => $updatedPrizes->count(),
                'total_probability' => (float) $updatedPrizes->sum('win_percentage'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Add or update a prize for a campaign.
     */
    public function storePrize(LuckyDraw $draw, array $data, $imageFile = null): LuckyDrawPrize
    {
        if (!empty($data['prize_id'])) {
            $prize = LuckyDrawPrize::where('lucky_draw_id', $draw->id)->findOrFail($data['prize_id']);
        } else {
            $prize = new LuckyDrawPrize();
            $prize->lucky_draw_id = $draw->id;
        }

        $prize->name = $data['name'];
        $prize->win_percentage = $data['win_percentage'];

        if ($imageFile) {
            if (!empty($prize->image_path)) {
                $isUsedElsewhere = LuckyDrawPrize::where('image_path', $prize->image_path)
                    ->where('id', '!=', $prize->id)
                    ->exists();

                if (!$isUsedElsewhere) {
                    fileRemoveStorage($prize->image_path);
                }
            }
            $prize->image_path = fileUploadStorage($imageFile, 'lucky_draw', 200, 200);
        }

        $prize->save();

        return $prize;
    }

    /**
     * Delete a prize item.
     */
    public function deletePrize(LuckyDraw $draw, int $prizeId): bool
    {
        $prize = LuckyDrawPrize::where('lucky_draw_id', $draw->id)->findOrFail($prizeId);

        if (!empty($prize->image_path)) {
            $isUsedElsewhere = LuckyDrawPrize::where('image_path', $prize->image_path)
                ->where('id', '!=', $prize->id)
                ->exists();

            if (!$isUsedElsewhere) {
                fileRemoveStorage($prize->image_path);
            }
        }

        return (bool) $prize->delete();
    }

    /**
     * Get Entries query for DataTables with optional user filter.
     */
    public function getEntriesQuery(int $drawId, ?int $createdById = null)
    {
        return LuckyDrawEntry::where('lucky_draw_id', $drawId)
            ->when($createdById, function ($q) use ($createdById) {
                $q->where('created_by_id', $createdById);
            })
            ->with(['wonPrize', 'createdBy'])
            ->latest();
    }

    /**
     * Get all Entries query across campaigns for a business with optional user filter.
     */
    public function getBusinessEntriesQuery(int $businessId, ?int $createdById = null, ?int $luckyDrawId = null)
    {
        return LuckyDrawEntry::whereHas('luckyDraw', function ($q) use ($businessId) {
            $q->where('business_id', $businessId);
        })
            ->when($createdById, function ($q) use ($createdById) {
                $q->where('created_by_id', $createdById);
            })
            ->when($luckyDrawId, function ($q) use ($luckyDrawId) {
                $q->where('lucky_draw_id', $luckyDrawId);
            })
            ->with(['luckyDraw', 'wonPrize', 'createdBy'])
            ->latest();
    }

    /**
     * Weighted Random Selection Algorithm for Prize Determination.
     */
    public function determinePrize(LuckyDraw $draw): ?LuckyDrawPrize
    {
        $activePrizes = LuckyDrawPrize::where('lucky_draw_id', $draw->id)
            ->where('win_percentage', '>', 0)
            ->get();

        if ($activePrizes->isEmpty()) {
            return null;
        }

        $totalWeight = $activePrizes->sum('win_percentage');

        if ($totalWeight <= 0) {
            return $activePrizes->first();
        }

        $randomRoll = mt_rand(1, (int) round($totalWeight * 100)) / 100;

        $currentSum = 0;
        foreach ($activePrizes as $prize) {
            $currentSum += $prize->win_percentage;
            if ($randomRoll <= $currentSum) {
                return $prize;
            }
        }

        return $activePrizes->first();
    }

    /**
     * Generate customer scratch link & deduct credit.
     */
    public function generateEntry(LuckyDraw $draw, int $businessId, array $data, ?int $createdById = null): array
    {
        if ($draw->status !== 'active') {
            throw new \Exception('This lucky draw campaign is inactive.');
        }

        $hasActivePrizes = LuckyDrawPrize::where('lucky_draw_id', $draw->id)
            ->where('win_percentage', '>', 0)
            ->exists();

        if (!$hasActivePrizes) {
            throw new \Exception('Cannot generate scratch card link. This campaign does not have any prize items, Please add prize item first.');
        }

        $creatorId = $createdById ?? (Auth::guard('pos')->id() ?? Auth::id());
        $for = !empty($data['for']) && in_array($data['for'], ['other', 'order']) ? $data['for'] : 'other';
        $sourceId = !empty($data['source_id']) ? $data['source_id'] : null;

        if ($for === 'order' && !empty($sourceId)) {
            $existing = LuckyDrawEntry::where('for', 'order')
                ->where('source_id', $sourceId)
                ->first();
            if ($existing) {
                throw new \Exception('A Lucky Draw link has already been created for this order.');
            }
        }

        $costPerScratch = $this->creditService->getLuckyDrawCharge();
        $this->creditService->checkLuckyDrawCredit($businessId);

        try {
            DB::beginTransaction();

            $this->creditService->deductLuckyDrawCharge($businessId);

            $wonPrize = null;
            if (!empty($data['won_prize_id'])) {
                $wonPrize = LuckyDrawPrize::where('lucky_draw_id', $draw->id)
                    ->where('win_percentage', '>', 0)
                    ->find($data['won_prize_id']);
            }

            if (!$wonPrize) {
                $wonPrize = $this->determinePrize($draw);
            }

            $token = Str::random(32);

            $entry = LuckyDrawEntry::create([
                'lucky_draw_id'    => $draw->id,
                'created_by_id'    => $creatorId,
                'customer_name'    => trim($data['customer_name']),
                'customer_contact' => trim($data['customer_contact']),
                'for'              => $for,
                'source_id'        => $sourceId,
                'token'            => $token,
                'won_prize_id'     => $wonPrize ? $wonPrize->id : null,
                'credits_deducted' => $costPerScratch,
                'status'           => 'pending',
            ]);

            DB::commit();

            $businessSlug = $draw->business->slug ?? (auth()->user()->business->slug ?? 'store');
            $scratchUrl = route('lucky-draw.scratch', ['business_slug' => $businessSlug, 'token' => $token]);
            $cleanPhone = preg_replace('/[^0-9]/', '', $data['customer_contact']);
            $businessName = $draw->business->name ?? (auth()->user()->business->name ?? 'Our Store');
            $campaignTitle = $draw->title;
            $customerName = trim($data['customer_name']);
            $sourceUrl = url('/');

            $waMessage = "Hello {$customerName}!\n\n🎉 You have received an exclusive Lucky Draw Scratch Card ({$campaignTitle}) from {$businessName}. Scratch now to win exciting prizes.\n\n{$scratchUrl}\n\nSource: {$sourceUrl}";
            $whatsappUrl = "https://api.whatsapp.com/send?phone={$cleanPhone}&text=" . urlencode($waMessage);

            return [
                'entry_id'          => $entry->id,
                'customer_name'     => $entry->customer_name,
                'customer_contact'  => $entry->customer_contact,
                'won_prize_id'      => $entry->won_prize_id,
                'won_prize_name'    => $wonPrize ? $wonPrize->name : 'N/A',
                'status'            => $entry->status,
                'scratch_url'       => $scratchUrl,
                'whatsapp_url'      => $whatsappUrl,
                'cost_per_scratch'  => $costPerScratch,
                'remaining_credits' => $this->creditService->getAvailableCredit($businessId),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update customer entry details.
     */
    public function updateEntry(LuckyDraw $draw, int $entryId, array $data, bool $ignorePrizeChange = false): array
    {
        $entry = LuckyDrawEntry::where('lucky_draw_id', $draw->id)->findOrFail($entryId);

        if ($entry->status === 'scratched' || $entry->status === 'claimed') {
            throw new \Exception('This card has already been scratched and cannot be edited.');
        }

        if (!$ignorePrizeChange) {
            if (!empty($data['won_prize_id'])) {
                $prizeExists = LuckyDrawPrize::where('lucky_draw_id', $draw->id)
                    ->where('win_percentage', '>', 0)
                    ->where('id', $data['won_prize_id'])
                    ->exists();

                if (!$prizeExists) {
                    throw new \Exception('Selected prize is invalid or has 0% win percentage.');
                }
                $entry->won_prize_id = $data['won_prize_id'];
            } else {
                $entry->won_prize_id = null;
            }
        }

        $entry->customer_name = trim($data['customer_name']);
        $entry->customer_contact = trim($data['customer_contact']);
        $entry->save();
        $entry->load('wonPrize');

        $businessSlug = $draw->business->slug ?? (auth()->user()->business->slug ?? 'store');
        $scratchUrl = route('lucky-draw.scratch', ['business_slug' => $businessSlug, 'token' => $entry->token]);
        $cleanPhone = preg_replace('/[^0-9]/', '', $entry->customer_contact);
        $businessName = $draw->business->name ?? (auth()->user()->business->name ?? 'Our Store');
        $campaignTitle = $draw->title;
        $customerName = $entry->customer_name;
        $sourceUrl = url('/');

        $waMessage = "Hello {$customerName}!\n\n🎉 You have received an exclusive Lucky Draw Scratch Card ({$campaignTitle}) from {$businessName}. Scratch now to win exciting prizes.\n\n{$scratchUrl}\n\nSource: {$sourceUrl}";
        $whatsappUrl = "https://api.whatsapp.com/send?phone={$cleanPhone}&text=" . urlencode($waMessage);

        return [
            'id'               => $entry->id,
            'customer_name'    => $entry->customer_name,
            'customer_contact' => $entry->customer_contact,
            'won_prize_id'     => $entry->won_prize_id,
            'won_prize_name'   => $entry->wonPrize ? $entry->wonPrize->name : 'N/A',
            'status'           => $entry->status,
            'scratch_url'      => $scratchUrl,
            'whatsapp_url'     => $whatsappUrl,
        ];
    }

    /**
     * Get entry by token for scratch card page.
     */
    public function getEntryByToken(string $token): LuckyDrawEntry
    {
        return LuckyDrawEntry::where('token', $token)
            ->with(['luckyDraw.business', 'wonPrize'])
            ->firstOrFail();
    }

    /**
     * Claim scratch prize.
     */
    public function claimScratch(string $token, ?int $scratchedCardIndex = null): LuckyDrawEntry
    {
        try {
            DB::beginTransaction();

            $entry = LuckyDrawEntry::where('token', $token)
                ->with('wonPrize')
                ->lockForUpdate()
                ->firstOrFail();

            if ($entry->status === 'pending') {
                $entry->status = 'scratched';
                $entry->scratched_at = now();
                if (!is_null($scratchedCardIndex)) {
                    $entry->scratched_card_index = (int) $scratchedCardIndex;
                }
                $entry->save();

                if ($entry->wonPrize) {
                    $entry->wonPrize->increment('total_won');
                }
            }

            DB::commit();

            return $entry;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
