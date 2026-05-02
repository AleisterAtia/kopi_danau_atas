<?php

namespace App\Http\Controllers;

use App\Services\MidtransService;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request, MidtransService $midtrans)
    {
        $notification = $request->all();

        if (! $midtrans->verifySignature($notification)) {
            return response('Invalid signature', 403);
        }

        $midtrans->handleNotification($notification);

        return response('OK', 200);
    }
}
