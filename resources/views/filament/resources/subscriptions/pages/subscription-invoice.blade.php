@php
    use App\Enums\PaymentStatusEnum;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Number;

    $money = fn (float $value): string => Number::currency($value, in: $currency, locale: app()->getLocale());
    $date = fn ($value): string => $value ? \Illuminate\Support\Carbon::parse($value)->translatedFormat('j F Y') : '—';
    $statusColors = [
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/30',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/30',
        'danger' => 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/30',
    ];
    $statusClasses = $statusColors[PaymentStatusEnum::color($payment->status)] ?? $statusColors['warning'];
@endphp

<x-filament-panels::page>
    <div class="subscription-invoice mx-auto w-full max-w-4xl">
        <article class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 print:rounded-none print:shadow-none print:ring-0">
            {{-- Header --}}
            <header class="relative border-b border-gray-100 bg-gradient-to-br from-amber-50 via-white to-white px-6 py-8 sm:px-10 dark:border-white/10 dark:from-amber-500/10 dark:via-gray-900 dark:to-gray-900">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-center gap-4">
                        <img src="{{ Storage::url('tayf.png') }}" alt="{{ config('app.name') }}" class="h-16 w-auto">
                    </div>

                    <div class="flex flex-col gap-2 sm:items-end">
                        <span class="text-xs font-semibold tracking-widest text-amber-600 uppercase dark:text-amber-400">{{ __('Invoice') }}</span>
                        <h2 class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white" dir="ltr">{{ $invoiceNumber }}</h2>
                        <span class="inline-flex w-fit items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $statusClasses }}">
                            <span class="size-1.5 rounded-full bg-current"></span>
                            {{ PaymentStatusEnum::label($payment->status) }}
                        </span>
                    </div>
                </div>
            </header>

            {{-- Parties & meta --}}
            <section class="grid gap-8 px-6 py-8 sm:grid-cols-2 sm:px-10">
                <div>
                    <h3 class="mb-3 text-xs font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">{{ __('Billed to') }}</h3>
                    <p class="text-lg font-semibold text-gray-950 dark:text-white">{{ $subscription->parent?->name ?? '—' }}</p>
                    @if ($subscription->parent?->phone)
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300" dir="ltr">{{ ltrim($subscription->parent->phone, '+') }}</p>
                    @endif
                    @if ($subscription->parent?->email)
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $subscription->parent->email }}</p>
                    @endif
                </div>

                <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Issue Date') }}</dt>
                        <dd class="mt-1 font-medium text-gray-950 dark:text-white">{{ $date($payment->created_at) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Paid At') }}</dt>
                        <dd class="mt-1 font-medium text-gray-950 dark:text-white">{{ $date($payment->paid_at) }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-gray-500 dark:text-gray-400">{{ __('Invoice ID') }}</dt>
                        <dd class="mt-1 font-mono text-xs break-all text-gray-950 dark:text-white" dir="ltr">{{ $payment->gateway_invoice_id ?? '—' }}</dd>
                    </div>
                    @if ($payment->gateway_payment_id)
                        <div class="col-span-2">
                            <dt class="text-gray-500 dark:text-gray-400">{{ __('Transaction ID') }}</dt>
                            <dd class="mt-1 font-mono text-xs break-all text-gray-950 dark:text-white" dir="ltr">{{ $payment->gateway_payment_id }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            {{-- Line items --}}
            <section class="px-6 sm:px-10">
                <div class="overflow-hidden rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 dark:bg-white/5 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-start font-semibold">{{ __('Description') }}</th>
                                <th class="hidden px-4 py-3 text-start font-semibold sm:table-cell">{{ __('Subscription Period') }}</th>
                                <th class="px-4 py-3 text-end font-semibold">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                            <tr>
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-gray-950 dark:text-white">{{ $packageName }}</p>
                                    <p class="mt-1 text-xs text-gray-500 sm:hidden dark:text-gray-400">{{ $date($subscription->start_date) }} — {{ $date($subscription->expiry_date) }}</p>
                                </td>
                                <td class="hidden px-4 py-4 text-gray-600 sm:table-cell dark:text-gray-300">
                                    {{ $date($subscription->start_date) }} — {{ $date($subscription->expiry_date) }}
                                </td>
                                <td class="px-4 py-4 text-end font-semibold whitespace-nowrap text-gray-950 dark:text-white">{{ $money($amount) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Totals --}}
                <div class="mt-6 flex justify-end">
                    <dl class="w-full max-w-xs space-y-3 text-sm">
                        <div class="flex justify-between text-gray-600 dark:text-gray-300">
                            <dt>{{ __('Subtotal') }}</dt>
                            <dd class="whitespace-nowrap">{{ $money($amount) }}</dd>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-200 pt-3 dark:border-white/10">
                            <dt class="text-base font-semibold text-gray-950 dark:text-white">{{ __('Total') }}</dt>
                            <dd class="text-xl font-bold whitespace-nowrap text-amber-600 dark:text-amber-400">{{ $money($amount) }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            {{-- Payment method --}}
            <section class="px-6 py-8 sm:px-10">
                <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/60 p-5 dark:border-white/10 dark:bg-white/5">
                    <h3 class="mb-4 text-xs font-semibold tracking-wider text-gray-500 uppercase dark:text-gray-400">{{ __('Payment Method') }}</h3>

                    <div class="grid gap-4 text-sm sm:grid-cols-4">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('Payment Gateway') }}</p>
                            <p class="mt-1 font-medium text-gray-950 dark:text-white">{{ ucfirst((string) $payment->gateway) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('Card Type') }}</p>
                            <p class="mt-1 font-medium text-gray-950 dark:text-white">{{ $card['company'] ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('Card Number') }}</p>
                            <p class="mt-1 font-mono font-medium text-gray-950 dark:text-white" dir="ltr">{{ $card['number'] ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">{{ __('Card Holder') }}</p>
                            <p class="mt-1 font-medium text-gray-950 dark:text-white">{{ $card['holder'] ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Footer --}}
            <footer class="border-t border-gray-100 px-6 py-6 text-center text-xs text-gray-500 sm:px-10 dark:border-white/10 dark:text-gray-400">
                {{ __('Thank you for subscribing to :app.', ['app' => __(config('app.name'))]) }}
            </footer>
        </article>
    </div>
</x-filament-panels::page>
