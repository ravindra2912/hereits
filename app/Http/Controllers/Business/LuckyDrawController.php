<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\LuckyDrawRepository;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class LuckyDrawController extends Controller
{
    public function __construct(
        protected LuckyDrawRepository $luckyDrawRepository,
        protected CreditService $creditService
    ) {}

    private function checkModuleEnabled($permission = null)
    {
        $setting = getBusinessSettings();
        if (empty($setting->is_lucky_draw)) {
            abort(403, 'Lucky Draw module is disabled for your business.');
        }

        if ($permission && !checkBusinessPermission($permission)) {
            abort(403, 'You do not have permission to perform this action.');
        }
    }

    /**
     * Display listing of lucky draws.
     */
    public function index(Request $request)
    {
        $this->checkModuleEnabled('view_lucky_draw');
        $business_id = getBusinessId();

        if ($request->ajax()) {
            $drawsQuery = $this->luckyDrawRepository->getDrawsQuery($business_id);

            return DataTables::of($drawsQuery)
                ->addIndexColumn()
                ->editColumn('title', function ($row) {
                    $html = '<a href="' . route('business.lucky-draw.show', $row->id) . '" class="text-decoration-none text-dark fw-bold">' . e($row->title) . '</a>';
                    if ($row->description) {
                        $html .= '<small class="text-muted d-block font-weight-normal text-truncate" style="max-width: 250px;">' . e($row->description) . '</small>';
                    }
                    return $html;
                })
                ->addColumn('type_badge', function ($row) {
                    if ($row->type === 'spinner') {
                        return '<span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill"><i class="bi bi-disc me-1"></i> Spinner</span>';
                    }
                    return '<span class="badge bg-warning-subtle text-warning px-3 py-1 rounded-pill"><i class="bi bi-ticket-perforated me-1"></i> Scratch Card</span>';
                })
                ->addColumn('prizes_count_badge', function ($row) {
                    return '<span class="badge bg-info-subtle text-info px-3 py-1 rounded-pill"><i class="bi bi-gift me-1"></i> ' . $row->prizes_count . ' Prize(s)</span>';
                })
                ->addColumn('entries_count_badge', function ($row) {
                    return '<span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill"><i class="bi bi-people me-1"></i> ' . $row->entries_count . ' Sent</span>';
                })
                ->addColumn('dates_formatted', function ($row) {
                    $start = $row->start_date ? $row->start_date->format('M d, Y') : 'Anytime';
                    $end = $row->end_date ? $row->end_date->format('M d, Y') : 'Ongoing';
                    return '<small class="text-muted">' . $start . ' - ' . $end . '</small>';
                })
                ->addColumn('status_badge', function ($row) {
                    if (!checkBusinessPermission('edit_lucky_draw')) {
                        return $row->status === 'active'
                            ? '<span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill">Active</span>'
                            : '<span class="badge bg-danger-subtle text-danger px-3 py-1 rounded-pill">In-Active</span>';
                    }
                    $checked = $row->status === 'active' ? 'checked' : '';
                    return '<div class="d-flex justify-content-center m-0">
                                <div class="form-check form-switch text-switch m-0 p-0">
                                    <input class="form-check-input status-toggle m-0" type="checkbox" role="switch" data-id="' . $row->id . '" data-on="Active" data-off="In-Active" ' . $checked . '>
                                </div>
                            </div>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="text-end pe-2">';
                    $btn .= '<a href="' . route('business.lucky-draw.show', $row->id) . '" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1">
                                <i class="bi bi-eye me-1"></i> View
                            </a>';
                    if (checkBusinessPermission('delete_lucky_draw')) {
                        $btn .= '<button class="btn btn-sm btn-outline-danger rounded-circle delete-draw-btn" data-id="' . $row->id . '">
                                    <i class="bi bi-trash"></i>
                                </button>';
                    }
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['title', 'type_badge', 'prizes_count_badge', 'entries_count_badge', 'dates_formatted', 'status_badge', 'action'])
                ->make(true);
        }

        $costPerScratch = $this->creditService->getLuckyDrawCharge();

        return view('business.lucky_draw.index', compact('costPerScratch'));
    }

    /**
     * Store new lucky draw campaign.
     */
    public function store(Request $request)
    {
        $this->checkModuleEnabled('add_lucky_draw');
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:scratch,spinner',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $business_id = getBusinessId();
        $this->luckyDrawRepository->createDraw($business_id, $request->all());

        return response()->json(['success' => true, 'message' => 'Lucky draw campaign created successfully!']);
    }

    /**
     * Show detailed view of a campaign (Prizes & Entry History).
     */
    public function show(Request $request, $id)
    {
        $this->checkModuleEnabled('view_lucky_draw');
        $business_id = getBusinessId();
        $draw = $this->luckyDrawRepository->getDrawById($business_id, $id, [
            'prizes',
            'entries' => function ($q) {
                $q->with('wonPrize')->latest();
            }
        ]);

        if ($request->ajax()) {
            if ($request->get('type') === 'prizes') {
                $prizesQuery = $this->luckyDrawRepository->getPrizesQuery($draw->id);

                return DataTables::of($prizesQuery)
                    ->addIndexColumn()
                    ->addColumn('prize_item', function ($row) {
                        $img = e($row->image_url);
                        $name = e($row->name);
                        return '
                        <div class="d-flex align-items-center">
                            <img src="' . $img . '" alt="' . $name . '" class="rounded-3 me-3" style="width: 48px; height: 48px; object-fit: cover;">
                            <div>
                                <h6 class="fw-bold mb-0">' . $name . '</h6>
                            </div>
                        </div>';
                    })
                    ->addColumn('win_percentage_badge', function ($row) {
                        return '<span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1 rounded-pill">' . number_format($row->win_percentage, 2) . '%</span>';
                    })
                    ->addColumn('times_claimed', function ($row) {
                        return '<span class="fw-bold text-success">' . (int) $row->total_won . '</span> item(s)';
                    })
                    ->addColumn('action', function ($row) {
                        $canEdit = checkBusinessPermission('edit_lucky_draw');
                        $canDelete = checkBusinessPermission('delete_lucky_draw');
                        if (!$canEdit && !$canDelete) {
                            return '<div class="text-end pe-2 text-muted small">-</div>';
                        }
                        $btn = '<div class="text-end pe-2">';
                        if ($canEdit) {
                            $btn .= '<button class="btn btn-sm btn-outline-primary rounded-circle edit-prize-btn me-1"
                                data-id="' . $row->id . '"
                                data-name="' . e($row->name) . '"
                                data-percentage="' . e($row->win_percentage) . '"
                                data-image="' . e($row->image_url) . '">
                                <i class="bi bi-pencil"></i>
                            </button>';
                        }
                        if ($canDelete) {
                            $btn .= '<button class="btn btn-sm btn-outline-danger rounded-circle delete-prize-btn" data-id="' . $row->id . '">
                                <i class="bi bi-trash"></i>
                            </button>';
                        }
                        $btn .= '</div>';
                        return $btn;
                    })
                    ->rawColumns(['prize_item', 'win_percentage_badge', 'times_claimed', 'action'])
                    ->make(true);
            }

            $entriesQuery = $this->luckyDrawRepository->getEntriesQuery($draw->id);

            $businessSlug = $draw->business->slug ?? (auth()->user()->business->slug ?? 'store');
            $businessName = $draw->business->name ?? (auth()->user()->business->name ?? 'Our Store');
            $campaignTitle = $draw->title;
            $sourceUrl = url('/');

            return DataTables::of($entriesQuery)
                ->addIndexColumn()
                ->editColumn('customer_name', function ($row) {
                    return '<span class="fw-bold text-dark">' . e($row->customer_name) . '</span>';
                })
                ->addColumn('contact', function ($row) {
                    return '<i class="bi bi-telephone text-muted me-1"></i> ' . e($row->customer_contact);
                })
                ->addColumn('status_badge', function ($row) {
                    if ($row->status === 'scratched' || $row->status === 'claimed') {
                        return '<span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill"><i class="bi bi-check-circle me-1"></i> Scratched</span>';
                    }
                    return '<span class="badge bg-warning-subtle text-warning px-3 py-1 rounded-pill"><i class="bi bi-clock me-1"></i> Pending Scratch</span>';
                })
                ->addColumn('prize_result', function ($row) {
                    if ($row->wonPrize) {
                        return '<span class="fw-bold text-success"><i class="bi bi-trophy-fill me-1 text-warning"></i> ' . e($row->wonPrize->name) . '</span>';
                    }
                    return '<span class="text-muted small">Not revealed yet</span>';
                })
                ->addColumn('scratched_at_formatted', function ($row) {
                    return '<small class="text-muted">' . ($row->scratched_at ? $row->scratched_at->format('M d, Y h:i A') : '-') . '</small>';
                })
                ->addColumn('action', function ($row) use ($businessSlug, $businessName, $campaignTitle, $sourceUrl) {
                    $scratchUrl = route('lucky-draw.scratch', ['business_slug' => $businessSlug, 'token' => $row->token]);
                    $cleanPhone = preg_replace('/[^0-9]/', '', $row->customer_contact);
                    $waMsg = "Hello {$row->customer_name}!\n\n🎉 You have received an exclusive Lucky Draw Scratch Card ({$campaignTitle}) from {$businessName}. Scratch now to win exciting prizes.\n\n{$scratchUrl}\n\nSource: {$sourceUrl}";
                    $waLink = "https://api.whatsapp.com/send?phone={$cleanPhone}&text=" . urlencode($waMsg);
                    $wonPrizeName = $row->wonPrize ? $row->wonPrize->name : 'N/A';
                    $canEdit = checkBusinessPermission('edit_lucky_draw');

                    $btn = '<div class="text-end pe-2">';
                    if ($canEdit) {
                        $btn .= '<button class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1 edit-entry-btn"
                            data-id="' . $row->id . '"
                            data-name="' . e($row->customer_name) . '"
                            data-contact="' . e($row->customer_contact) . '"
                            data-status="' . e($row->status) . '"
                            data-won-prize-id="' . e($row->won_prize_id) . '"
                            data-won-prize-name="' . e($wonPrizeName) . '"
                            data-url="' . e($scratchUrl) . '"
                            data-wa-url="' . e($waLink) . '">
                            <i class="bi bi-pencil"></i>
                        </button>';
                    }
                    $btn .= '<button class="btn btn-sm btn-outline-secondary rounded-pill px-3 me-1 copy-url-btn" data-url="' . e($scratchUrl) . '">
                            <i class="bi bi-copy"></i>
                        </button>
                        <a href="' . e($waLink) . '" target="_blank" class="btn btn-sm btn-success rounded-pill px-3">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                    </div>';
                    return $btn;
                })
                ->rawColumns(['customer_name', 'contact', 'status_badge', 'prize_result', 'scratched_at_formatted', 'action'])
                ->make(true);
        }

        $costPerScratch = $this->creditService->getLuckyDrawCharge();
        $totalProbability = $draw->prizes->sum('win_percentage');
        $availableCampaigns = $this->luckyDrawRepository->getAvailableCampaignsForImport($business_id, $draw->id);

        return view('business.lucky_draw.show', compact('draw', 'costPerScratch', 'totalProbability', 'availableCampaigns'));
    }

    /**
     * Update lucky draw campaign details.
     */
    public function update(Request $request, $id)
    {
        $this->checkModuleEnabled('edit_lucky_draw');
        $business_id = getBusinessId();
        $draw = $this->luckyDrawRepository->getDrawById($business_id, $id);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:scratch,spinner',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:active,in-active',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $this->luckyDrawRepository->updateDraw($draw, $request->all());

        return response()->json(['success' => true, 'message' => 'Campaign updated successfully!']);
    }

    /**
     * Change status of a lucky draw campaign directly via AJAX.
     */
    public function changeStatus(Request $request, $id)
    {
        $this->checkModuleEnabled('edit_lucky_draw');
        $business_id = getBusinessId();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,in-active',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $draw = $this->luckyDrawRepository->getDrawById($business_id, $id);
        $draw->status = $request->status;
        $draw->save();

        return response()->json(['success' => true, 'message' => 'Campaign status updated successfully!']);
    }

    /**
     * Delete campaign.
     */
    public function destroy($id)
    {
        $this->checkModuleEnabled('delete_lucky_draw');
        $business_id = getBusinessId();
        $draw = $this->luckyDrawRepository->getDrawById($business_id, $id);

        $this->luckyDrawRepository->deleteDraw($draw);

        return response()->json(['success' => true, 'message' => 'Campaign and associated prizes deleted successfully!']);
    }

    /**
     * Add or Update prize for a lucky draw.
     */
    public function storePrize(Request $request, $id)
    {
        $this->checkModuleEnabled('add_lucky_draw');
        $business_id = getBusinessId();
        $draw = $this->luckyDrawRepository->getDrawById($business_id, $id);

        $validator = Validator::make($request->all(), [
            'prize_id' => 'nullable|exists:lucky_draw_prizes,id',
            'name' => 'required|string|max:255',
            'win_percentage' => 'required|numeric|min:0|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $imageFile = $request->hasFile('image') ? $request->file('image') : null;
        $this->luckyDrawRepository->storePrize($draw, $request->all(), $imageFile);

        return response()->json(['success' => true, 'message' => 'Prize saved successfully!']);
    }

    /**
     * Delete a prize.
     */
    public function deletePrize($id, $prizeId)
    {
        $this->checkModuleEnabled('delete_lucky_draw');
        $business_id = getBusinessId();
        $draw = $this->luckyDrawRepository->getDrawById($business_id, $id);

        $this->luckyDrawRepository->deletePrize($draw, $prizeId);

        return response()->json(['success' => true, 'message' => 'Prize removed successfully!']);
    }

    /**
     * Get prizes of a source campaign for import modal.
     */
    public function getSourcePrizes(Request $request, $id, $sourceId)
    {
        $this->checkModuleEnabled('add_lucky_draw');
        $business_id = getBusinessId();

        try {
            $prizes = $this->luckyDrawRepository->getSourceCampaignPrizes($business_id, (int) $sourceId);

            return response()->json([
                'success' => true,
                'prizes'  => $prizes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load prizes from selected campaign: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Import / Copy prizes from selected campaign into current campaign.
     */
    public function importPrizes(Request $request, $id)
    {
        $this->checkModuleEnabled('add_lucky_draw');
        $business_id = getBusinessId();
        $draw = $this->luckyDrawRepository->getDrawById($business_id, $id);

        $validator = Validator::make($request->all(), [
            'source_campaign_id' => 'required|integer|exists:lucky_draws,id',
            'prize_ids'          => 'nullable|array',
            'prize_ids.*'        => 'integer|exists:lucky_draw_prizes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $result = $this->luckyDrawRepository->importPrizesFromCampaign(
                $draw,
                $business_id,
                (int) $request->source_campaign_id,
                $request->prize_ids
            );

            return response()->json([
                'success'           => true,
                'message'           => "{$result['imported_count']} prize(s) successfully imported!",
                'imported_count'    => $result['imported_count'],
                'total_prizes'      => $result['total_prizes'],
                'total_probability' => $result['total_probability'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => checkResponse($e)]);
        }
    }

    /**
     * Generate customer scratch card URL & deduct credit.
     */
    public function generateEntry(Request $request, $id)
    {
        $this->checkModuleEnabled('add_lucky_draw');
        $business_id = getBusinessId();
        $draw = $this->luckyDrawRepository->getDrawById($business_id, $id);

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_contact' => 'required|digits:10',
            'won_prize_id' => 'nullable|exists:lucky_draw_prizes,id',
        ], [
            'customer_contact.digits' => 'Customer phone number must be exactly 10 digits.',
            'customer_contact.required' => 'Customer phone number is required.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $result = $this->luckyDrawRepository->generateEntry($draw, $business_id, $request->all());

            return response()->json(array_merge(['success' => true, 'message' => 'Scratch Card URL generated successfully! ' . $result['cost_per_scratch'] . ' credit(s) deducted.'], $result));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => checkResponse($e)]);
        }
    }

    /**
     * Update customer entry (details & winning prize) before scratched.
     */
    public function updateEntry(Request $request, $id, $entry_id)
    {
        $this->checkModuleEnabled('edit_lucky_draw');
        $business_id = getBusinessId();
        $draw = $this->luckyDrawRepository->getDrawById($business_id, $id);

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_contact' => 'required|digits:10',
            'won_prize_id' => 'nullable|exists:lucky_draw_prizes,id',
        ], [
            'customer_contact.digits' => 'Customer phone number must be exactly 10 digits.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        try {
            $entryData = $this->luckyDrawRepository->updateEntry($draw, $entry_id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Entry updated successfully!',
                'entry' => $entryData
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => checkResponse($e)]);
        }
    }
}
