<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Repositories\Eloquent\QuotationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PosQuotationController extends Controller
{
    public function __construct(
        protected QuotationRepository $quotationRepository
    ) {}

    public function index(Request $request)
    {
        if (!checkPosPermission('view_quotation') && !checkPosPermission('view_all_quotations')) {
            abort(403, 'You do not have permission to view quotations.');
        }

        $businessId = getPosBusinessId();
        $userId = Auth::guard('pos')->id() ?? Auth::id();

        if ($request->ajax()) {
            $createdById = !checkPosPermission('view_all_quotations') ? $userId : null;

            $data = $this->quotationRepository->getQueryForDataTable(
                $businessId,
                $request->status,
                $request->start_date,
                $request->end_date,
                $createdById,
                $request->created_from
            );

            return DataTables::of($data)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    if ($keyword = $request->input('search.value')) {
                        $query->where(function ($q) use ($keyword) {
                            $q->where('quotations.quotation_no', 'like', "%{$keyword}%")
                              ->orWhere('quotations.customer_name', 'like', "%{$keyword}%")
                              ->orWhere('quotations.customer_contact', 'like', "%{$keyword}%")
                              ->orWhereHas('customer', function ($cq) use ($keyword) {
                                  $cq->where('customer_name', 'like', "%{$keyword}%")
                                     ->orWhere('contact', 'like', "%{$keyword}%")
                                     ->orWhere('business_name', 'like', "%{$keyword}%");
                              });
                        });
                    }
                })
                ->filterColumn('quotation_info', function ($query, $keyword) {
                    $query->where('quotations.quotation_no', 'like', "%{$keyword}%");
                })
                ->filterColumn('customer_info', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('quotations.customer_name', 'like', "%{$keyword}%")
                          ->orWhere('quotations.customer_contact', 'like', "%{$keyword}%")
                          ->orWhereHas('customer', function ($cq) use ($keyword) {
                              $cq->where('customer_name', 'like', "%{$keyword}%")
                                 ->orWhere('contact', 'like', "%{$keyword}%")
                                 ->orWhere('business_name', 'like', "%{$keyword}%");
                          });
                    });
                })
                ->addColumn('quotation_info', function ($row) {
                    $createdFrom = $row->created_from ?? 'pos';
                    $badge = ($createdFrom === 'inquiry')
                        ? '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0 ms-1 small" title="Created from WhatsApp Inquiry"><i class="bi bi-whatsapp me-1"></i>Inquiry</span>'
                        : '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0 ms-1 small" title="Created from POS"><i class="bi bi-shop me-1"></i>POS</span>';

                    return '<div>
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <span class="fw-bold text-dark">' . e($row->quotation_no) . '</span>
                            ' . $badge . '
                        </div>
                        <div class="small text-muted">' . $row->created_at->format('d M Y, h:i A') . '</div>
                    </div>';
                })
                ->addColumn('customer_info', function ($row) {
                    $name = $row->customer ? $row->customer->customer_name : ($row->customer_name ?: 'Walk-in Customer');
                    $contact = $row->customer_contact ?: ($row->customer->contact ?? 'N/A');
                    return '<div>
                        <div class="fw-bold text-dark">' . e($name) . '</div>
                        <div class="small text-muted">' . e($contact) . '</div>
                    </div>';
                })
                ->addColumn('amount_info', function ($row) {
                    return '<div class="fw-bold text-dark">₹' . number_format($row->total, 2) . '</div>';
                })
                ->addColumn('status_info', function ($row) {
                    $status_colors = [
                        'pending' => 'bg-info text-white',
                        'inprogress' => 'bg-warning text-dark',
                        'ordered' => 'bg-success',
                        'cancel' => 'bg-danger',
                        'expired' => 'bg-secondary'
                    ];
                    $class = $status_colors[$row->status] ?? 'bg-secondary';
                    $label = match($row->status) {
                        'pending' => 'Pending',
                        'inprogress' => 'In Progress',
                        default => ucfirst($row->status),
                    };
                    return '<span class="badge rounded-pill ' . $class . ' px-3 py-1 small">' . e($label) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="d-flex justify-content-end gap-1">';

                    if (checkPosPermission('view_quotation') || checkPosPermission('view_all_quotations')) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-primary rounded-circle view-quote-btn" data-id="' . $row->id . '" title="View"><i class="bi bi-eye"></i></button>';
                    }

                    if (checkPosPermission('create_quotation')) {
                        $btn .= '<button type="button" class="btn btn-sm btn-outline-info rounded-circle duplicate-quote-btn" data-id="' . $row->id . '" title="Duplicate Quotation"><i class="bi bi-files"></i></button>';
                    }

                    if (in_array($row->status, ['pending', 'inprogress'])) {
                        if (checkPosPermission('update_quotation')) {
                            $btn .= '<button type="button" class="btn btn-sm btn-outline-secondary rounded-circle edit-quote-btn" data-id="' . $row->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                        }
                        if (checkPosPermission('place_order_quotation')) {
                            $btn .= '<button type="button" class="btn btn-sm btn-outline-success rounded-circle convert-quote-btn" data-id="' . $row->id . '" title="Convert to Order"><i class="bi bi-cart-plus"></i></button>';
                        }
                        if (checkPosPermission('cancel_quotation')) {
                            $btn .= '<button type="button" class="btn btn-sm btn-outline-danger rounded-circle cancel-quote-btn" data-id="' . $row->id . '" title="Cancel Quotation"><i class="bi bi-x-circle"></i></button>';
                        }
                    }

                    $isLuckyDrawEnabled = !empty(getBusinessSettings($row->business_id)->is_lucky_draw);
                    if ($row->status === 'ordered' && !empty($row->order_id) && $isLuckyDrawEnabled && checkPosPermission('create_lucky_draw_quotation')) {
                        $hasLuckyDraw = !empty($row->luckyDrawEntry);
                        if (!$hasLuckyDraw) {
                            $custName = $row->customer_name ?: ($row->customer ? $row->customer->customer_name : '');
                            $custContact = $row->customer_contact ?: ($row->customer ? $row->customer->contact : '');
                            $orderId = $row->order_id;
                            $btn .= '<button type="button" class="btn btn-sm btn-outline-warning rounded-circle create-lucky-draw-quote-btn" data-id="' . $row->id . '" data-order-id="' . $orderId . '" data-name="' . e($custName) . '" data-contact="' . e($custContact) . '" title="Create Lucky Draw"><i class="bi bi-gift"></i></button>';
                        }
                    }

                    if (checkPosPermission('delete_quotation')) {
                        $btn .= '<button type="button" onclick="destroyQuotation(' . $row->id . ')" class="btn btn-sm btn-outline-danger rounded-circle" title="Delete"><i class="bi bi-trash"></i></button>';
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['quotation_info', 'customer_info', 'amount_info', 'status_info', 'action'])
                ->make(true);
        }

        $activeDraws = [];
        $costPerScratch = 1;
        $businessSetting = getBusinessSettings($businessId);
        if (!empty($businessSetting->is_lucky_draw)) {
            $activeDraws = app(\App\Repositories\Eloquent\LuckyDrawRepository::class)->getActiveDrawsForBusiness($businessId);
            $costPerScratch = app(\App\Services\CreditService::class)->getLuckyDrawCharge();
        }

        return view('pos.quotation.index', compact('activeDraws', 'costPerScratch'));
    }

    public function create(Request $request)
    {
        if (!checkPosPermission('create_quotation')) {
            abort(403, 'You do not have permission to create quotations.');
        }

        $businessId = getPosBusinessId();
        $businessSetting = getBusinessSettings($businessId);
        $activeCustomerGroups = collect();
        if ($businessSetting && $businessSetting->is_offer_with_customer_group) {
            $activeCustomerGroups = \App\Models\BusinessCustomerRole::where('business_id', $businessId)
                ->where('status', 'active')
                ->get();
        }

        $quotationCharge = $this->quotationRepository->getQuotationCharge($businessId);

        if ($request->ajax()) {
            $html = view('pos.quotation.partials.create_modal', compact('businessSetting', 'activeCustomerGroups', 'quotationCharge'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        }

        return redirect()->route('pos.quotation.index');
    }

    public function store(StoreQuotationRequest $request)
    {
        if (!checkPosPermission('create_quotation')) {
            abort(403, 'You do not have permission to create quotations.');
        }

        $businessId = getPosBusinessId();
        $userId = Auth::guard('pos')->id() ?? Auth::id();

        try {
            $quotation = $this->quotationRepository->createQuotation($request->validated(), $businessId, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Quotation #' . $quotation->quotation_no . ' created successfully!',
                'redirect' => route('pos.quotation.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => checkResponse($e)
            ], 422);
        }
    }

    public function show(Request $request, $id)
    {
        if (!checkPosPermission('view_quotation') && !checkPosPermission('view_all_quotations')) {
            abort(403, 'You do not have permission to view quotations.');
        }

        $businessId = getPosBusinessId();
        $userId = Auth::guard('pos')->id() ?? Auth::id();
        $quotation = $this->quotationRepository->findById($id, $businessId);

        if (!checkPosPermission('view_all_quotations') && (int)$quotation->created_by_id !== (int)$userId) {
            abort(403, 'You do not have permission to view this quotation.');
        }

        if ($request->ajax()) {
            $html = view('pos.quotation.partials.show_modal', compact('quotation'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        }

        return redirect()->route('pos.quotation.index');
    }

    public function pdf($id)
    {
        if (!checkPosPermission('view_quotation') && !checkPosPermission('view_all_quotations')) {
            abort(403, 'You do not have permission to view quotations.');
        }

        $businessId = getPosBusinessId();
        $userId = Auth::guard('pos')->id() ?? Auth::id();
        $quotation = $this->quotationRepository->findById($id, $businessId);

        if (!checkPosPermission('view_all_quotations') && (int)$quotation->created_by_id !== (int)$userId) {
            abort(403, 'You do not have permission to view this quotation.');
        }

        $business = $quotation->business;
        $businessSetting = $business ? $business->businessSetting : null;
        $quotationPolicy = $business && $business->leaglePages ? $business->leaglePages->where('type', 'quotation_policy')->first() : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pos.quotation.pdf', compact('quotation', 'business', 'businessSetting', 'quotationPolicy'))
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true);

        return $pdf->download('Quotation-' . $quotation->quotation_no . '.pdf');
    }

    private function getStaffList($business_id)
    {
        $business = \App\Models\Business::with('owner')->find($business_id);
        $staffList = collect();
        if ($business && $business->owner) {
            $staffList->push((object)[
                'id' => $business->owner->id,
                'name' => trim($business->owner->first_name . ' ' . $business->owner->last_name) . ' (Owner)'
            ]);
        }
        $businessStaff = \App\Models\BusinessUser::with('user', 'role')->where('business_id', $business_id)->get();
        foreach ($businessStaff as $bs) {
            if ($bs->user && (!$business->owner || $bs->user->id !== $business->owner->id)) {
                $staffList->push((object)[
                    'id' => $bs->user->id,
                    'name' => trim($bs->user->first_name . ' ' . $bs->user->last_name) . ' (' . ($bs->role->name ?? 'Staff') . ')'
                ]);
            }
        }
        return $staffList;
    }

    public function edit(Request $request, $id)
    {
        if (!checkPosPermission('update_quotation')) {
            abort(403, 'You do not have permission to edit quotations.');
        }

        $businessId = getPosBusinessId();
        $quotation = $this->quotationRepository->findById($id, $businessId);
        $selectedCustomer = null;
        $bcId = $quotation->business_customer_id ?: ($quotation->customer_id ?: null);
        if ($bcId) {
            $selectedCustomer = \App\Models\BusinessCustomer::where('business_id', $businessId)
                ->where('id', $bcId)
                ->first();
        }
        if (!$selectedCustomer && $quotation->customer_contact) {
            $rawContact = trim($quotation->customer_contact);
            $cleanContact = preg_replace('/[^0-9]/', '', $rawContact);
            $clean10 = $cleanContact ? substr($cleanContact, -10) : null;

            $selectedCustomer = \App\Models\BusinessCustomer::where('business_id', $businessId)
                ->where(function ($q) use ($rawContact, $cleanContact, $clean10) {
                    $q->where('contact', $rawContact);
                    if ($cleanContact && $cleanContact !== $rawContact) {
                        $q->orWhere('contact', $cleanContact);
                    }
                    if ($clean10) {
                        $q->orWhere('contact', $clean10)
                            ->orWhere('contact', '+91' . $clean10)
                            ->orWhere('contact', '91' . $clean10);
                    }
                })
                ->first();

            if (!$selectedCustomer && $clean10) {
                $linkedUserId = \App\Models\User::where(function ($q) use ($rawContact, $cleanContact, $clean10) {
                    $q->where('contact', $rawContact);
                    if ($cleanContact && $cleanContact !== $rawContact) {
                        $q->orWhere('contact', $cleanContact);
                    }
                    if ($clean10) {
                        $q->orWhere('contact', $clean10)
                            ->orWhere('contact', '+91' . $clean10)
                            ->orWhere('contact', '91' . $clean10);
                    }
                })->value('id');

                if ($linkedUserId) {
                    $selectedCustomer = \App\Models\BusinessCustomer::where('business_id', $businessId)
                        ->where('customer_id', $linkedUserId)
                        ->first();
                }
            }
        }

        $businessSetting = getBusinessSettings($businessId);
        $activeCustomerGroups = collect();
        if ($businessSetting && $businessSetting->is_offer_with_customer_group) {
            $activeCustomerGroups = \App\Models\BusinessCustomerRole::where('business_id', $businessId)
                ->where('status', 'active')
                ->get();
        }

        $staffList = $this->getStaffList($businessId);

        if ($request->ajax()) {
            $html = view('pos.quotation.partials.edit_modal', compact('quotation', 'selectedCustomer', 'businessSetting', 'activeCustomerGroups', 'staffList'))->render();
            return response()->json(['success' => true, 'html' => $html]);
        }

        return redirect()->route('pos.quotation.index');
    }

    public function calculateShipping(Request $request)
    {
        $businessId = getPosBusinessId();
        $items = (array)$request->get('items', []);
        $state = $request->get('state');

        $deliveryCharge = $this->quotationRepository->calculateDeliveryCharge($businessId, $items, $state);

        return response()->json([
            'success' => true,
            'delivery_charge' => number_format($deliveryCharge, 2, '.', '')
        ]);
    }

    public function update(UpdateQuotationRequest $request, $id)
    {
        if (!checkPosPermission('update_quotation')) {
            abort(403, 'You do not have permission to edit quotations.');
        }

        $businessId = getPosBusinessId();
        $quotation = $this->quotationRepository->updateQuotation($id, $businessId, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Quotation #' . $quotation->quotation_no . ' updated successfully!'
        ]);
    }

    public function destroy($id)
    {
        if (!checkPosPermission('delete_quotation')) {
            abort(403, 'You do not have permission to delete quotations.');
        }

        $businessId = getPosBusinessId();
        $this->quotationRepository->deleteQuotation($id, $businessId);

        return response()->json([
            'success' => true,
            'message' => 'Quotation deleted successfully!'
        ]);
    }

    public function convertToOrder($id)
    {
        if (!checkPosPermission('place_order_quotation')) {
            abort(403, 'You do not have permission to convert quotations to orders.');
        }

        $businessId = getPosBusinessId();
        $userId = Auth::guard('pos')->id() ?? Auth::id();

        try {
            $order = $this->quotationRepository->convertToOrder($id, $businessId, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Quotation successfully converted to Order #' . $order->invoice_number
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => checkResponse($e, 'Failed to convert quotation.')
            ], 422);
        }
    }

    public function cancel(Request $request, $id)
    {
        if (!checkPosPermission('cancel_quotation')) {
            abort(403, 'You do not have permission to cancel quotations.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $businessId = getPosBusinessId();

        try {
            $this->quotationRepository->cancelQuotation($id, $businessId, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Quotation canceled successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => checkResponse($e, 'Failed to cancel quotation.')
            ], 422);
        }
    }

    public function notify($id)
    {
        $businessId = getPosBusinessId();
        $quotation = $this->quotationRepository->findById($id, $businessId);

        if ($quotation && $quotation->status === 'pending') {
            $quotation->update(['status' => 'inprogress']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Customer notified about Quotation #' . $quotation->quotation_no . ' successfully!'
        ]);
    }

    public function duplicate($id)
    {
        if (!checkPosPermission('create_quotation')) {
            abort(403, 'You do not have permission to create quotations.');
        }

        $businessId = getPosBusinessId();
        $userId = Auth::guard('pos')->id() ?? Auth::id();

        try {
            $quotation = $this->quotationRepository->duplicateQuotation($id, $businessId, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Quotation duplicated successfully as #' . $quotation->quotation_no,
                'quotation_id' => $quotation->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => checkResponse($e, 'Failed to duplicate quotation.')
            ], 422);
        }
    }

    public function searchCustomer(Request $request)
    {
        $businessId = getPosBusinessId();
        $search = $request->get('q', '');
        $customers = $this->quotationRepository->searchCustomers($search, $businessId);

        return response()->json($customers);
    }

    public function searchProduct(Request $request)
    {
        $businessId = getPosBusinessId();
        $search = $request->get('q', '');
        $customerRoleId = $request->get('customer_role_id');
        $productIds = (array)$request->get('product_ids', []);
        $products = $this->quotationRepository->searchProducts(
            $businessId,
            $search,
            $customerRoleId ? (int)$customerRoleId : null,
            $productIds
        );

        return response()->json($products);
    }
}
