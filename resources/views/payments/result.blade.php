@php
    $isPaid = $payment->status === \App\Enums\PaymentStatusEnum::PAID;
    $isPending = $payment->status === \App\Enums\PaymentStatusEnum::PENDING;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Payment') }}</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f7fafc; color: #1a202c; font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif; }
        .card { max-width: 420px; margin: 16px; padding: 32px 24px; background: #fff; border-radius: 16px; box-shadow: 0 1px 3px rgba(0, 0, 0, .1); text-align: center; }
        .icon { font-size: 48px; line-height: 1; }
        h1 { font-size: 20px; margin: 16px 0 8px; }
        p { margin: 0; color: #4a5568; }
    </style>
</head>
<body>
    <div class="card" data-payment-status="{{ $payment->status->value }}">
        <div class="icon">{{ $isPaid ? '✅' : ($isPending ? '⏳' : '❌') }}</div>
        @if ($isPaid)
            <h1>{{ __('Payment successful') }}</h1>
            <p>{{ __('Your subscription is now active. You can return to the app.') }}</p>
        @elseif ($isPending)
            <h1>{{ __('Payment not completed yet') }}</h1>
            <p>{{ __('Return to the app to check your payment status.') }}</p>
        @else
            <h1>{{ __('Payment failed') }}</h1>
            <p>{{ __('The payment was not completed. You can return to the app and try again.') }}</p>
        @endif
    </div>
</body>
</html>
