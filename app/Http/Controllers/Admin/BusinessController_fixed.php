<?php



namespace App\Http\Controllers\Admin;



use App\Models\User;

use App\Models\Business;

use Illuminate\Http\Request;

use App\Models\BusinessSetting;

use Yajra\DataTables\DataTables;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;



class BusinessController extends Controller

{

    /**

     * Display the user's profile form.

     */

    public function index(Request $request)

    {

        if ($request->ajax()) {



            $data = Business::with(['owner', 'businessCategory'])

                ->select('id', 'owner_id', 'business_category_id', 'slug', 'name', 'business_image', 'business_logo', 'address', 'contact', 'status')

                ->orderBy('id', 'desc');



            return Datatables::of($data)

                ->addIndexColumn()

                ->addColumn('owner', function ($row) {

                    return isset($row->owner) && !empty($row->owner->first_name) ? $row->owner->first_name : '';

                })

                ->addColumn('category', function ($row) {

                    return isset($row->businessCategory) && !empty($row->businessCategory->name) ? $row->businessCategory->name : '';

                    return $row->businessCategory->name;

                })

                ->addColumn('img', function ($row) {

                    return '<div class="text-center d-flex gap-2 justify-content-center">

                        <img src="' . getImage($row->business_logo) . '" class="avatar-img rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;" title="Logo" />

                        <img src="' . getImage($row->business_image) . '" class="avatar-img rounded border" style="width: 40px; height: 40px; object-fit: cover;" title="Image" />

                    </div>';

                })

                ->addColumn('status', function ($row) {

                    return '<span class="badge bg-' . ($row->status == 'active' ? 'success' : 'warning') . '">' . ucfirst($row->status) . '</span>';

                })

                ->addColumn('action', function ($row) {

                    $url = route('admin.business.destroy', $row->id);

                    $url = "'" . $url . "'";

                    return ' <div class="text-center">

                    <a href="' . route('admin.business.edit', $row->id) . '" class="btn btn-outline-primary btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>

                    <a href="' . route('business-details', ['business_slug' => $row->slug]) . '" target="_blank" class="btn btn-outline-success btn-sm" title="Redirect to business"><i class="bi bi-box-arrow-in-right"></i></a>

