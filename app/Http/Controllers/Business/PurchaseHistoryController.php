<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseHistoryController extends Controller
{
    /**
     * Display a listing of the purchase history.
     */
    public function index()
    {
        $business_id = getBusinessId();

        $history = Purchase::where('business_id', $business_id)
            ->with(['transaction', 'plan'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('business.purchase.history', compact('history'));
    }

    /**
     * Display the specified purchase detail.
     */
    public function show($id)
    {
        $business_id = getBusinessId();

        $purchase = Purchase::where('business_id', $business_id)
            ->with(['transaction', 'plan', 'business'])
            ->findOrFail($id);

        return view('business.purchase.detail', compact('purchase'));
    }

    /**
     * Download the invoice for the specified purchase.
     */
    public function downloadInvoice($id)
    {
        $business_id = getBusinessId();

        $purchase = Purchase::where('business_id', $business_id)
            ->with(['transaction', 'plan', 'business'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('business.purchase.invoice', compact('purchase'));
        return $pdf->download('invoice-' . $purchase->id . '.pdf');
    }
}
