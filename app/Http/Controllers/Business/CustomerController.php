<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BusinessCustomer;
use App\Models\BusinessCustomerRole;
use App\Http\Requests\Business\UpdateBusinessCustomerRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomersExport;

class CustomerController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request)
    {
        $businessSetting = getBusinessSettings();
        $businessId = getBusinessId();

        $roles = BusinessCustomerRole::where('business_id', $businessId)
            ->where('status', 'active')
            ->select('id', 'role_name')
            ->get();

        if ($request->ajax()) {
            $data = BusinessCustomer::select(
                'business_customers.id',
                'business_customers.customer_id',
                'business_customers.customer_role_id',
                'business_customers.business_name',
                'business_customers.customer_name',
                'business_customers.contact as customer_contact',
                'business_customers.address',
                'business_customers.city',
                'business_customers.state',
                'business_customers.pincode',
                'business_customers.created_at',
                'business_customer_roles.role_name as customer_role_name',
                'users.first_name',
                'users.last_name',
                'users.email',
                'users.profile',
                'users.contact as user_contact'
            )
                ->where('business_customers.business_id', $businessId)
                ->leftJoin('users', 'users.id', '=', 'business_customers.customer_id')
                ->leftJoin('business_customer_roles', function ($join) use ($businessId) {
                    $join->on('business_customer_roles.id', '=', 'business_customers.customer_role_id')
                        ->where('business_customer_roles.business_id', '=', $businessId);
                });

            if ($request->filled('group_id')) {
                if ($request->group_id === 'none') {
                    $data->whereNull('business_customers.customer_role_id');
                } else {
                    $data->where('business_customers.customer_role_id', $request->group_id);
                }
            }

            return Datatables::of($data)
                ->addIndexColumn()
                ->filterColumn('customer_name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('business_customers.customer_name', 'like', "%{$keyword}%")
                          ->orWhere('users.first_name', 'like', "%{$keyword}%")
                          ->orWhere('users.last_name', 'like', "%{$keyword}%")
                          ->orWhereRaw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) LIKE ?", ["%{$keyword}%"]);
                    });
                })
                ->filterColumn('business_name', function ($query, $keyword) {
                    $query->where('business_customers.business_name', 'like', "%{$keyword}%");
                })
                ->filterColumn('contact', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('business_customers.contact', 'like', "%{$keyword}%")
                          ->orWhere('users.contact', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('email', function ($query, $keyword) {
                    $query->where('users.email', 'like', "%{$keyword}%");
                })
                ->addColumn('img', function ($row) {
                    if ($row->profile) {
                        return '<div class="text-center"><img src="' . getImage($row->profile) . '" class="rounded-circle shadow-sm" style="width: 40px; height: 40px; object-fit: cover;" /></div>';
                    }
                    $name = $row->customer_name ?: trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? ''));
                    $initial = strtoupper(substr($name ?: 'C', 0, 1));
                    return '<div class="text-center"><span class="avatar-circle rounded-circle d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-bold shadow-sm" style="width: 40px; height: 40px; font-size: 1rem;">' . e($initial) . '</span></div>';
                })
                ->addColumn('customer_name', function ($row) {
                    $name = $row->customer_name ?: trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')) ?: 'N/A';
                    return '<span class="fw-bold text-dark">' . e($name) . '</span>';
                })
                ->addColumn('business_name', function ($row) {
                    return $row->business_name ? '<span class="badge bg-light text-dark border px-2 py-1">' . e($row->business_name) . '</span>' : '<span class="text-muted">-</span>';
                })
                ->addColumn('contact', function ($row) {
                    $contact = $row->customer_contact ?: ($row->user_contact ?? 'N/A');
                    return '<span>' . e($contact) . '</span>';
                })
                ->addColumn('email', function ($row) {
                    return $row->email ? '<span>' . e($row->email) . '</span>' : '<span class="text-muted">N/A</span>';
                })
                ->addColumn('group_name', function ($row) use ($roles) {
                    $options = '<option value="">No Group</option>';
                    foreach ($roles as $role) {
                        $selected = ($row->customer_role_id == $role->id) ? 'selected' : '';
                        $options .= '<option value="' . $role->id . '" ' . $selected . '>' . e($role->role_name) . '</option>';
                    }
                    return '<div class="d-flex align-items-center"><select class="form-select form-select-sm customer-group-select border-primary-subtle shadow-sm" style="min-width: 140px; font-size: 0.82rem;" onchange="assignCustomerGroup(this, ' . $row->id . ')">'
                        . $options . '</select></div>';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="text-center text-nowrap">
                        <button onclick="getUserDetails(' . $row->id . ')" class="btn btn-outline-primary btn-sm rounded-circle shadow-sm me-1" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button onclick="editCustomer(' . $row->id . ')" class="btn btn-outline-warning btn-sm rounded-circle shadow-sm" title="Edit Customer">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>';
                })
                ->rawColumns(['img', 'customer_name', 'business_name', 'contact', 'email', 'group_name', 'action'])
                ->make(true);
        }

        return view('business.appointment.customer.index', compact('businessSetting', 'roles'));
    }

    /**
     * Assign role/group to a customer.
     */
    public function assignRole(Request $request, $id)
    {
        $businessId = getBusinessId();
        $roleId = $request->input('customer_role_id');

        if ($roleId) {
            $roleExists = BusinessCustomerRole::where('business_id', $businessId)
                ->where('id', $roleId)
                ->exists();

            if (!$roleExists) {
                return response()->json(['success' => false, 'message' => 'Invalid group selected.'], 422);
            }
        }

        BusinessCustomer::where('business_id', $businessId)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)
                  ->orWhere('customer_id', $id);
            })
            ->update(['customer_role_id' => $roleId ?: null]);

        return response()->json([
            'success' => true,
            'message' => 'Customer group updated successfully.',
        ]);
    }

    /**
     * Get customer data for editing.
     */
    public function edit(string $id)
    {
        $businessId = getBusinessId();
        $customer = BusinessCustomer::where('business_id', $businessId)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)
                  ->orWhere('customer_id', $id);
            })
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $customer->id,
                'customer_name' => $customer->customer_name,
                'business_name' => $customer->business_name,
                'contact' => $customer->contact,
                'customer_role_id' => $customer->customer_role_id,
                'address' => $customer->address,
                'city' => $customer->city,
                'state' => $customer->state,
                'pincode' => $customer->pincode,
            ]
        ]);
    }

    /**
     * Update customer fields in business_customers table.
     */
    public function update(UpdateBusinessCustomerRequest $request, string $id)
    {
        $businessId = getBusinessId();
        $customer = BusinessCustomer::where('business_id', $businessId)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)
                  ->orWhere('customer_id', $id);
            })
            ->firstOrFail();

        $validated = $request->validated();

        $customer->update([
            'customer_name' => $validated['customer_name'] ?? null,
            'business_name' => $validated['business_name'] ?? null,
            'contact' => $validated['contact'] ?? null,
            'customer_role_id' => !empty($validated['customer_role_id']) ? $validated['customer_role_id'] : null,
            'address' => $validated['address'] ?? null,
            'state' => $validated['state'] ?? null,
            'city' => $validated['city'] ?? null,
            'pincode' => $validated['pincode'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Customer details updated successfully.',
            'data' => $customer,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $businessId = getBusinessId();

        $businessCustomer = BusinessCustomer::where('business_id', $businessId)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)
                  ->orWhere('customer_id', $id);
            })
            ->first();

        $userId = $businessCustomer?->customer_id ?: (is_numeric($id) ? (int)$id : null);
        $user = null;
        $totalSpend = 0;

        if ($userId) {
            $user = User::select('id', 'first_name', 'last_name', 'email', 'profile', 'contact', 'gender', 'created_at')
                ->withCount([
                    'appointments as completed_appointments' => function ($query) use ($businessId) {
                        $query->where('appointment_bookings.business_id', $businessId)->where('appointment_bookings.status', 'completed');
                    },
                    'appointments as uncompleted_appointments' => function ($query) use ($businessId) {
                        $query->where('appointment_bookings.business_id', $businessId)->whereNotIn('appointment_bookings.status', ['completed', 'auto_cancelled', 'cancel']);
                    },
                    'orders as completed_orders' => function ($query) use ($businessId) {
                        $query->where('orders.business_id', $businessId)->where('orders.payment_status', 'paid');
                    },
                    'orders as pending_orders' => function ($query) use ($businessId) {
                        $query->where('orders.business_id', $businessId)->where('orders.payment_status', '!=', 'paid');
                    },
                    'quotations as total_quotations' => function ($query) use ($businessId) {
                        $query->where('quotations.business_id', $businessId);
                    }
                ])->find($userId);

            $bcId = $businessCustomer?->id;
            $totalSpend = \App\Models\Order::where('business_id', $businessId)
                ->where(function ($q) use ($userId, $bcId) {
                    $q->where('customer_id', $userId);
                    if ($bcId) {
                        $q->orWhere('business_customer_id', $bcId);
                    }
                })
                ->where('payment_status', 'paid')
                ->sum('total');
        }

        $businessSetting = getBusinessSettings();

        $html = view('business.appointment.customer.details_modal', compact(
            'businessCustomer',
            'user',
            'businessSetting',
            'totalSpend'
        ))->render();
        
        return response()->json(['success' => true, 'html' => $html]);
    }

    /**
     * Display customer transaction history and profile.
     */
    public function profile(Request $request, $id)
    {
        $businessId = getBusinessId();

        $businessCustomer = BusinessCustomer::where('business_id', $businessId)
            ->where(function ($q) use ($id) {
                $q->where('id', $id)
                  ->orWhere('customer_id', $id);
            })
            ->first();

        $userId = $businessCustomer?->customer_id ?: (is_numeric($id) ? (int)$id : null);
        $user = User::select('id', 'first_name', 'last_name', 'email', 'profile', 'contact', 'gender', 'created_at')
            ->find($userId);

        if (!$user) {
            $user = User::select('id', 'first_name', 'last_name', 'email', 'profile', 'contact', 'gender', 'created_at')
                ->findOrFail($id);
        }

        $businessSetting = getBusinessSettings();

        if ($request->ajax()) {
            $type = $request->get('type');
            $bcId = $businessCustomer?->id;
            $customerId = $user?->id ?: (is_numeric($id) ? (int)$id : null);

            if ($type === 'appointments' && $businessSetting->is_appointment_system) {
                $data = \App\Models\AppointmentBooking::with(['expert:id,user_id', 'expert.user:id,first_name,last_name', 'department:id,department_name'])
                    ->where('user_id', $customerId)
                    ->where('business_id', $businessId);

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('date', function ($row) {
                        return \Carbon\Carbon::parse($row->booking_date)->format('d M Y');
                    })
                    ->addColumn('expert', function ($row) {
                        return $row->expert?->user?->name ?? 'N/A';
                    })
                    ->filterColumn('expert', function ($query, $keyword) {
                        $query->whereHas('expert.user', function ($uq) use ($keyword) {
                            $uq->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$keyword}%")
                               ->orWhere('first_name', 'like', "%{$keyword}%")
                               ->orWhere('last_name', 'like', "%{$keyword}%");
                        });
                    })
                    ->addColumn('department', function ($row) {
                        return $row->department->department_name ?? 'N/A';
                    })
                    ->addColumn('status_badge', function ($row) {
                        $statusClasses = [
                            'completed' => 'bg-success text-white',
                            'confirmed' => 'bg-primary text-white',
                            'pending' => 'bg-warning text-dark',
                            'cancel' => 'bg-danger text-white'
                        ];
                        $badgeClass = $statusClasses[$row->status] ?? 'bg-secondary text-white';
                        return '<span class="badge ' . $badgeClass . ' rounded-pill px-2.5 py-1 small">' . ucfirst($row->status) . '</span>';
                    })
                    ->rawColumns(['status_badge'])
                    ->make(true);
            }

            if ($type === 'orders' && $businessSetting->is_ecommerce_system) {
                $data = \App\Models\Order::where('business_id', $businessId)
                    ->where(function ($q) use ($customerId, $bcId) {
                        if ($customerId && $bcId) {
                            $q->where('customer_id', $customerId)
                              ->orWhere('business_customer_id', $bcId);
                        } elseif ($customerId) {
                            $q->where('customer_id', $customerId);
                        } elseif ($bcId) {
                            $q->where('business_customer_id', $bcId);
                        }
                    });

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('invoice', function ($row) {
                        return '#' . ($row->invoice_number ?? $row->id);
                    })
                    ->addColumn('date', function ($row) {
                        return $row->created_at->format('d M Y');
                    })
                    ->addColumn('amount', function ($row) {
                        return '₹' . number_format($row->total, 2);
                    })
                    ->addColumn('order_status_badge', function ($row) {
                        $orderStatusColors = [
                            'pending' => 'bg-warning text-dark',
                            'confirmed' => 'bg-primary text-white',
                            'processing' => 'bg-info text-dark',
                            'ready_to_deliver' => 'bg-info text-dark',
                            'delivered' => 'bg-success text-white',
                            'canceled' => 'bg-danger text-white',
                            'canceled_by_user' => 'bg-danger text-white'
                        ];
                        $badgeClass = $orderStatusColors[$row->order_status] ?? 'bg-secondary text-white';
                        return '<span class="badge ' . $badgeClass . ' rounded-pill px-2.5 py-1 small">' . ucfirst(str_replace('_', ' ', $row->order_status)) . '</span>';
                    })
                    ->addColumn('payment_status_badge', function ($row) {
                        $payClass = $row->payment_status == 'paid' ? 'bg-success text-white' : ($row->payment_status == 'pending' ? 'bg-warning text-dark' : 'bg-danger text-white');
                        return '<span class="badge ' . $payClass . ' rounded-pill px-2.5 py-1 small">' . ucfirst($row->payment_status) . '</span>';
                    })
                    ->rawColumns(['order_status_badge', 'payment_status_badge'])
                    ->make(true);
            }

            if ($type === 'quotations' && $businessSetting->is_ecommerce_system) {
                $data = \App\Models\Quotation::where('business_id', $businessId)
                    ->where(function ($q) use ($customerId, $bcId) {
                        if ($bcId) {
                            $q->where('business_customer_id', $bcId);
                        }
                        if ($customerId) {
                            $q->orWhereHas('customer', function ($cq) use ($customerId) {
                                $cq->where('customer_id', $customerId);
                            });
                        }
                    });

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('quote_no', function ($row) {
                        return $row->quotation_no;
                    })
                    ->addColumn('date', function ($row) {
                        return $row->created_at->format('d M Y');
                    })
                    ->addColumn('amount', function ($row) {
                        return '₹' . number_format($row->total, 2);
                    })
                    ->addColumn('status_badge', function ($row) {
                        $quoteStatusColors = [
                            'pending' => 'bg-info text-white',
                            'inprogress' => 'bg-warning text-dark',
                            'ordered' => 'bg-success text-white',
                            'cancel' => 'bg-danger text-white',
                            'expired' => 'bg-secondary text-white'
                        ];
                        $badgeClass = $quoteStatusColors[$row->status] ?? 'bg-secondary text-white';
                        $label = match($row->status) {
                            'pending' => 'Pending',
                            'inprogress' => 'In Progress',
                            default => ucfirst($row->status),
                        };
                        return '<span class="badge ' . $badgeClass . ' rounded-pill px-2.5 py-1 small">' . $label . '</span>';
                    })
                    ->rawColumns(['status_badge'])
                    ->make(true);
            }

            return response()->json(['success' => false, 'message' => 'Unauthorized or invalid type.']);
        }

        $businessSetting = getBusinessSettings();
        return view('business.appointment.customer.profile', compact('user', 'businessSetting'));
    }

    /**
     * Export customers data to Excel.
     */
    public function export(Request $request)
    {
        $businessId = getBusinessId();
        if (!$businessId) {
            abort(403, 'Unauthorized access.');
        }

        return Excel::download(new CustomersExport($request), 'customers-' . date('Y-m-d-His') . '.xlsx');
    }
}
