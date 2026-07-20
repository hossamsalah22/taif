<?php

namespace App\Enums;

enum ExerciseTypeEnum: string
{
    case IMAGE_SELECTION = 'image_selection';
    case MATCHING = 'matching';
    case ORDERING = 'ordering';
    case DISTINGUISHING = 'distinguishing';
    case AUDIO_FLASHCARDS = 'audio_flashcards';
    case INSTRUCTIONAL_VIDEO = 'instructional_video';

    public static function label(self $status): string
    {
        return match ($status) {
            self::IMAGE_SELECTION => __('Image Selection'),
            self::MATCHING => __('Matching'),
            self::ORDERING => __('Ordering'),
            self::DISTINGUISHING => __('Distinguishing'),
            self::AUDIO_FLASHCARDS => __('Audio Flashcards'),
            self::INSTRUCTIONAL_VIDEO => __('Instructional Video'),
        };
    }

    public static function color(self $status): string
    {
        return match ($status) {
            self::IMAGE_SELECTION => 'primary',
            self::MATCHING => 'success',
            self::ORDERING => 'danger',
            self::DISTINGUISHING => 'warning',
            self::AUDIO_FLASHCARDS => 'info',
            self::INSTRUCTIONAL_VIDEO => 'secondary',
        };
    }

    public static function options(): array
    {
        return [
            self::IMAGE_SELECTION->value => __('Image Selection'),
            self::MATCHING->value => __('Matching'),
            self::ORDERING->value => __('Ordering'),
            self::DISTINGUISHING->value => __('Distinguishing'),
            self::AUDIO_FLASHCARDS->value => __('Audio Flashcards'),
            self::INSTRUCTIONAL_VIDEO->value => __('Instructional Video'),
        ];
    }
}
