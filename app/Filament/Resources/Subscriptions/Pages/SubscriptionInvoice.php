<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Models\Payment;
use App\Models\Subscription;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

/**
 * A printable invoice for a paid subscription, built from the stored Moyasar payment.
 *
 * @property Subscription $record
 */
class SubscriptionInvoice extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SubscriptionResource::class;

    protected string $view = 'filament.resources.subscriptions.pages.subscription-invoice';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->record->loadMissing(['parent', 'subscriptionPackage', 'payment.subscriptionPackage']);

        abort_if($this->record->payment === null, 404);
    }

    public function getTitle(): string|Htmlable
    {
        return __('Invoice').' '.self::invoiceNumber($this->record->payment);
    }

    public function getBreadcrumb(): string
    {
        return __('Invoice');
    }

    /**
     * A short, human friendly invoice number (the gateway id is a long uuid).
     */
    public static function invoiceNumber(Payment $payment): string
    {
        return 'INV-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('print')
                ->label(__('Print'))
                ->icon(Heroicon::OutlinedPrinter)
                ->alpineClickHandler('window.print()'),
            /*
             * Moyasar's hosted invoice only works as a checkout link: once paid it just redirects
             * back to our "payment successful" page, so it is offered only while still unpaid.
             */
            Action::make('openGatewayInvoice')
                ->label(__('Open payment link'))
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (): ?string => $this->record->payment?->payment_url)
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->record->payment?->isPending() && filled($this->record->payment->payment_url)),
            Action::make('viewSubscription')
                ->label(__('View subscription'))
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->url(fn (): string => SubscriptionResource::getUrl('view', ['record' => $this->record])),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $payment = $this->record->payment;
        $source = $payment->paidAttempt()['source'] ?? [];

        return [
            'subscription' => $this->record,
            'payment' => $payment,
            'invoiceNumber' => self::invoiceNumber($payment),
            'packageName' => $payment->subscriptionPackage?->name ?? $this->record->subscriptionPackage?->name ?? __('Subscription'),
            'amount' => $payment->amountInMainUnit(),
            'currency' => $payment->currency ?: 'SAR',
            'card' => [
                'holder' => filled($source['name'] ?? null) ? (string) $source['name'] : null,
                'number' => filled($source['number'] ?? null) ? (string) $source['number'] : null,
                'company' => filled($source['company'] ?? null) ? strtoupper((string) $source['company']) : null,
                'reference' => filled($source['reference_number'] ?? null) ? (string) $source['reference_number'] : null,
            ],
        ];
    }
}
