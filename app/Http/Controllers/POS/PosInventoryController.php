<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;

class PosInventoryController extends Controller
{
    public function index(Request $request)
    {
        if (!checkPosPermission('view_inventory')) {
            abort(403, 'Unauthorized access.');
        }

        $business_id = getPosBusinessId();

        if ($request->ajax()) {
            $data = Product::with('category')
                ->where('business_id', $business_id);

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('product_info', function ($row) {
                    $img = $row->firstImage ? getImage($row->firstImage->image_url) : asset('assets/images/default.png');
                    return '
                    <div class="d-flex align-items-center">
                        <img src="' . $img . '" class="rounded me-2" width="40" height="40" style="object-fit:cover;">
                        <div>
                            <div class="fw-bold text-dark">' . $row->name . '</div>
                            <div class="small text-muted">' . ($row->sku ?: 'No SKU') . '</div>
                        </div>
                    </div>';
                })
                ->addColumn('category_name', function ($row) {
                    return $row->category->name ?? 'N/A';
                })
                ->addColumn('stock_status', function ($row) {
                    if ($row->quantity <= 0) {
                        return '<span class="badge bg-danger rounded-pill px-3">Out of Stock</span>';
                    } elseif ($row->quantity <= 10) {
                        return '<span class="badge bg-warning text-dark rounded-pill px-3">Low Stock</span>';
                    }
                    return '<span class="badge bg-success rounded-pill px-3">In Stock</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (checkPosPermission('edit_inventory')) {
                        $btn .= '<button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-2 update-stock-btn" 
                                    data-id="' . $row->id . '" 
                                    data-name="' . $row->name . '" 
                                    data-current="' . $row->quantity . '">
                                    <i class="bi bi-plus-slash-minus me-1"></i> Adjust
                                </button>';
                    }
                    return $btn;
                })
                ->rawColumns(['product_info', 'stock_status', 'action'])
                ->make(true);
        }

        return view('pos.inventory.index');
    }

    public function updateStock(Request $request)
    {
        if (!checkPosPermission('edit_inventory')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized Access'], 403);
        }

        $request->validate([
            'id' => 'required|exists:products,id',
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer|min:0',
        ]);

        $product = Product::where('business_id', getPosBusinessId())->findOrFail($request->id);

        if ($request->adjustment_type === 'add') {
            $product->increment('quantity', $request->quantity);
        } elseif ($request->adjustment_type === 'subtract') {
            if ($product->quantity < $request->quantity) {
                return response()->json(['success' => false, 'message' => 'Insufficient stock to subtract.'], 422);
            }
            $product->decrement('quantity', $request->quantity);
        } else {
            $product->update(['quantity' => $request->quantity]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Inventory updated successfully for ' . $product->name,
            'new_quantity' => $product->quantity
        ]);
    }
}
