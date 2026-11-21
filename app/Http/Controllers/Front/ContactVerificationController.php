<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\SendWhatsAppOtpRequest;
use App\Http\Requests\Front\VerifyWhatsAppOtpRequest;
use App\Services\ContactVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactVerificationController extends Controller
{
    protected ContactVerificationService $verificationService;

    public function __construct(ContactVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    /**
     * Display the common contact verification page.
     *
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function show(Request $request): View|RedirectResponse
    {
        $target = $this->verificationService->getPendingVerificationTarget($request);

        // If no verification is pending, redirect the user back to their active panel
        if (!$target) {
            $fallbackUrl = $this->verificationService->resolveRedirectUrl(['return_url' => route('home')]);
            return redirect($fallbackUrl);
        }

        $logoutRoute = match ($target['guard']) {
            'expert' => route('expert.logout'),
            'pos' => route('pos.logout'),
            default => route('logout'),
        };

        return view('front.auth.verify_contact', compact('target', 'logoutRoute'));
    }

    /**
     * Dispatch WhatsApp OTP to the specified contact number.
     *
     * @param SendWhatsAppOtpRequest $request
     * @return JsonResponse
     */
    public function sendOtp(SendWhatsAppOtpRequest $request): JsonResponse
    {
        $contact = $request->getContact();
        $templateName = $request->input('template_name');
        $language = $request->input('language');

        $result = $this->verificationService->sendOtp(
            contact: $contact,
            templateName: $templateName,
            language: $language
        );

        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }

    /**
     * Verify WhatsApp OTP and mark the contact as verified on the target model.
     *
     * @param VerifyWhatsAppOtpRequest $request
     * @return JsonResponse
     */
    public function verifyOtp(VerifyWhatsAppOtpRequest $request): JsonResponse
    {
        $contact = $request->getContact();
        $otp = $request->input('otp');

        $result = $this->verificationService->verifyOtp(
            contact: $contact,
            otp: $otp
        );

        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }
}

