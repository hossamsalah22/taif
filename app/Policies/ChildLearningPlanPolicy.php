<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ChildLearningPlan;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChildLearningPlanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ChildLearningPlan');
    }

    public function view(AuthUser $authUser, ChildLearningPlan $childLearningPlan): bool
    {
        return $authUser->can('View:ChildLearningPlan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ChildLearningPlan');
    }

    public function update(AuthUser $authUser, ChildLearningPlan $childLearningPlan): bool
    {
        return $authUser->can('Update:ChildLearningPlan');
    }

    public function delete(AuthUser $authUser, ChildLearningPlan $childLearningPlan): bool
    {
        return $authUser->can('Delete:ChildLearningPlan');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ChildLearningPlan');
    }

    public function restore(AuthUser $authUser, ChildLearningPlan $childLearningPlan): bool
    {
        return $authUser->can('Restore:ChildLearningPlan');
    }

    public function forceDelete(AuthUser $authUser, ChildLearningPlan $childLearningPlan): bool
    {
        return $authUser->can('ForceDelete:ChildLearningPlan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ChildLearningPlan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ChildLearningPlan');
    }

    public function replicate(AuthUser $authUser, ChildLearningPlan $childLearningPlan): bool
    {
        return $authUser->can('Replicate:ChildLearningPlan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ChildLearningPlan');
    }

}