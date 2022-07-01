<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use App\Models\Account;
use App\Models\Setting;
use App\Models\Permission;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function setup(): void
    {
        parent::setup();
        $this->account = Account::factory()->create();
        session(['account_id' => $this->account->id]);
        Setting::updateOrCreate(['tec_key' => 'over_selling', 'account_id' => $this->account->id], ['tec_value' => '1']);
    }

    protected function createUser(string $role = 'Super Admin', $account = null)
    {
        $account = $account ?: Account::factory()->create();
        $user    = User::factory()->create(['account_id' => $account->id, 'edit_all' => 1, 'view_all' => 1]);
        $role    = Role::factory()->create(['name' => $role, 'account_id' => $account->id, 'guard_name' => 'web']);
        $user->assignRole($role);
        return $user;
    }

    protected function createUserWithPermissions(string $role, array $permissions, $account = null)
    {
        $user_permissions = [];
        foreach ($permissions as $permission) {
            $user_permissions[] = Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        $account = $account ?: Account::factory()->create();
        $user    = User::factory()->create(['account_id' => $account->id, 'edit_all' => 1, 'view_all' => 1]);
        $form    = Role::factory()->make(['name' => $role, 'account_id' => $account->id, 'guard_name' => 'web'])->toArray();
        $role    = Role::updateOrCreate(['name' => $form['name']], $form);
        $role->givePermissionTo($user_permissions);
        $user->assignRole($role);
        return $user;
    }
}
