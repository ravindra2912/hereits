<?php

namespace App\Http\Controllers\Business;

use App\Models\BusinessLeaglePage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Traits\CacheManagement;

class BusinessLeaglePageController extends Controller
{
    use CacheManagement;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pages = BusinessLeaglePage::where('business_id', getBusinessId())
            ->orderBy('created_at', 'desc')
            ->get();

        $allTypes = [
            'privacy_policy' => 'Privacy Policy',
            'term_and_condition' => 'Terms & Conditions',
            'refuncd_policy' => 'Refund Policy',
            'quotation_policy' => 'Quotation Policy'
        ];

        return view('business.leagle_page.index', compact('pages', 'allTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $existingTypes = BusinessLeaglePage::where('business_id', getBusinessId())->pluck('type')->toArray();
        $allTypes = [
            'privacy_policy' => 'Privacy Policy',
            'term_and_condition' => 'Terms & Conditions',
            'refuncd_policy' => 'Refund Policy',
            'quotation_policy' => 'Quotation Policy'
        ];
        $availableTypes = array_diff_key($allTypes, array_flip($existingTypes));

        if (empty($availableTypes)) {
            return redirect()->route('business.setting.business.leagle-page.index')
                ->with('error', 'All legal pages have already been created.');
        }

        return view('business.leagle_page.create', compact('availableTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $success = false;
        $message = 'Something went wrong!';
        $redirect = route('business.setting.business.leagle-page.index');
        $data = [];

        try {
            $rules = [
                'type' => [
                    'required',
                    'in:privacy_policy,term_and_condition,refuncd_policy,quotation_policy',
                    Rule::unique('business_leagle_pages')->where(function ($query) {
                        return $query->where('business_id', getBusinessId());
                    })
                ],
                'description' => 'required|string',
                'status' => 'required|in:active,inactive'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()]);
            }

            $insert = new BusinessLeaglePage();
            $insert->business_id = getBusinessId();
            $insert->type = $request->type;
            $insert->description = cleanHtml($request->description);
            $insert->status = $request->status;
            $insert->save();

            $this->clearBusinessLegalPageCache(getBusinessId());

            $success = true;
            $message = 'Legal page created successfully.';
        } catch (\Exception $e) {
            $message = checkResponse($e);
        }

        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $page = BusinessLeaglePage::where('business_id', getBusinessId())->findOrFail($id);
        $allTypes = [
            'privacy_policy' => 'Privacy Policy',
            'term_and_condition' => 'Terms & Conditions',
            'refuncd_policy' => 'Refund Policy',
            'quotation_policy' => 'Quotation Policy'
        ];

        return view('business.leagle_page.edit', compact('page', 'allTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $success = false;
        $message = 'Something went wrong!';
        $redirect = route('business.setting.business.leagle-page.index');
        $data = [];

        try {
            $page = BusinessLeaglePage::where('business_id', getBusinessId())->findOrFail($id);

            $rules = [
                'description' => 'required|string',
                'status' => 'required|in:active,inactive'
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()]);
            }

            $page->description = cleanHtml($request->description);
            $page->status = $request->status;
            $page->save();

            $this->clearBusinessLegalPageCache(getBusinessId());

            $success = true;
            $message = 'Legal page updated successfully.';
        } catch (\Exception $e) {
            $message = checkResponse($e);
        }

        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $success = false;
        $message = 'Something went wrong!';
        $redirect = route('business.setting.business.leagle-page.index');
        $data = [];

        try {
            $page = BusinessLeaglePage::where('business_id', getBusinessId())->findOrFail($id);
            $page->delete();

            $this->clearBusinessLegalPageCache(getBusinessId());

            $success = true;
            $message = 'Legal page deleted successfully.';
        } catch (\Exception $e) {
            $message = checkResponse($e);
        }

        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }
}
