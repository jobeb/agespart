<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        $request->user()->updatePushSubscription(
            $data['endpoint'],
            $data['keys']['p256dh'],
            $data['keys']['auth'],
            'aes128gcm',
        );

        return response()->noContent();
    }

    public function destroy(Request $request)
    {
        $data = $request->validate(['endpoint' => ['required', 'string']]);

        $request->user()->deletePushSubscription($data['endpoint']);

        return response()->noContent();
    }
}
