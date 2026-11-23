<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePushSubscriptionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Minishlink\WebPush\ContentEncoding;

class PushSubscriptionController extends Controller
{
    /**
     * Save this browser's push subscription for the signed-in user.
     */
    public function store(StorePushSubscriptionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $request->user()->updatePushSubscription(
            $validated['endpoint'],
            $validated['keys']['p256dh'],
            $validated['keys']['auth'],
            ContentEncoding::aes128gcm,
        );

        return response()->json(['subscribed' => true], 201);
    }

    /**
     * Forget one device's subscription. Other devices of the same user keep receiving pushes.
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url', 'max:1024'],
        ]);

        $request->user()->pushSubscriptions()->where('endpoint', $validated['endpoint'])->delete();

        return response()->json(['subscribed' => false]);
    }
}
