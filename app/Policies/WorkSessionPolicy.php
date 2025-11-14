<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WorkSession;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkSessionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WorkSession');
    }

    public function view(AuthUser $authUser, WorkSession $workSession): bool
    {
        return $authUser->can('View:WorkSession');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WorkSession');
    }

    public function update(AuthUser $authUser, WorkSession $workSession): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }
        return $authUser->can('Update:WorkSession') && $authUser->id === $workSession->user_id;
    }

    public function delete(AuthUser $authUser, WorkSession $workSession): bool
    {
        if ($authUser->hasRole('super_admin')) {
            return true;
        }
        return $authUser->can('Delete:WorkSession') && $authUser->id === $workSession->user_id;
    }

    public function restore(AuthUser $authUser, WorkSession $workSession): bool
    {
        return $authUser->can('Restore:WorkSession');
    }

    public function forceDelete(AuthUser $authUser, WorkSession $workSession): bool
    {
        return $authUser->can('ForceDelete:WorkSession');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WorkSession');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WorkSession');
    }

    public function replicate(AuthUser $authUser, WorkSession $workSession): bool
    {
        return $authUser->can('Replicate:WorkSession');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WorkSession');
    }

}
