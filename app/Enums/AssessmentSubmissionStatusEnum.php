<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Review workflow of a child's assessment: the app submits it, a specialist evaluates it and writes the report,
 * then the report is published to the parent.
 */
enum AssessmentSubmissionStatusEnum: string implements HasColor, HasLabel
{
    case PENDING_REVIEW = 'pending_review';
    case REVIEWED = 'reviewed_with_report';
    case PUBLISHED = 'published';

    /** Column default used before the review workflow existed; no report has been written for these either. */
    case COMPLETED = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING_REVIEW, self::COMPLETED => __('Waiting for review'),
            self::REVIEWED => __('Reviewed, not published'),
            self::PUBLISHED => __('Published to parent'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING_REVIEW, self::COMPLETED => 'warning',
            self::REVIEWED => 'info',
            self::PUBLISHED => 'success',
        };
    }

    /**
     * Statuses of submissions no specialist has evaluated yet.
     *
     * @return array<int, self>
     */
    public static function waitingForReview(): array
    {
        return [self::PENDING_REVIEW, self::COMPLETED];
    }
}
