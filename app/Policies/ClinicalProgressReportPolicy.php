<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ClinicalProgressReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClinicalProgressReportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ClinicalProgressReport');
    }

    public function view(AuthUser $authUser, ClinicalProgressReport $clinicalProgressReport): bool
    {
        return $authUser->can('View:ClinicalProgressReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ClinicalProgressReport');
    }

    public function update(AuthUser $authUser, ClinicalProgressReport $clinicalProgressReport): bool
    {
        return $authUser->can('Update:ClinicalProgressReport');
    }

    public function delete(AuthUser $authUser, ClinicalProgressReport $clinicalProgressReport): bool
    {
        return $authUser->can('Delete:ClinicalProgressReport');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ClinicalProgressReport');
    }

    public function restore(AuthUser $authUser, ClinicalProgressReport $clinicalProgressReport): bool
    {
        return $authUser->can('Restore:ClinicalProgressReport');
    }

    public function forceDelete(AuthUser $authUser, ClinicalProgressReport $clinicalProgressReport): bool
    {
        return $authUser->can('ForceDelete:ClinicalProgressReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ClinicalProgressReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ClinicalProgressReport');
    }

    public function replicate(AuthUser $authUser, ClinicalProgressReport $clinicalProgressReport): bool
    {
        return $authUser->can('Replicate:ClinicalProgressReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ClinicalProgressReport');
    }

}