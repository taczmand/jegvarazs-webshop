<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;

class PermissionBootstrapService
{
    public function syncAllPermissionsForConfiguredUsers(): array
    {
        $emails = (array) config('permission_bootstrap.users.emails', []);
        $emails = array_values(array_filter(array_map('strval', $emails)));

        $allPermissionNames = Permission::query()
            ->where('guard_name', 'admin')
            ->pluck('name')
            ->all();

        $result = [
            'configured_emails' => $emails,
            'permissions_count' => count($allPermissionNames),
            'users' => [],
            'missing_users' => [],
        ];

        if (count($emails) === 0) {
            return $result;
        }

        $users = User::query()->whereIn('email', $emails)->get();

        $foundEmails = $users->pluck('email')->all();
        $result['missing_users'] = array_values(array_diff($emails, $foundEmails));

        foreach ($users as $user) {
            $user->syncPermissions($allPermissionNames);

            $result['users'][] = [
                'id' => $user->id,
                'email' => $user->email,
                'synced_permissions_count' => count($allPermissionNames),
            ];
        }

        Artisan::call('permission:cache-reset');

        return $result;
    }
}
