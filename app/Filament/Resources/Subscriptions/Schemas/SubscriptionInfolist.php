<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use App\Enums\PaymentStatusEnum;
use App\Enums\SubscriptionStatusEnum;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Models\Subscription;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Full details of one subscription: the parent, the package and dates, and — for paid ones —
 * the Moyasar payment with its invoice and the card it was paid with.
 */
class SubscriptionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Parent'))
                    ->schema([
                        TextEntry::make('parent.name')
                            ->label(__('Name'))
                            ->placeholder('—'),
                        TextEntry::make('parent.phone')
                            ->label(__('Phone'))
                            ->formatStateUsing(fn (?string $state): ?string => $state ? ltrim($state, '+') : null)
                            ->placeholder('—'),
                        TextEntry::make('parent.email')
                            ->label(__('Email'))
                            ->placeholder('—'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make(__('Subscription'))
                    ->schema([
                        TextEntry::make('subscriptionPackage.name')
                            ->label(__('Package Name'))
                            ->placeholder(__('Free access')),
                        TextEntry::make('status')
                            ->label(__('Status'))
                            ->formatStateUsing(fn (SubscriptionStatusEnum $state): string => SubscriptionStatusEnum::label($state))
                            ->color(fn (SubscriptionStatusEnum $state): string => SubscriptionStatusEnum::color($state))
                            ->badge(),
                        TextEntry::make('amount_paid')
                            ->label(__('Amount Paid'))
                            ->money('SAR'),
                        IconEntry::make('is_free')
                            ->label(__('Free'))
                            ->boolean(),
                        TextEntry::make('start_date')
                            ->label(__('Subscription Date'))
                            ->date(),
                        TextEntry::make('expiry_date')
                            ->label(__('Expiry Date'))
                            ->date()
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label(__('Created At'))
                            ->dateTime(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make(__('Payment'))
                    ->visible(fn (Subscription $record): bool => $record->payment !== null)
                    ->schema([
                        TextEntry::make('payment.status')
                            ->label(__('Payment Status'))
                            ->formatStateUsing(fn (PaymentStatusEnum $state): string => PaymentStatusEnum::label($state))
                            ->color(fn (PaymentStatusEnum $state): string => PaymentStatusEnum::color($state))
                            ->badge(),
                        TextEntry::make('payment_amount')
                            ->label(__('Amount'))
                            ->state(fn (Subscription $record): ?float => $record->payment?->amountInMainUnit())
                            ->money(fn (Subscription $record): string => $record->payment?->currency ?? 'SAR'),
                        TextEntry::make('payment_fee')
                            ->label(__('Gateway Fee'))
                            ->state(fn (Subscription $record): ?float => self::attempt($record, 'fee') !== null ? round(self::attempt($record, 'fee') / 100, 2) : null)
                            ->money(fn (Subscription $record): string => $record->payment?->currency ?? 'SAR')
                            ->placeholder('—'),
                        TextEntry::make('payment.paid_at')
                            ->label(__('Paid At'))
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('payment.gateway')
                            ->label(__('Payment Gateway'))
                            ->formatStateUsing(fn (?string $state): string => ucfirst((string) $state)),
                        TextEntry::make('payment.gateway_invoice_id')
                            ->label(__('Invoice ID'))
                            ->copyable()
                            ->placeholder('—'),
                        TextEntry::make('payment.gateway_payment_id')
                            ->label(__('Transaction ID'))
                            ->copyable()
                            ->placeholder('—'),
                        TextEntry::make('payment.payment_url')
                            ->label(__('Invoice'))
                            ->formatStateUsing(fn (): string => __('View invoice'))
                            ->url(fn (Subscription $record): ?string => $record->payment ? SubscriptionResource::getUrl('invoice', ['record' => $record]) : null)
                            ->icon(Heroicon::OutlinedDocumentText)
                            ->color('primary')
                            ->placeholder('—'),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make(__('Payment Method'))
                    ->visible(fn (Subscription $record): bool => self::attempt($record, 'source') !== null)
                    ->schema([
                        TextEntry::make('card_holder')
                            ->label(__('Card Holder'))
                            ->state(fn (Subscription $record): ?string => self::source($record, 'name'))
                            ->placeholder('—'),
                        TextEntry::make('card_number')
                            ->label(__('Card Number'))
                            ->state(fn (Subscription $record): ?string => self::source($record, 'number'))
                            ->placeholder('—'),
                        TextEntry::make('card_company')
                            ->label(__('Card Type'))
                            ->state(fn (Subscription $record): ?string => ($company = self::source($record, 'company')) ? strtoupper($company) : null)
                            ->badge()
                            ->placeholder('—'),
                        TextEntry::make('card_issuer')
                            ->label(__('Issuer'))
                            ->state(fn (Subscription $record): ?string => self::source($record, 'issuer_name'))
                            ->placeholder('—'),
                        TextEntry::make('gateway_message')
                            ->label(__('Gateway Message'))
                            ->state(fn (Subscription $record): ?string => self::source($record, 'message'))
                            ->placeholder('—'),
                        TextEntry::make('reference_number')
                            ->label(__('Reference Number'))
                            ->state(fn (Subscription $record): ?string => self::source($record, 'reference_number'))
                            ->copyable()
                            ->placeholder('—'),
                        TextEntry::make('authorization_code')
                            ->label(__('Authorization Code'))
                            ->state(fn (Subscription $record): ?string => self::source($record, 'authorization_code'))
                            ->placeholder('—'),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ]);
    }

    private static function attempt(Subscription $record, string $key): mixed
    {
        return $record->payment?->paidAttempt()[$key] ?? null;
    }

    private static function source(Subscription $record, string $key): ?string
    {
        $value = self::attempt($record, 'source')[$key] ?? null;

        return filled($value) ? (string) $value : null;
    }
}
