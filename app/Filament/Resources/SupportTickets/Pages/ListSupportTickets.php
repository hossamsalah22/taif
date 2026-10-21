<?php

namespace App\Filament\Resources\SupportTickets\Pages;

use App\Enums\SupportTicketStatusEnum;
use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Models\SupportTicket;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSupportTickets extends ListRecords
{
    protected static string $resource = SupportTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    /**
     * "Not closed" opens first so finished tickets stay out of the way; then one tab per status.
     */
    public function getTabs(): array
    {
        $counts = SupportTicket::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $tabs = [
            'active' => Tab::make(__('Not closed'))
                ->badge($counts->except(SupportTicketStatusEnum::CLOSED->value)->sum())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', '!=', SupportTicketStatusEnum::CLOSED)),
        ];

        foreach (SupportTicketStatusEnum::cases() as $status) {
            $tabs[str($status->name)->lower()->toString()] = Tab::make($status->getLabel())
                ->badge((int) ($counts[$status->value] ?? 0))
                ->badgeColor($status->getColor())
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', $status));
        }

        $tabs['all'] = Tab::make(__('All'));

        return $tabs;
    }
}
