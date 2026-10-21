<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\LuckyDrawRepository;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PosLuckyDrawController extends Controller
{
    public function __construct(
        protected LuckyDrawRepository $luckyDrawRepository,
        protected CreditService $creditService
    ) {}

    private function checkAccess($permission = null)
    {
        $businessId = getPosBusinessId();
        $setting = getBusinessSettings($businessId);

        if (empty($setting->is_lucky_draw)) {
            abort(403, 'Lucky Draw module is disabled for your business.');
        }

        if ($permission) {
            if ($permission === 'view_lucky_draw') {
                if (!checkPosPermission('view_lucky_draw') && !checkPosPermission('view_all_lucky_draw')) {
                    abort(403, 'You do not have permission to perform this action.');
                }
            } else {
                if (!checkPosPermission($permission)) {
                    abort(403, 'You do not have permission to perform this action.');
                }
            }
        }
    }

    /**
     * Display POS Lucky Draw management dashboard.
     */
    public function index(Request $request)
    {
        $this->checkAccess('view_lucky_draw');

        $businessId = getPosBusinessId();
        $userId = Auth::guard('pos')->id() ?? Auth::id();

        if ($request->ajax()) {
            $createdById = checkPosPermission('view_all_lucky_draw') ? null : $userId;
            $drawId = $request->filled('lucky_draw_id') ? (int) $request->lucky_draw_id : null;

            $entriesQuery = $this->luckyDrawRepository->getBusinessEntriesQuery($businessId, $createdById, $drawId);

            $business = Auth::guard('pos')->user()->getBusinessDetails ?? null;
            $businessSlug = $business->slug ?? 'store';
            $businessName = $business->name ?? 'Our Store';
            $sourceUrl = url('/');

            return DataTables::of($entriesQuery)
                ->addIndexColumn()
                ->editColumn('customer_name', function ($row) {
                    return '<span class="fw-bold text-dark">' . e($row->customer_name) . '</span>';
                })
                ->addColumn('contact', function ($row) {
                    return '<i class="bi bi-telephone text-muted me-1"></i> ' . e($row->customer_contact);
                })
                ->addColumn('campaign', function ($row) {
                    return '<span class="badge bg-light text-dark border px-3 py-1 rounded-pill">' . e($row->luckyDraw->title ?? 'N/A') . '</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    if ($row->status === 'scratched' || $row->status === 'claimed') {
                        return '<span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill"><i class="bi bi-check-circle me-1"></i> Scratched</span>';
                    }
                    return '<span class="badge bg-warning-subtle text-warning px-3 py-1 rounded-pill"><i class="bi bi-clock me-1"></i> Pending Scratch</span>';
                })
                ->addColumn('prize_result', function ($row) {
                    if ($row->status === 'scratched' || $row->status === 'claimed') {
                        if ($row->wonPrize) {
                            return '<span class="fw-bold text-success"><i class="bi bi-trophy-fill me-1 text-warning"></i> ' . e($row->wonPrize->name) . '</span>';
                        }
                        return '<span class="text-muted small">Better Luck Next Time</span>';
                    }
                    return '<span class="text-muted small"><i class="bi bi-eye-slash me-1"></i> Hidden till scratched</span>';
                })
                ->addColumn('created_info', function ($row) {
                    $creator = $row->createdBy ? trim($row->createdBy->first_name . ' ' . $row->createdBy->last_name) : 'System';
                    return '<small class="text-muted d-block">' . $row->created_at->format('M d, Y h:i A') . '</small><small class="text-secondary fw-semibold">by ' . e($creator) . '</small>';
                })
                ->addColumn('action', function ($row) use ($businessSlug, $businessName, $sourceUrl) {
                    $campaignTitle = $row->luckyDraw->title ?? 'Lucky Draw';
                    $scratchUrl = route('lucky-draw.scratch', ['business_slug' => $businessSlug, 'token' => $row->token]);
                    $cleanPhone = preg_replace('/[^0-9]/', '', $row->customer_contact);
                    $waMsg = "Hello {$row->customer_name}!\n\n🎉 You have received an exclusive Lucky Draw Scratch Card ({$campaignTitle}) from {$businessName}. Scratch now to win exciting prizes.\n\n{$scratchUrl}\n\nSource: {$sourceUrl}";
                    $waLink = "https://api.whatsapp.com/send?phone={$cleanPhone}&text=" . urlencode($waMsg);

                    $canEdit = checkPosPermission('edit_lucky_draw');

                    $html = '<div class="text-end pe-2">';
                    if ($canEdit && $row->status === 'pending') {
                        $html .= '<button class="btn btn-sm btn-outline-primary rounded-circle edit-entry-btn me-1"
                            data-id="' . $row->id . '"
                            data-draw-id="' . $row->lucky_draw_id . '"
                            data-name="' . e($row->customer_name) . '"
                            data-contact="' . e($row->customer_contact) . '"
                            data-url="' . e($scratchUrl) . '"
                            data-wa-url="' . e($waLink) . '">
                            <i class="bi bi-pencil"></i>
                        </button>';
                    }
                    $html .= '<button class="btn btn-sm btn-outline-secondary rounded-circle copy-url-btn me-1" data-url="' . e($scratchUrl) . '" title="Copy Scratch URL">
                        <i class="bi bi-copy"></i>
                    </button>
                    <a href="' . e($waLink) . '" target="_blank" class="btn btn-sm btn-success rounded-circle" title="Share via WhatsApp">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                    </div>';

                    return $html;
                })
                ->rawColumns(['customer_name', 'contact', 'campaign', 'status_badge', 'prize_result', 'created_info', 'action'])
                ->make(true);
        }

        $activeDraws = $this->luckyDrawRepository->getActiveDrawsForBusiness($businessId);
        $costPerScratch = $this->creditService->getLuckyDrawCharge();

        return view('pos.lucky_draw.index', compact('activeDraws', 'costPerScratch'));
    }

    /**
     * Generate customer scratch link in POS.
     */
    public function generateEntry(Request $request)
    {
        $businessId = getPosBusinessId();
        $setting = getBusinessSettings($businessId);

        if (empty($setting->is_lucky_draw)) {
            abort(403, 'Lucky Draw module is disabled for your business.');
        }

        $canGenerate = checkPosPermission('generate_lucky_draw')
            || checkPosPermission('view_all_lucky_draw')
            || ($request->get('for') === 'order' && checkPosPermission('create_lucky_draw_quotation'));

        if (!$canGenerate) {
            abort(403, 'You do not have permission to perform this action.');
        }

        $validator = Validator::make($request->all(), [
            'lucky_draw_id' => 'required|exists:lucky_draws,id',
            'customer_name' => 'required|string|max:255',
            'customer_contact' => 'required|digits:10',
            'for' => 'nullable|in:other,order',
            'source_id' => 'nullable',
        ], [
            'customer_contact.digits' => 'Customer phone number must be exactly 10 digits.',
            'customer_contact.required' => 'Customer phone number is required.',
            'lucky_draw_id.required' => 'Please select a lucky draw campaign.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        if ($request->get('for') === 'order' && $request->filled('source_id')) {
            $existing = \App\Models\LuckyDrawEntry::where('for', 'order')
                ->where('source_id', $request->source_id)
                ->first();
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'A Lucky Draw link has already been created for this order.'
                ]);
            }
        }

        $draw = $this->luckyDrawRepository->getDrawById($businessId, $request->lucky_draw_id);

        try {
            $userId = Auth::guard('pos')->id() ?? Auth::id();
            
            // Ensure no won_prize_id is set manually from POS (it is strictly random)
            $inputData = $request->only(['customer_name', 'customer_contact', 'for', 'source_id']);

            $result = $this->luckyDrawRepository->generateEntry($draw, $businessId, $inputData, $userId);

            return response()->json(array_merge([
                'success' => true,
                'message' => 'Scratch Card URL generated successfully! ' . $result['cost_per_scratch'] . ' credit(s) deducted.'
            ], $result));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => checkResponse($e)]);
        }
    }

    /**
     * Update customer entry details in POS.
     */
    public function updateEntry(Request $request, $id)
    {
        $this->checkAccess('edit_lucky_draw');

        $validator = Validator::make($request->all(), [
            'lucky_draw_id' => 'required|exists:lucky_draws,id',
            'customer_name' => 'required|string|max:255',
            'customer_contact' => 'required|digits:10',
        ], [
            'customer_contact.digits' => 'Customer phone number must be exactly 10 digits.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $businessId = getPosBusinessId();
        $draw = $this->luckyDrawRepository->getDrawById($businessId, $request->lucky_draw_id);

        try {
            $inputData = $request->only(['customer_name', 'customer_contact']);
            $entryData = $this->luckyDrawRepository->updateEntry($draw, $id, $inputData, true);

            return response()->json([
                'success' => true,
                'message' => 'Entry details updated successfully!',
                'entry' => $entryData
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => checkResponse($e)]);
        }
    }
}
