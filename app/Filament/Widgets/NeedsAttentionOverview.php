<?php

namespace App\Filament\Widgets;

use App\Enums\AssessmentSubmissionStatusEnum;
use App\Enums\SupportTicketStatusEnum;
use App\Filament\Resources\AssessmentSubmissions\AssessmentSubmissionResource;
use App\Filament\Resources\Children\ChildResource;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\Subscriptions\Tables\SubscriptionsTable;
use App\Filament\Resources\SupportTickets\SupportTicketResource;
use App\Models\AssessmentSubmission;
use App\Models\Child;
use App\Models\Subscription;
use App\Models\SupportTicket;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

/**
 * Work waiting for the team right now. Each card opens the matching list already filtered.
 * Not affected by the dashboard dates.
 */
class NeedsAttentionOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    public function getHeading(): ?string
    {
        return __('Needs attention');
    }

    public function getDescription(): ?string
    {
        return __('Work waiting for the team right now. Click a card to open the list. Not affected by the dates.');
    }

    protected function getColumns(): int|array
    {
        return ['default' => 1, 'sm' => 2, 'xl' => 4];
    }

    protected function getStats(): array
    {
        return array_values(array_filter([
            $this->submissionsWaitingForReview(),
            $this->openTickets(),
            $this->subscriptionsEndingSoon(),
            $this->childrenWithoutPlan(),
        ]));
    }

    private function submissionsWaitingForReview(): ?Stat
    {
        if (! AssessmentSubmissionResource::canViewAny()) {
            return null;
        }

        $count = AssessmentSubmission::whereIn('status', AssessmentSubmissionStatusEnum::waitingForReview())->count();

        return Stat::make(__('Assessments to review'), Number::format($count))
            ->description($count > 0 ? __('Evaluate them and publish the report to the parent') : __('All caught up'))
            ->descriptionIcon($count > 0 ? 'heroicon-m-clipboard-document-check' : 'heroicon-m-check-circle')
            ->color($count > 0 ? 'warning' : 'success')
            ->url(AssessmentSubmissionResource::getUrl('index', ['tab' => 'waiting']));
    }

    private function openTickets(): ?Stat
    {
        if (! SupportTicketResource::canViewAny()) {
            return null;
        }

        $count = SupportTicket::where('status', SupportTicketStatusEnum::OPEN)->count();
        $unassigned = SupportTicket::where('status', '!=', SupportTicketStatusEnum::CLOSED)->whereNull('assigned_admin_id')->count();

        return Stat::make(__('Open support tickets'), Number::format($count))
            ->description($unassigned > 0
                ? trans_choice(':count ticket is not assigned to anyone|:count tickets are not assigned to anyone', $unassigned, ['count' => $unassigned])
                : __('Every ticket has a specialist'))
            ->descriptionIcon($unassigned > 0 ? 'heroicon-m-user-minus' : 'heroicon-m-check-circle')
            ->color($count > 0 ? 'danger' : 'success')
            ->url(SupportTicketResource::getUrl('index', ['tab' => 'open']));
    }

    private function subscriptionsEndingSoon(): ?Stat
    {
        if (! SubscriptionResource::canViewAny()) {
            return null;
        }

        $days = SubscriptionsTable::ENDING_SOON_DAYS;
        $count = Subscription::query()->expiringWithin($days)->count();

        return Stat::make(__('Subscriptions ending soon'), Number::format($count))
            ->description(__('Access ends within :days days; a good time to remind the parent', ['days' => $days]))
            ->descriptionIcon('heroicon-m-clock')
            ->color($count > 0 ? 'warning' : 'success')
            ->url(SubscriptionResource::getUrl('index', [
                'filters' => ['ending_soon' => ['isActive' => true]],
            ]));
    }

    private function childrenWithoutPlan(): ?Stat
    {
        if (! ChildResource::canViewAny()) {
            return null;
        }

        $count = Child::query()->withoutEagerLoads()->whereDoesntHave('childLearningPlans')->count();

        return Stat::make(__('Children without a learning plan'), Number::format($count))
            ->description($count > 0 ? __('Assign a plan so they can start their sessions') : __('Every child has a plan'))
            ->descriptionIcon($count > 0 ? 'heroicon-m-academic-cap' : 'heroicon-m-check-circle')
            ->color($count > 0 ? 'info' : 'success')
            ->url(ChildResource::getUrl('index', [
                'filters' => ['has_learning_plan' => ['value' => '0']],
            ]));
    }
}
