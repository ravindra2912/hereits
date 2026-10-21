<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\BusinessHomePopup;
use App\Models\BusinessCampaignLead;
use App\Models\User;
use App\Http\Requests\Business\StoreBusinessCampaignLeadRequest;
use App\Http\Requests\Business\UpdateBusinessCampaignLeadRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Traits\GoogleSheet;

class CampaignLeadController extends Controller
{
    use GoogleSheet;
    public function index(Request $request)
    {
        $businessId = getBusinessId();

        if ($request->ajax()) {
            $query = BusinessCampaignLead::query()
                ->where('business_id', $businessId)
                ->select(['id', 'popup_id', 'assigned_by', 'assigned_to', 'contact', 'email', 'details', 'ip_address', 'status', 'is_webhook_sent', 'webhook_response', 'scheduled_at', 'created_at'])
                ->with([
                    'popup:id,title,popup_type,is_web_hook_for_google_sheet,google_sheet_webhook_url',
                    'assignedTo:id,first_name,last_name,email',
                    'assignedBy:id,first_name,last_name,email',
                ]);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('popup_id')) {
                $query->where('popup_id', $request->popup_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('id_date', function ($row) {
                    $date = $row->created_at ? $row->created_at->format('M d, Y H:i') : 'N/A';
                    return '<span class="fw-bold text-dark">#' . $row->id . '</span><div class="small text-muted">' . $date . '</div>';
                })
                ->addColumn('campaign', function ($row) {
                    if ($row->popup) {
                        return '<span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-bold">' . e($row->popup->title) . '</span>';
                    }
                    return '<span class="text-muted small">Direct / Manual</span>';
                })
                ->addColumn('contact_email', function ($row) {
                    $html = '';
                    if ($row->contact) {
                        $cleanPhone = preg_replace('/[^0-9]/', '', $row->contact);
                        $html .= '<div class="fw-semibold text-dark"><i class="bi bi-telephone text-primary me-1"></i><a href="tel:' . e($row->contact) . '" class="text-dark text-decoration-none">' . e($row->contact) . '</a> <a href="https://wa.me/91' . $cleanPhone . '" target="_blank" class="text-success ms-1" title="Open WhatsApp"><i class="bi bi-whatsapp"></i></a></div>';
                    }
                    if ($row->email) {
                        $html .= '<div class="small text-muted"><i class="bi bi-envelope me-1"></i><a href="mailto:' . e($row->email) . '" class="text-muted text-decoration-none">' . e($row->email) . '</a></div>';
                    }
                    if (!$row->contact && !$row->email) {
                        $html = '<span class="text-muted italic small">No direct contact</span>';
                    }
                    return $html;
                })
                ->addColumn('details_col', function ($row) {
                    if (is_array($row->details) && count($row->details) > 0) {
                        return '<button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-primary btn-view-details" data-id="' . $row->id . '"><i class="bi bi-journal-text me-1"></i> View ' . count($row->details) . ' fields</button>';
                    }
                    return '<span class="text-muted small">None</span>';
                })
                ->addColumn('status_badge', function ($row) {
                    $statusClass = match ($row->status) {
                        'new' => 'bg-new',
                        'contacted' => 'bg-contacted',
                        'scheduled' => 'bg-scheduled',
                        'in_progress' => 'bg-in_progress',
                        'converted' => 'bg-converted',
                        'closed_lost' => 'bg-closed_lost',
                        'duplicate' => 'bg-duplicate',
                        'spam' => 'bg-spam',
                        default => 'bg-secondary text-white',
                    };
                    $label = ucfirst(str_replace('_', ' ', $row->status));
                    return '<span class="status-badge ' . $statusClass . '">' . $label . '</span>';
                })
                ->addColumn('assigned_user', function ($row) {
                    if ($row->assignedTo) {
                        return '<span class="small fw-semibold text-dark"><i class="bi bi-person me-1"></i>' . e($row->assignedTo->first_name . ' ' . $row->assignedTo->last_name) . '</span>';
                    }
                    return '<span class="text-muted small">Unassigned</span>';
                })
                ->addColumn('webhook_status', function ($row) {
                    if ($row->popup && $row->popup->is_web_hook_for_google_sheet && !empty($row->popup->google_sheet_webhook_url)) {
                        if ($row->is_webhook_sent) {
                            return '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold" title="' . e($row->webhook_response ?? 'Success') . '"><i class="bi bi-check-circle-fill me-1"></i>Sent</span>';
                        }
                        $title = !empty($row->webhook_response) ? 'Error: ' . e($row->webhook_response) : 'Pending Upload';
                        $recallBtn = '';
                        if (checkBusinessPermission('edit_campaign_leads')) {
                            $recallBtn = '<button type="button" class="btn btn-xs btn-outline-primary py-0 px-2 btn-recall-webhook fs-7" data-id="' . $row->id . '" title="Re-send Lead to Google Sheet"><i class="bi bi-arrow-repeat"></i> Re-call</button>';
                        }
                        return '
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-1 fw-bold" style="cursor: help;" title="' . $title . '"><i class="bi bi-exclamation-triangle-fill me-1"></i>Pending</span>
                            ' . $recallBtn . '
                        </div>';
                    }
                    return '<span class="text-muted small">N/A</span>';
                })
                ->addColumn('action', function ($row) {
                    $canEdit = checkBusinessPermission('edit_campaign_leads');
                    $canDelete = checkBusinessPermission('delete_campaign_leads');

                    $html = '<div class="btn-group">';
                    $html .= '<button type="button" class="btn btn-sm btn-outline-info rounded-circle me-1 btn-view-lead" data-id="' . $row->id . '" title="View Lead Profile"><i class="bi bi-eye"></i></button>';

                    if ($canEdit) {
                        $html .= '<button type="button" class="btn btn-sm btn-outline-primary rounded-circle me-1 btn-edit-lead" data-id="' . $row->id . '" title="Edit Lead"><i class="bi bi-pencil"></i></button>';
                    }
                    if ($canDelete) {
                        $html .= '<button type="button" class="btn btn-sm btn-outline-danger rounded-circle btn-delete-lead" data-id="' . $row->id . '" title="Delete Lead"><i class="bi bi-trash"></i></button>';
                    }
                    $html .= '</div>';

                    return $html;
                })
                ->filterColumn('campaign', function ($query, $keyword) {
                    $query->whereHas('popup', function ($q) use ($keyword) {
                        $q->where('title', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('contact_email', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('contact', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('assigned_user', function ($query, $keyword) {
                    $query->whereHas('assignedTo', function ($q) use ($keyword) {
                        $q->where('first_name', 'like', "%{$keyword}%")
                            ->orWhere('last_name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['id_date', 'campaign', 'contact_email', 'details_col', 'status_badge', 'assigned_user', 'webhook_status', 'action'])
                ->make(true);
        }

        $setting = getBusinessSettings($businessId);

        $popups = BusinessHomePopup::where('business_id', $businessId)->get();
        $users = User::select(['id', 'first_name', 'last_name', 'email'])
            ->where('business_id', $businessId)
            ->get();

        $statsQuery = BusinessCampaignLead::where('business_id', $businessId);

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'new' => (clone $statsQuery)->where('status', 'new')->count(),
            'contacted' => (clone $statsQuery)->where('status', 'contacted')->count(),
            'converted' => (clone $statsQuery)->where('status', 'converted')->count(),
        ];

        return view('business.home-management.leads', compact('popups', 'users', 'stats', 'setting'));
    }

    public function store(StoreBusinessCampaignLeadRequest $request)
    {
        if (!checkBusinessPermission('add_campaign_leads')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. You do not have permission to add campaign leads.'], 403);
        }

        $businessId = getBusinessId();
        $popupIds = BusinessHomePopup::where('business_id', $businessId)->pluck('id');

        $data = $request->validated();
        if (!empty($data['popup_id']) && !$popupIds->contains($data['popup_id'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid popup campaign selected.'], 422);
        }

        $data['assigned_by'] = auth()->id();
        $data['ip_address'] = $request->ip();
        $data['business_id'] = $businessId;

        BusinessCampaignLead::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Campaign lead created successfully.'
        ]);
    }

    public function edit($id)
    {
        if (!checkBusinessPermission('edit_campaign_leads')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. You do not have permission to edit campaign leads.'], 403);
        }

        $businessId = getBusinessId();

        $lead = BusinessCampaignLead::where('business_id', $businessId)
            ->with([
                'popup:id,title',
                'assignedTo:id,first_name,last_name,email',
                'assignedBy:id,first_name,last_name,email',
            ])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $lead
        ]);
    }

    public function update(UpdateBusinessCampaignLeadRequest $request, $id)
    {
        if (!checkBusinessPermission('edit_campaign_leads')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. You do not have permission to edit campaign leads.'], 403);
        }

        $businessId = getBusinessId();

        $lead = BusinessCampaignLead::where('business_id', $businessId)->findOrFail($id);

        $data = $request->validated();

        if (!empty($data['popup_id'])) {
            $popupIds = BusinessHomePopup::where('business_id', $businessId)->pluck('id');
            if (!$popupIds->contains($data['popup_id'])) {
                return response()->json(['status' => 'error', 'message' => 'Invalid popup campaign selected.'], 422);
            }
        }

        if (!empty($data['assigned_to']) && $lead->assigned_to != $data['assigned_to']) {
            $data['assigned_by'] = auth()->id();
        }

        $lead->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Campaign lead updated successfully.'
        ]);
    }

    public function destroy($id)
    {
        if (!checkBusinessPermission('delete_campaign_leads')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. You do not have permission to delete campaign leads.'], 403);
        }

        $businessId = getBusinessId();

        $lead = BusinessCampaignLead::where('business_id', $businessId)->findOrFail($id);

        $lead->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Campaign lead deleted successfully.'
        ]);
    }

    public function recallWebhook($id)
    {
        if (!checkBusinessPermission('edit_campaign_leads')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized. You do not have permission to recall webhooks.'], 403);
        }

        $businessId = getBusinessId();

        $lead = BusinessCampaignLead::with('popup.business')
            ->where('business_id', $businessId)
            ->findOrFail($id);

        $popup = $lead->popup;
        if (!$popup || !$popup->is_web_hook_for_google_sheet || empty($popup->google_sheet_webhook_url)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This lead is not associated with an active Google Sheet webhook campaign.'
            ], 422);
        }

        try {
            $webhookFields = $lead->details ?? [];
            $webhookFields[] = [
                'label' => 'Campaign Name',
                'value' => $popup->title,
            ];
            if ($popup->business) {
                $webhookFields[] = [
                    'label' => 'Business URL',
                    'value' => route('business-details', ['business_slug' => $popup->business->slug]),
                ];
            }
            $webhookFields[] = [
                'label' => 'date',
                'value' => $lead->created_at ? $lead->created_at->format('d-m-Y H:i:s') : now()->format('d-m-Y H:i:s'),
            ];

            $res = $this->storeToGoogleSheet($webhookFields, $popup->google_sheet_webhook_url);
            if (isset($res['status']) && $res['status'] >= 200 && $res['status'] < 300) {
                $lead->update([
                    'is_webhook_sent' => true,
                    'webhook_response' => is_array($res['body']) ? json_encode($res['body']) : ($res['body'] ?? 'Success')
                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Webhook called successfully and record uploaded.'
                ]);
            }

            $lead->update([
                'is_webhook_sent' => false,
                'webhook_response' => is_array($res['body']) ? json_encode($res['body']) : ($res['body'] ?? 'Failed with status ' . ($res['status'] ?? 'unknown'))
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Webhook failed with status code ' . ($res['status'] ?? 'unknown') . '.'
            ], 500);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Recall Google Sheet Lead Webhook Error: ' . $e->getMessage());
            $lead->update([
                'is_webhook_sent' => false,
                'webhook_response' => checkResponse($e)
            ]);
            return response()->json([
                'status' => 'error',
                'message' => checkResponse($e, 'An error occurred while uploading.')
            ], 500);
        }
    }
}
