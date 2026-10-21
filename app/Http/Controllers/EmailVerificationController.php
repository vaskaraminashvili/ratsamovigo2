<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('lists.index')->with('status', 'ელფოსტა დადასტურდა.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with('status', 'დამადასტურებელი ბმული გაიგზავნა.');
    }
}
