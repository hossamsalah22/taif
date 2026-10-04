<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LearningPlan;
use Illuminate\Auth\Access\HandlesAuthorization;

class LearningPlanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LearningPlan');
    }

    public function view(AuthUser $authUser, LearningPlan $learningPlan): bool
    {
        return $authUser->can('View:LearningPlan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LearningPlan');
    }

    public function update(AuthUser $authUser, LearningPlan $learningPlan): bool
    {
        return $authUser->can('Update:LearningPlan');
    }

    public function delete(AuthUser $authUser, LearningPlan $learningPlan): bool
    {
        return $authUser->can('Delete:LearningPlan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:LearningPlan');
    }

    public function restore(AuthUser $authUser, LearningPlan $learningPlan): bool
    {
        return $authUser->can('Restore:LearningPlan');
    }

    public function forceDelete(AuthUser $authUser, LearningPlan $learningPlan): bool
    {
        return $authUser->can('ForceDelete:LearningPlan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LearningPlan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LearningPlan');
    }

    public function replicate(AuthUser $authUser, LearningPlan $learningPlan): bool
    {
        return $authUser->can('Replicate:LearningPlan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LearningPlan');
    }

}