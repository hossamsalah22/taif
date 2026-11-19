<?php

namespace App\Filament\Resources\Concerns;

use App\Filament\Resources\LearningGoals\LearningGoalResource;
use App\Filament\Resources\LearningLessons\LearningLessonResource;
use App\Filament\Resources\LearningPlans\LearningPlanResource;
use App\Filament\Resources\Rewards\RewardResource;
use Filament\Resources\Resource;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

/**
 * Plans, goals, lessons and rewards are one "Learning content" workspace: their list pages share
 * one title and a tab bar to move between them, instead of four separate sidebar entries.
 */
trait HasLearningContentTabs
{
    /**
     * @return array<int, class-string<resource>>
     */
    public static function learningContentResources(): array
    {
        return [
            LearningPlanResource::class,
            LearningGoalResource::class,
            LearningLessonResource::class,
            RewardResource::class,
        ];
    }

    public function getTitle(): string
    {
        return __('Learning content');
    }

    public function content(Schema $schema): Schema
    {
        $content = parent::content($schema);

        return $content->components([
            View::make('filament.resources.learning-content-tabs')
                ->viewData(['tabs' => $this->learningContentTabs()]),
            ...$content->getComponents(withHidden: true),
        ]);
    }

    /**
     * @return array<int, array{label: string, icon: mixed, url: string, count: int, active: bool}>
     */
    protected function learningContentTabs(): array
    {
        return collect(static::learningContentResources())
            ->filter(fn (string $resource): bool => $resource::canViewAny())
            ->map(fn (string $resource): array => [
                'label' => $resource::getPluralModelLabel(),
                'icon' => $resource::getNavigationIcon(),
                'url' => $resource::getUrl('index'),
                'count' => $resource::getModel()::query()->count(),
                'active' => $resource === static::getResource(),
            ])
            ->values()
            ->all();
    }
}
