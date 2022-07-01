<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Item;
use App\Models\Checkin;
use App\Models\Contact;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckinTest extends TestCase
{
    use HelperTrait;
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setup();

        $this->warehouse = Warehouse::factory()->create(['account_id' => $this->account->id]);
        $this->items     = Item::factory()->count(10)->create(['account_id' => $this->account->id]);
        $this->contacts  = Contact::factory()->count(10)->create(['account_id' => $this->account->id]);
    }

    public function test_checkin_validation()
    {
        $this->actingAs($this->createUser());
        $response = $this->post('/checkins', []);
        $response->assertSessionHasErrors(['date', 'items', 'contact_id', 'warehouse_id']);
    }

    public function test_checkin_with_no_permissions_can_not_create_checkin()
    {
        $this->actingAs($this->createUser('Admin'));
        $this->post('/checkins', $this->checkinform())->assertSessionHas('error');
    }

    public function test_guest_can_not_create_checkin()
    {
        $this->post('/checkins', [])->assertRedirect(route('login'));
    }

    public function test_super_admin_can_manage_checkins()
    {
        $this->actingAs($this->createUser('Super Admin', $this->account));

        // Can create
        $form = $this->checkinform();
        $this->post('/checkins', $form)->assertSessionHas('message');

        $checkin = Checkin::where('reference', $form['reference'])->first();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($checkin->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $checkin->details);

        // Can update
        $form = $this->checkinform();
        $this->put('/checkins/' . $checkin->id, $form)->assertSessionHas('message');

        $checkin->refresh();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($checkin->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $checkin->details);
        $this->assertEquals($form['reference'], $checkin->reference);

        // Can delete
        $this->delete('/checkins/' . $checkin->id)->assertSessionHas('message');
        $this->assertSoftDeleted($checkin);
        $this->assertTrue($checkin->refresh()->trashed());

        // can restore
        $this->put('/checkins/' . $checkin->id . '/restore')->assertSessionHas('message');
        $this->assertFalse($checkin->refresh()->trashed());

        // Can delete permanently
        $this->delete('/checkins/' . $checkin->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($checkin);
    }

    public function test_user_with_permissions_can_manage_checkins()
    {
        $admin = $this->createUserWithPermissions('Admin', ['read-checkins', 'create-checkins'], $this->account);

        // can create
        $form = $this->checkinform();
        $this->actingAs($admin)->post('/checkins', $form)->assertSessionHas('message');

        $checkin = Checkin::where('reference', $form['reference'])->first();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($checkin->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $checkin->details);

        // Can't update or delete
        $form = $this->checkinform();
        $this->put('/checkins/' . $checkin->id, $form)->assertSessionHas('error');
        $this->delete('/checkins/' . $checkin->id)->assertSessionHas('error');
        $this->assertFalse($checkin->refresh()->trashed());

        // New admin with update and delete permissions
        $admin2 = $this->createUserWithPermissions('Admin', ['read-checkins', 'create-checkins', 'update-checkins', 'delete-checkins'], $admin->account);

        // Can update
        $form = $this->checkinform();
        $this->actingAs($admin2)->put('/checkins/' . $checkin->id, $form)->assertSessionHas('message');

        $checkin->refresh();
        $this->assertEquals(Carbon::parse($form['date'])->format('Y-m-d'), Carbon::parse($checkin->date)->format('Y-m-d'));
        $this->assertEquals($form['details'], $checkin->details);
        $this->assertEquals($form['reference'], $checkin->reference);

        // can delete
        $this->actingAs($admin2)->delete('/checkins/' . $checkin->id)->assertSessionHas('message');
        $this->assertSoftDeleted($checkin);

        $this->actingAs($admin2)->delete('/checkins/' . $checkin->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($checkin);
    }
}
