<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Business
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // dd(Auth::user());
        // return $next($request);
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role == 'Business') {
                if (!session()->has('user_businesses_list') || !session()->has('current_business_details') || !session()->has('business_permissions')) {
                    $user->syncBusinessSessionData();
                }

                $businessStatus = session('business_status', 'active');
                if ($businessStatus == 'baned' || $businessStatus == 'in-active') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    exit('Your business is not active, Please contact to Admin');
                }
                return $next($request);
            } elseif ($user->role == 'User') {
                $businessId = $user->business_id;
                
                // Fallback in case business_id is not set on the user model yet
                if (!$businessId) {
                    $businessUser = \App\Models\BusinessUser::with('role')
                        ->where('user_id', $user->id)
                        ->get()
                        ->filter(function ($bu) {
                            return !empty($bu->role?->permissions['business_access']);
                        })
                        ->first();

                    if ($businessUser) {
                        $businessId = $businessUser->business_id;
                        $user->business_id = $businessId;
                        $user->save();
                    }
                }

                if ($businessId) {
                    $needsSync = !session()->has('user_businesses_list') ||
                        !session()->has('current_business_details') ||
                        !session()->has('business_permissions') ||
                        !session()->has('business_permissions_last_synced') ||
                        now()->diffInMinutes(session('business_permissions_last_synced')) >= 10;

                    if ($needsSync) {
                        $user->syncBusinessSessionData($businessId);
                    }
                    $permissions = session('business_permissions', []);

                    if (!empty($permissions['business_access'])) {
                        // Check if the business is active (retrieved from session status)
                        $businessStatus = session('business_status', 'active');
                        if ($businessStatus == 'baned' || $businessStatus == 'in-active') {
                            Auth::logout();
                            $request->session()->invalidate();
                            $request->session()->regenerateToken();
                            exit('This business is not active, Please contact to Admin');
                        }

                        // Enforce route level permission checks for staff members
                        $routeName = $request->route()->getName();

                        // Owner-only routes
                        $ownerOnlyRoutes = [
                            'business.appointment.customers',
                            'business.analytics',
                            'business.credits',
                            'business.purchase.history',
                            'business.setting.business.seo',
                            'business.setting.business.configuration',
                            'business.setting.business.about_us',
                            'business.setting.business.leagle-page',
                            'business.setting.business.timing',
                        ];

                        foreach ($ownerOnlyRoutes as $prefix) {
                            if (str_starts_with($routeName, $prefix)) {
                                if ($request->ajax()) {
                                    return response()->json(['success' => false, 'message' => 'Unauthorized. This section is only accessible by the business owner.'], 403);
                                }
                                abort(403, 'Unauthorized. This section is only accessible by the business owner.');
                            }
                        }

                        // Business profile is owner only, except timing and configuration and seo (which are also listed above)
                        if ($routeName === 'business.setting.business') {
                            if ($request->ajax()) {
                                return response()->json(['success' => false, 'message' => 'Unauthorized. This section is only accessible by the business owner.'], 403);
                            }
                            abort(403, 'Unauthorized. This section is only accessible by the business owner.');
                        }

                        // Map route name prefixes to business permissions
                        $permissionMapping = [
                            // department
                            'business.appointment.department.index' => 'view_department',
                            'business.appointment.department.create' => 'add_department',
                            'business.appointment.department.store' => 'add_department',
                            'business.appointment.department.edit' => 'edit_department',
                            'business.appointment.department.update' => 'edit_department',
                            'business.appointment.department.destroy' => 'delete_department',

                            // experts
                            'business.appointment.expert.index' => 'view_experts',
                            'business.appointment.expert.create' => 'add_experts',
                            'business.appointment.expert.store' => 'add_experts',
                            'business.appointment.expert.edit' => 'edit_experts',
                            'business.appointment.expert.update' => 'edit_experts',
                            'business.appointment.expert.status-toggle' => 'edit_experts',
                            'business.appointment.expert.timing' => 'edit_experts',
                            'business.appointment.expert.destroy' => 'delete_experts',

                            // appointments
                            'business.appointment.bookings.index' => 'view_appointments',
                            'business.appointment.bookings.pending' => 'view_appointments',
                            'business.appointment.bookings.export' => 'view_appointments',
                            'business.appointment.bookings.search-product' => 'view_appointments',
                            'business.appointment.bookings.get.expert' => 'view_appointments',
                            'business.appointment.bookings.create' => 'add_appointments',
                            'business.appointment.bookings.store' => 'add_appointments',
                            'business.appointment.bookings.edit' => 'edit_appointments',
                            'business.appointment.bookings.update' => 'edit_appointments',
                            'business.appointment.bookings.change.status' => 'edit_appointments',
                            'business.appointment.bookings.destroy' => 'delete_appointments',

                            // product category
                            'business.product-category.index' => 'view_product_category',
                            'business.product-category.create' => 'add_product_category',
                            'business.product-category.store' => 'add_product_category',
                            'business.product-category.edit' => 'edit_product_category',
                            'business.product-category.update' => 'edit_product_category',
                            'business.product-category.status-toggle' => 'edit_product_category',
                            'business.product-category.reorder' => 'edit_product_category',
                            'business.product-category.destroy' => 'delete_product_category',

                            // labels
                            'business.label.index' => 'view_labels',
                            'business.label.create' => 'add_labels',
                            'business.label.store' => 'add_labels',
                            'business.label.edit' => 'edit_labels',
                            'business.label.update' => 'edit_labels',
                            'business.label.status-toggle' => 'edit_labels',
                            'business.label.reorder' => 'edit_labels',
                            'business.label.destroy' => 'delete_labels',

                            // products
                            'business.product.index' => 'view_products',
                            'business.product.showDetail' => 'view_products',
                            'business.product.export' => 'view_products',
                            'business.product.export-stock-price' => 'view_products',
                            'business.product.create' => 'add_products',
                            'business.product.store' => 'add_products',
                            'business.product.import' => 'add_products',
                            'business.product.edit' => 'edit_products',
                            'business.product.update' => 'edit_products',
                            'business.product.status-toggle' => 'edit_products',
                            'business.product.inventory' => 'edit_products',
                            'business.product.update-quantity' => 'edit_products',
                            'business.product.import-stock-price' => 'edit_products',
                            'business.product.image.' => 'edit_products',
                            'business.product.destroy' => 'delete_products',

                            // product sharing
                            'business.product.share.index' => 'view_product_sharing',
                            'business.product.share.search_businesses' => 'view_product_sharing',
                            'business.product.share.create' => 'add_product_sharing',
                            'business.product.share.store' => 'add_product_sharing',
                            'business.product.share.edit' => 'edit_product_sharing',
                            'business.product.share.update' => 'edit_product_sharing',
                            'business.product.share.manage' => 'edit_product_sharing',
                            'business.product.share.status-toggle' => 'edit_product_sharing',
                            'business.product.share.destroy' => 'delete_product_sharing',

                            // offers
                            'business.offer.search-items' => 'view_offers',
                            'business.offer.store' => 'add_offers',
                            'business.offer.update' => 'edit_offers',
                            'business.offer.status-toggle' => 'edit_offers',
                            'business.offer.destroy' => 'delete_offers',
                            'business.offer.show' => 'view_offers',
                            'business.offer.index' => 'view_offers',
                            'business.offer' => 'view_offers',

                            // service category
                            'business.service-category.index' => 'view_service_category',
                            'business.service-category.create' => 'add_service_category',
                            'business.service-category.store' => 'add_service_category',
                            'business.service-category.edit' => 'edit_service_category',
                            'business.service-category.update' => 'edit_service_category',
                            'business.service-category.status-toggle' => 'edit_service_category',
                            'business.service-category.reorder' => 'edit_service_category',
                            'business.service-category.destroy' => 'delete_service_category',

                            // service list
                            'business.service.index' => 'view_services',
                            'business.service.create' => 'add_services',
                            'business.service.store' => 'add_services',
                            'business.service.edit' => 'edit_services',
                            'business.service.update' => 'edit_services',
                            'business.service.status-toggle' => 'edit_services',
                            'business.service.destroy' => 'delete_services',

                            // role
                            'business.role.index' => 'view_roles',
                            'business.role.create' => 'add_roles',
                            'business.role.store' => 'add_roles',
                            'business.role.edit' => 'edit_roles',
                            'business.role.update' => 'edit_roles',
                            'business.role.destroy' => 'delete_roles',

                            // staff
                            'business.staff.index' => 'view_staff',
                            'business.staff.history' => 'view_staff',
                            'business.staff.check-email' => 'add_staff',
                            'business.staff.create' => 'add_staff',
                            'business.staff.store' => 'add_staff',
                            'business.staff.edit' => 'edit_staff',
                            'business.staff.update' => 'edit_staff',
                            'business.staff.destroy' => 'delete_staff',

                            // gallery
                            'business.gallery.index' => 'view_gallery',
                            'business.gallery.create' => 'add_gallery',
                            'business.gallery.store' => 'add_gallery',
                            'business.gallery.edit' => 'edit_gallery',
                            'business.gallery.update' => 'edit_gallery',
                            'business.gallery.status-toggle' => 'edit_gallery',
                            'business.gallery.destroy' => 'delete_gallery',

                            // blog
                            'business.blog.index' => 'view_blogs',
                            'business.blog.upload-image' => 'add_blogs',
                            'business.blog.uploadImage' => 'add_blogs',
                            'business.blog.create' => 'add_blogs',
                            'business.blog.store' => 'add_blogs',
                            'business.blog.edit' => 'edit_blogs',
                            'business.blog.update' => 'edit_blogs',
                            'business.blog.destroy' => 'delete_blogs',
                            'business.blog.status-toggle' => 'edit_blogs',

                            // lucky draw
                            'business.lucky-draw.index' => 'view_lucky_draw',
                            'business.lucky-draw.show' => 'view_lucky_draw',
                            'business.lucky-draw.source-prizes' => 'view_lucky_draw',
                            'business.lucky-draw.create' => 'add_lucky_draw',
                            'business.lucky-draw.store' => 'add_lucky_draw',
                            'business.lucky-draw.import-prizes' => 'add_lucky_draw',
                            'business.lucky-draw.edit' => 'edit_lucky_draw',
                            'business.lucky-draw.update' => 'edit_lucky_draw',
                            'business.lucky-draw.change-status' => 'edit_lucky_draw',
                            'business.lucky-draw.destroy' => 'delete_lucky_draw',
                            'business.lucky-draw.prize.store' => 'add_lucky_draw',
                            'business.lucky-draw.prize.delete' => 'delete_lucky_draw',
                            'business.lucky-draw.generate-entry' => 'add_lucky_draw',
                            'business.lucky-draw.entry.update' => 'edit_lucky_draw',

                            // home layouts
                            'business.home-management.layouts.search-items' => 'view_home_layouts',
                            'business.home-management.layouts.store' => 'add_home_layouts',
                            'business.home-management.layouts.edit' => 'edit_home_layouts',
                            'business.home-management.layouts.update-order' => 'edit_home_layouts',
                            'business.home-management.layouts.update' => 'edit_home_layouts',
                            'business.home-management.layouts.toggle-status' => 'edit_home_layouts',
                            'business.home-management.layouts.item.toggle-status' => 'edit_home_layouts',
                            'business.home-management.layouts.destroy' => 'delete_home_layouts',
                            'business.home-management.layouts' => 'view_home_layouts',

                            // home settings
                            'business.home-management.google-sheets-setup' => 'view_home_settings',
                            'business.home-management.settings.store' => 'add_home_settings',
                            'business.home-management.settings.edit' => 'edit_home_settings',
                            'business.home-management.settings.update' => 'edit_home_settings',
                            'business.home-management.settings.toggle-status' => 'edit_home_settings',
                            'business.home-management.settings.destroy' => 'delete_home_settings',
                            'business.home-management.settings' => 'view_home_settings',

                            // campaign leads
                            'business.home-management.leads.store' => 'add_campaign_leads',
                            'business.home-management.leads.edit' => 'edit_campaign_leads',
                            'business.home-management.leads.update' => 'edit_campaign_leads',
                            'business.home-management.leads.recall-webhook' => 'edit_campaign_leads',
                            'business.home-management.leads.destroy' => 'delete_campaign_leads',
                            'business.home-management.leads' => 'view_campaign_leads',
                        ];

                        if ($routeName === 'business.home-management') {
                            if (!checkBusinessPermission('view_home_layouts') && !checkBusinessPermission('view_home_settings') && !checkBusinessPermission('view_campaign_leads')) {
                                if ($request->ajax()) {
                                    return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to access Home Management.'], 403);
                                }
                                abort(403, 'Unauthorized. You do not have permission to access Home Management.');
                            }
                        }

                        foreach ($permissionMapping as $routePattern => $permission) {
                            if (str_starts_with($routeName, $routePattern)) {
                                if (!checkBusinessPermission($permission)) {
                                    if ($request->ajax()) {
                                        return response()->json(['success' => false, 'message' => 'Unauthorized. You do not have permission to access this section.'], 403);
                                    }
                                    abort(403, 'Unauthorized. You do not have permission to access this section.');
                                }
                            }
                        }

                        return $next($request);
                    }
                }
            }

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Un-Authenticated Access', 'data' => array()]);
            } else {
                return redirect()->route('business.login');
            }
        } else {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Session Expired', 'data' => array()]);
            } else {
                return redirect()->route('business.login');
            }
        }
    }
}
