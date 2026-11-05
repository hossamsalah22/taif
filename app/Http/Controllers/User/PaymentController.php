<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\PaymentResource;
use App\Models\Payment;
use App\Services\SubscriptionCheckout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * The app calls this after the payment page closes: the payment is re-checked on Moyasar
     * so the result does not depend on the callback having arrived first.
     */
    public function show(Request $request, Payment $payment, SubscriptionCheckout $checkout): JsonResponse
    {
        abort_unless($payment->parent_id === $request->user()->id, 404);

        $payment = $checkout->confirm($payment)->load('subscription');

        return $this->successResponse(__('Payment Details'), [
            'payment' => new PaymentResource($payment),
        ]);
    }
}
