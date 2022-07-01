<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setup();
        $this->account = Account::factory()->create();
        $this->roles   = Role::factory(4)->create(['account_id' => $this->account->id]);
    }

    public function test_guest_can_not_create_user()
    {
        $this->post('/users', [])->assertRedirect(route('login'));
    }

    public function test_super_admin_can_manage_users()
    {
        $this->actingAs($loggedin = $this->createUser());

        // Can create
        $form             = User::factory()->make(['account_id' => $this->account->id])->toArray();
        $form['password'] = '12345678';
        $form['roles'][]  = $this->roles->random()->first()->id;
        $this->post('/users', $form)
            ->assertSessionHas('message', __choice('action_text', ['record' => 'User', 'action' => 'created']));

        $user = User::where('username', $form['username'])->first();
        $this->assertEquals($form['name'], $user->name);
        $this->assertEquals($form['username'], $user->username);
        $this->assertEquals($form['email'], $user->email);
        $this->assertTrue(Hash::check('12345678', $user->password));

        // Can update
        unset($form['password']);
        $form['name'] = 'User 1';
        $this->put('/users/' . $user->id, $form)->assertSessionHas('message');

        $user->refresh();
        $this->assertEquals('User 1', $user->name);

        // Can delete
        $this->delete('/users/' . $user->id)->assertSessionHas('message');
        $this->assertSoftDeleted($user);
        $this->assertTrue($user->refresh()->trashed());

        // can restore
        $this->put('/users/' . $user->id . '/restore')->assertSessionHas('message');
        $this->assertFalse($user->refresh()->trashed());

        // Can delete permanently
        $this->delete('/users/' . $user->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($user);

        // Can't delete himself
        $this->delete('/users/' . $loggedin->id)->assertSessionHas('error', __('You should not delete your own account.'));
    }

    public function test_user_validation()
    {
        $this->actingAs($this->createUser());
        $response = $this->post('/users', []);
        $response->assertSessionHasErrors(['name', 'username', 'email', 'password']);
    }

    public function test_user_with_no_permissions_can_not_create_user()
    {
        $this->actingAs($this->createUser('Admin'));
        $form             = User::factory()->make(['account_id' => $this->account->id])->toArray();
        $form['password'] = '12345678';
        $form['roles'][]  = $this->roles->random()->first()->id;
        $this->post('/users', $form)->assertSessionHas('error');
    }

    public function test_user_with_permissions_can_manage_users()
    {
        $admin = $this->createUserWithPermissions('Admin', ['read-users', 'create-users']);

        // can create
        $form             = User::factory()->make(['account_id' => $this->account->id])->toArray();
        $form['password'] = '12345678';
        $form['roles'][]  = $this->roles->random()->first()->id;
        $this->actingAs($admin)->post('/users', $form)->assertSessionHas('message');

        $user = User::where('username', $form['username'])->first();
        $this->assertEquals($form['name'], $user->name);
        $this->assertEquals($form['username'], $user->username);
        $this->assertEquals($form['email'], $user->email);
        $this->assertTrue(Hash::check('12345678', $user->password));

        // Can't update or delete
        unset($form['password']);
        $form['name'] = 'User 2';
        $this->put('/users/' . $user->id, $form)->assertSessionHas('error');
        $this->delete('/users/' . $user->id)->assertSessionHas('error');
        $this->assertFalse($user->refresh()->trashed());

        // New admin with update and delete permissions
        $admin2 = $this->createUserWithPermissions('Admin', ['read-users', 'create-users', 'update-users', 'delete-users'], $admin->account);

        // Can update
        $this->actingAs($admin2)->put('/users/' . $user->id, $form)->assertSessionHas('message');
        // $this->actingAs($admin2)->put('/users/' . $user->id, ['name' => 'User 2'])->assertSessionHas('message');

        $user->refresh();
        $this->assertEquals('User 2', $user->name);

        // can delete
        $this->actingAs($admin2)->delete('/users/' . $user->id)->assertSessionHas('message');
        $this->assertSoftDeleted($user);

        $this->actingAs($admin2)->delete('/users/' . $user->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($user);

        // Can't delete own and super user
        $this->actingAs($admin)->delete('/users/' . $admin->id)->assertSessionHas('error', __('You should not delete your own account.'));
        $this->actingAs($admin2)->delete('/users/' . $admin2->id)->assertSessionHas('error', __('You should not delete your own account.'));
    }
}
