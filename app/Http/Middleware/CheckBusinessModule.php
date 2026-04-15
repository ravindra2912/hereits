<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBusinessModule
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $settings = getBusinessSettings();

        $allow = false;

        switch ($module) {
            case 'product':
                if ($settings->is_ecommerce_system) {
                    $allow = true;
                }
                break;
            case 'service':
                if ($settings->is_service_system) {
                    $allow = true;
                }
                break;
            case 'appointment':
                if ($settings->is_appointment_system) {
                    $allow = true;
                }
                break;
        }

        if (!$allow) {
            return redirect()->route('business.dashboard')->with('error', 'You do not have permission to access this module.');
        }

        return $next($request);
    }
}
