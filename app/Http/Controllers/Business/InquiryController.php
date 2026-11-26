<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Business\Inquiry\AcceptInquiryRequest;
use App\Http\Requests\Business\Inquiry\AddInquiryNoteRequest;
use App\Http\Requests\Business\Inquiry\AssignInquiryRequest;
use App\Http\Requests\Business\Inquiry\RejectInquiryRequest;
use App\Http\Requests\Business\Inquiry\UpdateBusinessStatusRequest;
use App\Models\BusinessInquiry;
use App\Models\BusinessInquiryLog;
use App\Models\BusinessUser;
use App\Models\User;
use App\Services\BusinessInquiryService;
use App\Services\CreditService;
use DomainException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class InquiryController extends Controller
{
    public function __construct(
        protected BusinessInquiryService $inquiryService,
        protected CreditService $creditService
    ) {}

    /**
     * Display a listing of inquiries or return DataTables JSON.
     */
    public function index(Request $request)
    {
        $businessId = getBusinessId();

        if ($request->ajax()) {
            $query = BusinessInquiry::query()
                ->where('business_id', $businessId)
                ->select([
                    'id',
                    'business_id',
                    'user_id',
                    'assigned_to',
                    'name',
                    'contact',
                    'email',
                    'message',
                    'inquiry_type',
                    'system_status',
                    'business_status',
                    'credit_deducted',
                    'accepted_at',
                    'contacted_at',
                    'next_follow_up_at',
                    'converted_at',
                    'closed_at',
                    'created_at',
                ])
                ->with([
                    'assignedTo:id,first_name,last_name,email',
                    'sources' => function ($q) {
                        $q->select(['id', 'inquiry_id', 'source_id', 'source_type'])
                            ->with([
                                'product:id,category_id,name,sell_price,price',
                                'product.category.businessCategory',
                                'service:id,category_id,name,price',
                                'service.category.businessCategory',
                            ]);
                    },
                ]);

            if ($request->filled('system_status')) {
                $query->where('system_status', $request->system_status);
            }

            if ($request->filled('business_status')) {
                $query->where('business_status', $request->business_status);
            }

            if ($request->filled('inquiry_type')) {
                $query->where('inquiry_type', $request->inquiry_type);
            }

            if ($request->filled('assigned_to')) {
                if ($request->assigned_to === 'unassigned') {
                    $query->whereNull('assigned_to');
                } else {
                    $query->where('assigned_to', $request->assigned_to);
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('id_date', function ($row) {
                    $date = $row->created_at ? $row->created_at->format('M d, Y h:i A') : 'N/A';
                    return '<div class="fw-bold text-dark">#' . $row->id . '</div><div class="small text-muted">' . $date . '</div>';
                })
                ->addColumn('type_and_source', function ($row) {
                    $typeLabels = [
                        'contact_us' => '<span class="badge bg-secondary-subtle text-secondary border px-2 py-1 rounded-pill"><i class="bi bi-chat-left-text me-1"></i>Contact Us</span>',
                        'product'    => '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1 rounded-pill"><i class="bi bi-box-seam me-1"></i>Product</span>',
                        'service'    => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill"><i class="bi bi-tools me-1"></i>Service</span>',
                    ];

                    $html = $typeLabels[$row->inquiry_type] ?? '<span class="badge bg-light text-dark border">Unknown</span>';

                    if ($row->sources->isNotEmpty()) {
                        $html .= '<div class="mt-1 small text-dark">';
                        foreach ($row->sources as $source) {
                            $item = $source->item;
                            if ($item) {
                                $name = e($item->name ?? 'Item #' . $source->source_id);
                                $price = isset($item->sell_price) ? ($item->sell_price ?: $item->price) : ($item->price ?? null);
                                $priceText = $price ? ' (Rs. ' . number_format($price, 2) . ')' : '';
                                $html .= '<div class="text-truncate" style="max-width: 220px;" title="' . $name . '"><i class="bi bi-arrow-return-right text-muted me-1"></i><span class="fw-medium">' . $name . '</span><span class="text-muted">' . $priceText . '</span></div>';
                            }
                        }
                        $html .= '</div>';
                    }

                    return $html;
                })
                ->addColumn('customer_info', function ($row) {
                    // Privacy rule: If vendor has not accepted inquiry, do NOT show user info!
                    if (!$row->isAccepted()) {
                        return '<div class="customer-info-locked p-2 rounded-3 bg-light border border-dashed text-start">
                            <div class="fw-semibold text-secondary small mb-1">
                                <i class="bi bi-lock-fill text-warning me-1"></i>' . e($row->masked_name) . '
                            </div>
                            <div class="small text-muted mb-1">
                                <i class="bi bi-telephone-fill me-1 text-muted"></i>' . e($row->masked_contact) . '
                            </div>
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0 small">
                                <i class="bi bi-shield-lock me-1"></i>Accept to unlock
                            </span>
                        </div>';
                    }

                    $cleanPhone = preg_replace('/[^0-9]/', '', (string)$row->contact);
                    $html = '<div class="customer-info-unlocked text-start">
                        <div class="fw-bold text-dark"><i class="bi bi-person-check-fill text-success me-1"></i>' . e($row->name) . '</div>
                        <div class="small text-dark mt-1">
                            <a href="tel:' . e($row->contact) . '" class="text-decoration-none text-dark fw-semibold me-1"><i class="bi bi-telephone text-primary me-1"></i>' . e($row->contact) . '</a>
                            <a href="https://wa.me/91' . $cleanPhone . '" target="_blank" class="text-success text-decoration-none" title="Chat on WhatsApp"><i class="bi bi-whatsapp"></i></a>
                        </div>';

                    if ($row->email) {
                        $html .= '<div class="small text-muted mt-0 text-truncate" style="max-width: 220px;"><a href="mailto:' . e($row->email) . '" class="text-muted text-decoration-none"><i class="bi bi-envelope me-1"></i>' . e($row->email) . '</a></div>';
                    }

                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('system_status_badge', function ($row) {
                    $badges = [
                        'pending'       => '<span class="badge bg-warning text-dark px-3 py-1 rounded-pill"><i class="bi bi-hourglass-split me-1"></i>Pending</span>',
                        'accepted'      => '<span class="badge bg-success px-3 py-1 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i>Accepted</span>',
                        'rejected'      => '<span class="badge bg-danger px-3 py-1 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>',
                        'not_fulfilled' => '<span class="badge bg-secondary px-3 py-1 rounded-pill"><i class="bi bi-slash-circle me-1"></i>Not Fulfilled</span>',
                    ];

                    return $badges[$row->system_status] ?? '<span class="badge bg-light text-dark border">' . e($row->system_status) . '</span>';
                })
                ->addColumn('business_status_badge', function ($row) {
                    if (!$row->isAccepted()) {
                        return '<span class="text-muted small fst-italic">New Inquiry</span>';
                    }

                    $badges = [
                        'new'       => '<span class="badge bg-info text-dark px-2.5 py-1 rounded-pill"><i class="bi bi-stars me-1"></i>New</span>',
                        'contacted' => '<span class="badge bg-primary px-2.5 py-1 rounded-pill"><i class="bi bi-telephone-outbound me-1"></i>Contacted</span>',
                        'follow_up' => '<span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill"><i class="bi bi-calendar-event me-1"></i>Follow Up</span>',
                        'scheduled' => '<span class="badge bg-purple text-white px-2.5 py-1 rounded-pill" style="background-color: #6f42c1;"><i class="bi bi-clock-history me-1"></i>Scheduled</span>',
                        'converted' => '<span class="badge bg-success px-2.5 py-1 rounded-pill"><i class="bi bi-trophy-fill me-1"></i>Converted</span>',
                        'closed'    => '<span class="badge bg-dark px-2.5 py-1 rounded-pill"><i class="bi bi-archive-fill me-1"></i>Closed</span>',
                    ];

                    $html = $badges[$row->business_status] ?? '<span class="badge bg-light text-dark border">' . e($row->business_status) . '</span>';

                    if ($row->next_follow_up_at && $row->business_status === 'follow_up') {
                        $html .= '<div class="small text-muted mt-1"><i class="bi bi-alarm me-1 text-warning"></i>' . $row->next_follow_up_at->format('M d, h:i A') . '</div>';
                    }

                    return $html;
                })
                ->addColumn('assigned_user', function ($row) {
                    if ($row->assignedTo) {
                        return '<span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 fw-medium"><i class="bi bi-person-fill text-primary me-1"></i>' . e($row->assignedTo->name) . '</span>';
                    }
                    return '<span class="badge bg-light text-muted border rounded-pill px-2 py-1 small">Unassigned</span>';
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="d-flex align-items-center justify-content-end gap-1 flex-nowrap">';

                    // View details button (always available)
                    $html .= '<button type="button" class="btn btn-sm btn-outline-secondary rounded-circle btn-view-inquiry" data-id="' . $row->id . '" title="View Details"><i class="bi bi-eye"></i></button>';

                    if ($row->isPending()) {
                        $charge = $this->creditService->getInquiryCharge($row->business_id, $row->inquiry_type, $row);
                        // Accept button with unlock modal
                        $html .= '<button type="button" class="btn btn-sm btn-success rounded-pill px-2.5 btn-accept-inquiry" data-id="' . $row->id . '" data-type="' . $row->inquiry_type . '" data-charge="' . $charge . '" title="Accept Inquiry (Unlock details)"><i class="bi bi-unlock-fill me-1"></i>Accept</button>';

                        // Reject / Not fulfilled dropdown or buttons
                        $html .= '<button type="button" class="btn btn-sm btn-outline-danger rounded-circle btn-reject-inquiry" data-id="' . $row->id . '" title="Reject Inquiry"><i class="bi bi-x-lg"></i></button>';
                        $html .= '<button type="button" class="btn btn-sm btn-outline-dark rounded-circle btn-not-fulfilled-inquiry" data-id="' . $row->id . '" title="Mark Not Fulfilled"><i class="bi bi-slash-circle"></i></button>';
                    } elseif ($row->isAccepted()) {
                        // Business sales status update button
                        $html .= '<button type="button" class="btn btn-sm btn-outline-primary rounded-circle btn-status-inquiry" data-id="' . $row->id . '" data-status="' . $row->business_status . '" title="Update Sales Status"><i class="bi bi-arrow-repeat"></i></button>';

                        // Assign employee button
                        $html .= '<button type="button" class="btn btn-sm btn-outline-info rounded-circle btn-assign-inquiry" data-id="' . $row->id . '" data-assigned="' . ($row->assigned_to ?? '') . '" title="Assign Staff"><i class="bi bi-person-plus"></i></button>';

                        // Add note / timeline button
                        $html .= '<button type="button" class="btn btn-sm btn-outline-secondary rounded-circle btn-logs-inquiry" data-id="' . $row->id . '" title="View Timeline & Notes"><i class="bi bi-chat-square-dots"></i></button>';
                    } else {
                        // Rejected or Not Fulfilled inquiries
                        $html .= '<button type="button" class="btn btn-sm btn-outline-secondary rounded-circle btn-logs-inquiry" data-id="' . $row->id . '" title="View Logs"><i class="bi bi-journal-text"></i></button>';
                    }

                    $html .= '</div>';
                    return $html;
                })
                ->rawColumns(['id_date', 'type_and_source', 'customer_info', 'system_status_badge', 'business_status_badge', 'assigned_user', 'action'])
                ->make(true);
        }

        // Stats calculation for header cards
        $statsBase = BusinessInquiry::where('business_id', $businessId);

        $stats = [
            'total'         => (clone $statsBase)->count(),
            'pending'       => (clone $statsBase)->where('system_status', 'pending')->count(),
            'accepted'      => (clone $statsBase)->where('system_status', 'accepted')->count(),
            'converted'     => (clone $statsBase)->where('business_status', 'converted')->count(),
            'closed_rejected' => (clone $statsBase)->whereIn('system_status', ['rejected', 'not_fulfilled'])->count(),
        ];

        // Available credit and required charge per type
        $availableCredit = $this->creditService->getAvailableCredit($businessId);
        $inquiryCharges = [
            'contact_us' => $this->creditService->getInquiryCharge($businessId, 'contact_us'),
            'product'    => $this->creditService->getInquiryCharge($businessId, 'product'),
            'service'    => $this->creditService->getInquiryCharge($businessId, 'service'),
        ];

        // Retrieve employees for assignment dropdown
        $business = \App\Models\Business::select(['id', 'owner_id'])->find($businessId);
        $staffUserIds = BusinessUser::where('business_id', $businessId)->pluck('user_id')->toArray();
        if ($business && $business->owner_id) {
            $staffUserIds[] = $business->owner_id;
        }
        $staffUserIds = array_unique(array_filter($staffUserIds));

        $staffUsers = User::select(['id', 'first_name', 'last_name', 'email'])
            ->whereIn('id', $staffUserIds)
            ->get();

        return view('business.inquiries.index', compact('stats', 'availableCredit', 'inquiryCharges', 'staffUsers'));
    }

    /**
     * Show inquiry details (JSON for modal / AJAX).
     */
    public function show($id): JsonResponse
    {
        $businessId = getBusinessId();

        $inquiry = BusinessInquiry::where('business_id', $businessId)
            ->with([
                'assignedTo:id,first_name,last_name,email',
                'sources' => function ($q) {
                    $q->select(['id', 'inquiry_id', 'source_id', 'source_type'])
                        ->with([
                            'product:id,category_id,name,sell_price,price,description',
                            'product.category.businessCategory',
                            'service:id,category_id,name,price,description',
                            'service.category.businessCategory',
                        ]);
                },
                'creditDeducted:id,amount,transaction_type,type,balance_after,created_at',
                'logs' => function ($q) {
                    $q->with('user:id,first_name,last_name,email')->latest();
                },
            ])
            ->findOrFail($id);

        $isAccepted = $inquiry->isAccepted();
        $inquiryCharge = $this->creditService->getInquiryCharge($businessId, $inquiry->inquiry_type, $inquiry);

        $data = [
            'id'                => $inquiry->id,
            'business_id'       => $inquiry->business_id,
            'inquiry_type'      => $inquiry->inquiry_type,
            'inquiry_charge'    => $inquiryCharge,
            'system_status'     => $inquiry->system_status,
            'business_status'   => $inquiry->business_status,
            'is_accepted'       => $isAccepted,
            'credit_deducted'   => (bool)$inquiry->credit_deducted,
            'credit_history'    => $inquiry->creditDeducted,
            // Masking applied if not accepted:
            'name'              => $isAccepted ? $inquiry->name : $inquiry->masked_name,
            'contact'           => $isAccepted ? $inquiry->contact : $inquiry->masked_contact,
            'email'             => $isAccepted ? $inquiry->email : $inquiry->masked_email,
            'message'           => $inquiry->message,
            'assigned_to'       => $inquiry->assigned_to,
            'assigned_user'     => $inquiry->assignedTo ? $inquiry->assignedTo->name : null,
            'accepted_at'       => $inquiry->accepted_at ? $inquiry->accepted_at->format('M d, Y h:i A') : null,
            'contacted_at'      => $inquiry->contacted_at ? $inquiry->contacted_at->format('M d, Y h:i A') : null,
            'next_follow_up_at' => $inquiry->next_follow_up_at ? $inquiry->next_follow_up_at->format('Y-m-d\TH:i') : null,
            'next_follow_up_formatted' => $inquiry->next_follow_up_at ? $inquiry->next_follow_up_at->format('M d, Y h:i A') : null,
            'converted_at'      => $inquiry->converted_at ? $inquiry->converted_at->format('M d, Y h:i A') : null,
            'closed_at'         => $inquiry->closed_at ? $inquiry->closed_at->format('M d, Y h:i A') : null,
            'created_at'        => $inquiry->created_at ? $inquiry->created_at->format('M d, Y h:i A') : null,
            'sources'           => $inquiry->sources->map(function ($s) {
                return [
                    'id'          => $s->id,
                    'source_type' => $s->source_type,
                    'source_id'   => $s->source_id,
                    'name'        => $s->item ? $s->item->name : 'Item #' . $s->source_id,
                    'price'       => $s->item ? ($s->item->sell_price ?? $s->item->price ?? null) : null,
                ];
            }),
            'logs'              => $inquiry->logs->map(function ($log) {
                return [
                    'id'         => $log->id,
                    'action'     => $log->action,
                    'old_value'  => $log->old_value,
                    'new_value'  => $log->new_value,
                    'remarks'    => $log->remarks,
                    'user_name'  => $log->user ? $log->user->name : 'System',
                    'created_at' => $log->created_at ? $log->created_at->format('M d, Y h:i A') : null,
                ];
            }),
        ];

        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    /**
     * Accept a pending inquiry and deduct business credits.
     */
    public function accept(AcceptInquiryRequest $request, $id): JsonResponse
    {
        $businessId = getBusinessId();
        $inquiry = BusinessInquiry::where('business_id', $businessId)->findOrFail($id);

        try {
            $updated = $this->inquiryService->acceptInquiry(
                $inquiry,
                $request->remarks,
                Auth::id()
            );

            $availableCredit = $this->creditService->getAvailableCredit($businessId);

            return response()->json([
                'status'           => 'success',
                'message'          => 'Inquiry accepted successfully! Customer contact information is now unlocked.',
                'available_credit' => $availableCredit,
                'data'             => [
                    'id'              => $updated->id,
                    'name'            => $updated->name,
                    'contact'         => $updated->contact,
                    'email'           => $updated->email,
                    'system_status'   => $updated->system_status,
                    'business_status' => $updated->business_status,
                ],
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage() ?: 'An unexpected error occurred while accepting the inquiry.',
            ], 500);
        }
    }

    /**
     * Reject a pending inquiry (no credit deduction).
     */
    public function reject(RejectInquiryRequest $request, $id): JsonResponse
    {
        $businessId = getBusinessId();
        $inquiry = BusinessInquiry::where('business_id', $businessId)->findOrFail($id);

        try {
            $this->inquiryService->rejectInquiry(
                $inquiry,
                $request->remarks,
                Auth::id()
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Inquiry rejected successfully.',
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to reject inquiry. Please try again.',
            ], 500);
        }
    }

    /**
     * Mark a pending inquiry as not fulfilled (no credit deduction).
     */
    public function notFulfilled(RejectInquiryRequest $request, $id): JsonResponse
    {
        $businessId = getBusinessId();
        $inquiry = BusinessInquiry::where('business_id', $businessId)->findOrFail($id);

        try {
            $this->inquiryService->markNotFulfilled(
                $inquiry,
                $request->remarks,
                Auth::id()
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Inquiry marked as not fulfilled.',
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update inquiry. Please try again.',
            ], 500);
        }
    }

    /**
     * Update business sales workflow status (only allowed for accepted inquiries).
     */
    public function updateBusinessStatus(UpdateBusinessStatusRequest $request, $id): JsonResponse
    {
        $businessId = getBusinessId();
        $inquiry = BusinessInquiry::where('business_id', $businessId)->findOrFail($id);

        try {
            $updated = $this->inquiryService->updateBusinessStatus(
                $inquiry,
                $request->business_status,
                [
                    'remarks'           => $request->remarks,
                    'next_follow_up_at' => $request->next_follow_up_at,
                ],
                Auth::id()
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Sales status updated successfully.',
                'data'    => [
                    'business_status'   => $updated->business_status,
                    'next_follow_up_at' => $updated->next_follow_up_at ? $updated->next_follow_up_at->format('M d, Y h:i A') : null,
                ],
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update sales status.',
            ], 500);
        }
    }

    /**
     * Assign staff to inquiry.
     */
    public function assign(AssignInquiryRequest $request, $id): JsonResponse
    {
        $businessId = getBusinessId();
        $inquiry = BusinessInquiry::where('business_id', $businessId)->findOrFail($id);

        try {
            $updated = $this->inquiryService->assignEmployee(
                $inquiry,
                $request->assigned_to,
                $request->remarks,
                Auth::id()
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Staff assigned successfully.',
                'data'    => [
                    'assigned_to'   => $updated->assigned_to,
                    'assigned_user' => $updated->assignedTo ? $updated->assignedTo->name : 'Unassigned',
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to assign staff.',
            ], 500);
        }
    }

    /**
     * Add a note / remark to inquiry history.
     */
    public function addNote(AddInquiryNoteRequest $request, $id): JsonResponse
    {
        $businessId = getBusinessId();
        $inquiry = BusinessInquiry::where('business_id', $businessId)->findOrFail($id);

        try {
            $log = $this->inquiryService->addNote(
                $inquiry,
                $request->remarks,
                Auth::id()
            );

            return response()->json([
                'status'  => 'success',
                'message' => 'Note added successfully.',
                'data'    => [
                    'id'         => $log->id,
                    'remarks'    => $log->remarks,
                    'user_name'  => Auth::user()->name,
                    'created_at' => $log->created_at->format('M d, Y h:i A'),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to add note.',
            ], 500);
        }
    }

    /**
     * Fetch timeline logs for an inquiry.
     */
    public function getLogs($id): JsonResponse
    {
        $businessId = getBusinessId();
        $inquiry = BusinessInquiry::where('business_id', $businessId)->findOrFail($id);

        $logs = BusinessInquiryLog::where('inquiry_id', $inquiry->id)
            ->with('user:id,first_name,last_name,email')
            ->latest()
            ->get()
            ->map(function ($log) {
                return [
                    'id'         => $log->id,
                    'action'     => $log->action,
                    'old_value'  => $log->old_value,
                    'new_value'  => $log->new_value,
                    'remarks'    => $log->remarks,
                    'user_name'  => $log->user ? $log->user->name : 'System',
                    'created_at' => $log->created_at ? $log->created_at->format('M d, Y h:i A') : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => $logs,
        ]);
    }
}

