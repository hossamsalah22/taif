<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LearningGoal;
use Illuminate\Auth\Access\HandlesAuthorization;

class LearningGoalPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LearningGoal');
    }

    public function view(AuthUser $authUser, LearningGoal $learningGoal): bool
    {
        return $authUser->can('View:LearningGoal');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LearningGoal');
    }

    public function update(AuthUser $authUser, LearningGoal $learningGoal): bool
    {
        return $authUser->can('Update:LearningGoal');
    }

    public function delete(AuthUser $authUser, LearningGoal $learningGoal): bool
    {
        return $authUser->can('Delete:LearningGoal');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LearningGoal');
    }

    public function restore(AuthUser $authUser, LearningGoal $learningGoal): bool
    {
        return $authUser->can('Restore:LearningGoal');
    }

    public function forceDelete(AuthUser $authUser, LearningGoal $learningGoal): bool
    {
        return $authUser->can('ForceDelete:LearningGoal');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LearningGoal');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LearningGoal');
    }

    public function replicate(AuthUser $authUser, LearningGoal $learningGoal): bool
    {
        return $authUser->can('Replicate:LearningGoal');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LearningGoal');
    }

}