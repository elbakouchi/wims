<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_validation()
    {
        $this->actingAs($this->createUser());
        $response = $this->post('/contacts', []);
        $response->assertSessionHasErrors(['name', 'phone']);
    }

    public function test_guest_can_not_create_contact()
    {
        $this->post('/contacts', [
            'name'  => 'Test Name',
            'email' => 'test@example.com',
        ])->assertRedirect(route('login'));
    }

    public function test_super_admin_can_manage_contacts()
    {
        $this->actingAs($this->createUser());

        // Can create
        $this->post('/contacts', [
            'name'  => 'Test Name',
            'email' => 'test@example.com',
        ])->assertSessionHas('message', __choice('action_text', ['record' => 'Contact', 'action' => 'created']));

        $contact = Contact::first();
        $this->assertEquals('Test Name', $contact->name);
        $this->assertEquals('test@example.com', $contact->email);

        // Can update
        $this->put('/contacts/' . $contact->id, [
            'name'  => 'Update Name',
            'email' => 'update@example.com',
        ])->assertSessionHas('message');

        $contact->refresh();
        $this->assertEquals('Update Name', $contact->name);
        $this->assertEquals('update@example.com', $contact->email);

        // Can delete
        $this->delete('/contacts/' . $contact->id)->assertSessionHas('message');
        $this->assertSoftDeleted($contact);
        $this->assertTrue($contact->refresh()->trashed());

        // can restore
        $this->put('/contacts/' . $contact->id . '/restore')->assertSessionHas('message');
        $this->assertFalse($contact->refresh()->trashed());

        // Can delete permanently
        $this->delete('/contacts/' . $contact->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($contact);
    }

    public function test_user_with_no_permissions_can_not_create_contact()
    {
        $this->actingAs($this->createUser('Admin'));
        $this->post('/contacts', [
            'name'  => 'Test Name',
            'email' => 'test@example.com',
        ])->assertSessionHas('error');
    }

    public function test_user_with_permissions_can_manage_contacts()
    {
        $admin = $this->createUserWithPermissions('Admin', ['read-contacts', 'create-contacts']);

        // can create
        $this->actingAs($admin)->post('/contacts', [
            'name'  => 'Test Name 1',
            'email' => 'test1@example.com',
        ])->assertSessionHas('message');

        $contact = Contact::first();
        $this->assertEquals('Test Name 1', $contact->name);
        $this->assertEquals('test1@example.com', $contact->email);

        // Can't update or delete
        $this->put('/contacts/' . $contact->id, [
            'name'  => 'Update Name 1',
            'email' => 'update1@example.com',
        ])->assertSessionHas('error');
        $this->delete('/contacts/' . $contact->id)->assertSessionHas('error');
        $this->assertFalse($contact->refresh()->trashed());

        // New admin with update and delete permissions
        $admin2 = $this->createUserWithPermissions('Admin', ['read-contacts', 'create-contacts', 'update-contacts', 'delete-contacts'], $admin->account);

        // Can update
        $this->actingAs($admin2)->put('/contacts/' . $contact->id, [
            'name'  => 'Update Name 2',
            'email' => 'update2@example.com',
        ])->assertSessionHas('message');

        $contact->refresh();
        $this->assertEquals('Update Name 2', $contact->name);
        $this->assertEquals('update2@example.com', $contact->email);

        // can delete
        $this->actingAs($admin2)->delete('/contacts/' . $contact->id)->assertSessionHas('message');
        $this->assertSoftDeleted($contact);

        $this->actingAs($admin2)->delete('/contacts/' . $contact->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($contact);
    }
}
