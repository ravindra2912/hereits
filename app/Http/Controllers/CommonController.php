<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class CommonController extends Controller
{
    /**
     * Download Invoice from a common URL
     */
    public function downloadInvoice($id)
    {
        $purchase = null;

        if (Auth::guard('admin')->check()) {
            // Admin can see all invoices
            $purchase = Purchase::with(['transaction', 'business', 'business.owner'])->findOrFail($id);
        } elseif (Auth::check()) {
            $user = Auth::user();
            if ($user->role == 'Business') {
                // Business owners can only see their own invoices
                $purchase = Purchase::where('business_id', $user->business_id)
                    ->with(['transaction', 'business', 'business.owner'])
                    ->findOrFail($id);
            }
        }

        if (!$purchase) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        $pdf = Pdf::loadView('business.purchase.invoice', compact('purchase'));
        return $pdf->download('invoice-' . $purchase->id . '.pdf');
    }

    /**
     * Display the user's profile form.
     */
    public function getCities(Request $request)
    {
        $citeis = getCities($request->state_id);
        return response()->json($citeis);
    }
}
