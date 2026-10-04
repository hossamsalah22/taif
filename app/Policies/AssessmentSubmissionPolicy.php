<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\AssessmentSubmission;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssessmentSubmissionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AssessmentSubmission');
    }

    public function view(AuthUser $authUser, AssessmentSubmission $assessmentSubmission): bool
    {
        return $authUser->can('View:AssessmentSubmission');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AssessmentSubmission');
    }

    public function update(AuthUser $authUser, AssessmentSubmission $assessmentSubmission): bool
    {
        return $authUser->can('Update:AssessmentSubmission');
    }

    public function delete(AuthUser $authUser, AssessmentSubmission $assessmentSubmission): bool
    {
        return $authUser->can('Delete:AssessmentSubmission');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AssessmentSubmission');
    }

    public function restore(AuthUser $authUser, AssessmentSubmission $assessmentSubmission): bool
    {
        return $authUser->can('Restore:AssessmentSubmission');
    }

    public function forceDelete(AuthUser $authUser, AssessmentSubmission $assessmentSubmission): bool
    {
        return $authUser->can('ForceDelete:AssessmentSubmission');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AssessmentSubmission');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AssessmentSubmission');
    }

    public function replicate(AuthUser $authUser, AssessmentSubmission $assessmentSubmission): bool
    {
        return $authUser->can('Replicate:AssessmentSubmission');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AssessmentSubmission');
    }

}