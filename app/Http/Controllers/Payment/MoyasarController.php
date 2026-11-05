<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\SubscriptionCheckout;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoints Moyasar talks to. Neither trusts the request body: both only use it to find the
 * payment, then confirm() re-reads the invoice from the Moyasar API with the secret key.
 */
class MoyasarController extends Controller
{
    /**
     * Server-to-server notification Moyasar POSTs (the invoice object) when an invoice is paid.
     */
    public function callback(Request $request, SubscriptionCheckout $checkout): JsonResponse
    {
        $invoiceId = $request->input('id');

        $payment = is_string($invoiceId)
            ? Payment::where('gateway_invoice_id', $invoiceId)->first()
            : null;

        if (! $payment) {
            return response()->json(['status' => false], 404);
        }

        $checkout->confirm($payment);

        return response()->json(['status' => true]);
    }

    /**
     * Where the payer lands after the hosted payment page (paid or back button). The app's
     * web view watches for this URL, closes itself and then asks the API for the payment.
     */
    public function return(Payment $payment, SubscriptionCheckout $checkout): View
    {
        $payment = $checkout->confirm($payment);

        return view('payments.result', ['payment' => $payment]);
    }
}
