<?php

namespace App\Filament\Resources\Sliders\Pages;

use App\Filament\Resources\Sliders\SliderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSlider extends CreateRecord
{
    protected static string $resource = SliderResource::class;

    /**
     * A slide left at order 0 goes after the last one; an order the admin picked is kept.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['sort_order'] ?? null) || (int) $data['sort_order'] === 0) {
            $data['sort_order'] = (int) static::getModel()::max('sort_order') + 1;
        }

        return $data;
    }
}
