<?php

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Resources\Faqs\FaqResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFaq extends CreateRecord
{
    protected static string $resource = FaqResource::class;

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $lastOrder = static::getModel()::max('order');
        $data['order'] = $lastOrder ? $lastOrder + 1 : 1;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return FaqResource::getUrl('index');
    }
}
