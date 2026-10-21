<?php

namespace App\Filament\Resources\AssessmentSubmissions\Pages;

use App\Enums\AssessmentSubmissionStatusEnum;
use App\Filament\Resources\AssessmentSubmissions\AssessmentSubmissionResource;
use App\Models\AssessmentSubmission;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAssessmentSubmissions extends ListRecords
{
    protected static string $resource = AssessmentSubmissionResource::class;

    public function getSubheading(): ?string
    {
        return __('Evaluate each submission to write the report, then publish it so the parent can see it in the app.');
    }

    /**
     * One tab per review step, in the order the work happens. "Waiting for review" opens first.
     */
    public function getTabs(): array
    {
        $counts = AssessmentSubmission::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $count = fn (array $statuses): int => collect($statuses)->sum(fn (AssessmentSubmissionStatusEnum $status): int => (int) ($counts[$status->value] ?? 0));

        return [
            'waiting' => Tab::make(__('Waiting for review'))
                ->icon('heroicon-m-clock')
                ->badge($count(AssessmentSubmissionStatusEnum::waitingForReview()))
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('status', AssessmentSubmissionStatusEnum::waitingForReview())),
            'reviewed' => Tab::make(__('Reviewed, not published'))
                ->icon('heroicon-m-pencil-square')
                ->badge($count([AssessmentSubmissionStatusEnum::REVIEWED]))
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', AssessmentSubmissionStatusEnum::REVIEWED)),
            'published' => Tab::make(__('Published to parent'))
                ->icon('heroicon-m-check-circle')
                ->badge($count([AssessmentSubmissionStatusEnum::PUBLISHED]))
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', AssessmentSubmissionStatusEnum::PUBLISHED)),
            'all' => Tab::make(__('All')),
        ];
    }
}
