<?php

namespace App\Filament\Resources\Sliders\Pages;

use App\Filament\Resources\Sliders\SliderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSlider extends CreateRecord
{
    protected static string $resource = SliderResource::class;

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $lastOrder = static::getModel()::max('order');
        $data['order'] = $lastOrder ? $lastOrder + 1 : 1;

        return $data;
    }
}
