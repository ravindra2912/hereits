<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\BusinessAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class VisitorController extends Controller
{
    public function __construct(protected BusinessAnalyticsService $analyticsService)
    {
    }

    /**
     * Display comprehensive visitor analytics page.
     */
    public function index(Request $request): View
    {
        $businessId = Auth::user()->business_id;

        $businessDetails = Business::select('id', 'status', 'name')
            ->with([
                'businessSetting' => function ($q) {
                    $q->select('business_id', 'is_ecommerce_system', 'is_service_system', 'is_appointment_system', 'is_appointment_with_department');
                }
            ])
            ->findOrFail($businessId);

        $businessSettings = $businessDetails->businessSetting;

        $filter = $request->get('filter', 'today');
        if (!in_array($filter, ['today', 'daily', 'monthly', 'yearly', 'custom'])) {
            $filter = 'today';
        }

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $analytics = $this->analyticsService->getVisitorAnalytics($businessId, $filter, $fromDate, $toDate);

        return view('business.visitors.index', array_merge(
            compact('businessDetails', 'businessSettings', 'filter', 'fromDate', 'toDate'),
            $analytics
        ));
    }
}
