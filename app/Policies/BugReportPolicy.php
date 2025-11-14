<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BugReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class BugReportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BugReport');
    }

    public function view(AuthUser $authUser, BugReport $bugReport): bool
    {
        return $authUser->can('View:BugReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BugReport');
    }

    public function update(AuthUser $authUser, BugReport $bugReport): bool
    {
        return $authUser->can('Update:BugReport');
    }

    public function delete(AuthUser $authUser, BugReport $bugReport): bool
    {
        return $authUser->can('Delete:BugReport');
    }

    public function restore(AuthUser $authUser, BugReport $bugReport): bool
    {
        return $authUser->can('Restore:BugReport');
    }

    public function forceDelete(AuthUser $authUser, BugReport $bugReport): bool
    {
        return $authUser->can('ForceDelete:BugReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BugReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BugReport');
    }

    public function replicate(AuthUser $authUser, BugReport $bugReport): bool
    {
        return $authUser->can('Replicate:BugReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BugReport');
    }

}