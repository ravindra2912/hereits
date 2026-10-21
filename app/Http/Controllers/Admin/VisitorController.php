<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VisitorAnalyticsRequest;
use App\Services\AdminVisitorAnalyticsService;
use Illuminate\View\View;

class VisitorController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected AdminVisitorAnalyticsService $analyticsService)
    {
    }

    /**
     * Display SaaS-wide visitor analytics dashboard with date filters.
     */
    public function index(VisitorAnalyticsRequest $request): View
    {
        $validated = $request->validated();
        $filter = $validated['filter'] ?? 'today';
        $fromDate = $validated['from_date'] ?? null;
        $toDate = $validated['to_date'] ?? null;

        $analytics = $this->analyticsService->getAnalytics($filter, $fromDate, $toDate);

        return view('admin.visitors.index', $analytics);
    }
}
