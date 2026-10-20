<?php

namespace App\Filament\Resources\LearningPlans\Pages;

use App\Filament\Resources\LearningPlans\LearningPlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLearningPlans extends ListRecords
{
    protected static string $resource = LearningPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return __('A plan is what a child follows. Add goals to a plan, then use "Assign to Children" to give it to children with the same severity level.');
    }
}
