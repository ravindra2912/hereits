<?php

namespace App\Http\Controllers\Business;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class HomeManagementController extends Controller
{
    private function getCategoryTypeAndField($listType)
    {
        $type = 'Products';
        $field = 'show_in_home'; // default

        if ($listType === 'home') {
            $type = 'Products';
            $field = 'show_in_home';
        } elseif ($listType === 'home_products') {
            $type = 'Products';
            $field = 'show_in_home_with_items';
        } elseif ($listType === 'home_services') {
            $type = 'Services';
            $field = 'show_in_home';
        } elseif ($listType === 'home_services_with_items') {
            $type = 'Services';
            $field = 'show_in_home_with_items';
        }

        return [$type, $field];
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $listType = $request->get('list_type');

            [$categoryType, $field] = $this->getCategoryTypeAndField($listType);

            $data = Category::where('business_id', getBusinessId())
                ->where('type', $categoryType)
                ->where($field, 1)
                ->orderBy('sort_order', 'asc')
                ->select('id', 'name', 'status')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }

        return view('business.home-management.index');
    }

    public function searchCategory(Request $request)
    {
        $businessId = getBusinessId();
        $term = $request->get('q');
        $listType = $request->get('list_type');

        [$categoryType, $field] = $this->getCategoryTypeAndField($listType);

        $query = Category::where('business_id', $businessId)
            ->where('type', $categoryType)
            ->where(function ($q) use ($field) {
                $q->where($field, 0)->orWhereNull($field);
            });

        if (!empty($term)) {
            $query->where('name', 'like', '%' . $term . '%');
        }

        $categories = $query->select('id', 'name as text')->limit(20)->get();

        return response()->json($categories);
    }

    public function addCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'list_type' => 'required|in:home,home_products,home_services,home_services_with_items',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $businessId = getBusinessId();
        $category = Category::where('business_id', $businessId)->find($request->category_id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found.']);
        }

        [, $field] = $this->getCategoryTypeAndField($request->list_type);
        $category->$field = 1;
        $category->save();

        return response()->json(['success' => true, 'message' => 'Category added successfully.']);
    }

    public function removeCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'list_type' => 'required|in:home,home_products,home_services,home_services_with_items',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()]);
        }

        $businessId = getBusinessId();
        $category = Category::where('business_id', $businessId)->find($request->category_id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found.']);
        }

        [, $field] = $this->getCategoryTypeAndField($request->list_type);
        $category->$field = 0;
        $category->save();

        return response()->json(['success' => true, 'message' => 'Category removed successfully.']);
    }
}
