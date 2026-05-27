<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBusinessPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module, ?string $submodule = null, ?string $action = null): Response
    {
        // Convert 'null' string to actual null type for route parameter passing convenience
        $subVal = ($submodule === 'null' || $submodule === '') ? null : $submodule;
        $actVal = ($action === 'null' || $action === '') ? null : $action;

        // Smart auto-detect permission action if submodule is specified but action is left empty
        if ($subVal !== null && $actVal === null) {
            $method = strtoupper($request->method());
            $routeName = $request->route() ? $request->route()->getName() : '';
            
            if ($method === 'GET') {
                $actVal = 'view';
            } elseif ($method === 'DELETE') {
                $actVal = 'delete';
            } elseif (in_array($method, ['PUT', 'PATCH'])) {
                $actVal = 'update';
            } elseif ($method === 'POST') {
                // Smart regex check for route names that indicate updates, deletes or additions
                if (preg_match('/(update|status|timing|edit|reorder|remove|add-category|remove-category)/i', $routeName)) {
                    $actVal = 'update';
                } elseif (preg_match('/(delete|destroy)/i', $routeName)) {
                    $actVal = 'delete';
                } else {
                    $actVal = 'add';
                }
            } else {
                $actVal = 'view';
            }
        }

        if (!checkBusinessPermission($module, $subVal, $actVal)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized Access']);
            }
            abort(403, 'Unauthorized Access to Business Panel Feature.');
        }

        return $next($request);
    }
}
