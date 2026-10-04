<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LearningLesson;
use Illuminate\Auth\Access\HandlesAuthorization;

class LearningLessonPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LearningLesson');
    }

    public function view(AuthUser $authUser, LearningLesson $learningLesson): bool
    {
        return $authUser->can('View:LearningLesson');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LearningLesson');
    }

    public function update(AuthUser $authUser, LearningLesson $learningLesson): bool
    {
        return $authUser->can('Update:LearningLesson');
    }

    public function delete(AuthUser $authUser, LearningLesson $learningLesson): bool
    {
        return $authUser->can('Delete:LearningLesson');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LearningLesson');
    }

    public function restore(AuthUser $authUser, LearningLesson $learningLesson): bool
    {
        return $authUser->can('Restore:LearningLesson');
    }

    public function forceDelete(AuthUser $authUser, LearningLesson $learningLesson): bool
    {
        return $authUser->can('ForceDelete:LearningLesson');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LearningLesson');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LearningLesson');
    }

    public function replicate(AuthUser $authUser, LearningLesson $learningLesson): bool
    {
        return $authUser->can('Replicate:LearningLesson');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LearningLesson');
    }

}