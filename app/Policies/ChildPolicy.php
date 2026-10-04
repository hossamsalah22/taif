<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Child;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChildPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Child');
    }

    public function view(AuthUser $authUser, Child $child): bool
    {
        return $authUser->can('View:Child');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Child');
    }

    public function update(AuthUser $authUser, Child $child): bool
    {
        return $authUser->can('Update:Child');
    }

    public function delete(AuthUser $authUser, Child $child): bool
    {
        return $authUser->can('Delete:Child');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Child');
    }

    public function restore(AuthUser $authUser, Child $child): bool
    {
        return $authUser->can('Restore:Child');
    }

    public function forceDelete(AuthUser $authUser, Child $child): bool
    {
        return $authUser->can('ForceDelete:Child');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Child');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Child');
    }

    public function replicate(AuthUser $authUser, Child $child): bool
    {
        return $authUser->can('Replicate:Child');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Child');
    }

}