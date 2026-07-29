<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Stores/removes the browser's Push API subscription for the logged-in
 * admin, so MidtransService can later reach them via BookingPaidPushNotification.
 */
class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $request->user()->updatePushSubscription(
            endpoint: $data['endpoint'],
            key: $data['keys']['p256dh'],
            token: $data['keys']['auth'],
            contentEncoding: 'aesgcm',
        );

        return response()->noContent();
    }

    public function destroy(Request $request)
    {
        $request->validate(['endpoint' => 'required|string']);

        $request->user()->pushSubscriptions()->where('endpoint', $request->string('endpoint'))->delete();

        return response()->noContent();
    }
}
