<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function setup(): void
    {
        parent::setup();

        $this->categories = Category::factory(5)->create(['account_id' => $this->account->id]);
    }

    public function test_guest_can_not_create_item()
    {
        $this->post('/items', [])->assertRedirect(route('login'));
    }

    public function test_item_validation()
    {
        $this->actingAs($this->createUser());
        $response = $this->post('/items', []);
        $response->assertSessionHasErrors(['code', 'name', 'category_id']);
    }

    public function test_item_with_no_permissions_can_not_create_item()
    {
        $this->actingAs($this->createUser('Admin'));
        $form = Item::factory()->make(['category_id' => 1, 'account_id' => $this->account->id]);
        $this->post('/items', $form->toArray())->assertSessionHas('error');
    }

    public function test_super_admin_can_manage_items()
    {
        $this->actingAs($this->createUser('Super Admin', $this->account));

        // Can create
        $form = Item::factory()->make([
            'account_id'  => $this->account->id,
            'category_id' => $this->categories->random()->first()->id,
        ])->toArray();
        $this->post('/items', $form)->assertSessionHas('message');

        $item = Item::where('code', $form['code'])->first();
        $this->assertEquals($form['code'], $item->code);
        $this->assertEquals($form['name'], $item->name);
        $this->assertEquals($form['category_id'], $item->categories()->first()->id);

        // Can update
        $form['name'] = 'Item 1';
        $this->put('/items/' . $item->id, $form)->assertSessionHas('message');

        $item->refresh();
        $this->assertEquals('Item 1', $item->name);

        // Can delete
        $this->delete('/items/' . $item->id)->assertSessionHas('message');
        $this->assertSoftDeleted($item);
        $this->assertTrue($item->refresh()->trashed());

        // can restore
        $this->put('/items/' . $item->id . '/restore')->assertSessionHas('message');
        $this->assertFalse($item->refresh()->trashed());

        // Can delete permanently
        $this->delete('/items/' . $item->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($item);

        $form = Item::factory()->make([
            'code'         => 'IT01',
            'has_variants' => true,
            'account_id'   => $this->account->id,
            'category_id'  => $this->categories->random()->first()->id,
            'variants'     => json_decode('[{"name":"Color","option":["Red", "Green"]}, {"name":"Size","option":["S","M","L"]}]', true),
        ])->toArray();

        $this->post('/items', $form)->assertSessionHas('message');
        $item = Item::where('code', $form['code'])->first();
        $this->assertEquals(6, $item->variations->count());

        $form['variants'] = json_decode('[{"name":"Color","option":["Red", "Green", "Black"]}, {"name":"Size","option":["S","M","L"]}]', true);
        $this->put('/items/' . $item->id, $form)->assertSessionHas('message');
        $this->assertEquals(9, $item->refresh()->variations->count());
    }

    public function test_user_with_permissions_can_manage_items()
    {
        $admin = $this->createUserWithPermissions('Admin', ['read-items', 'create-items'], $this->account);

        // can create
        $form = Item::factory(['account_id' => 1, 'category_id' => 1])->make()->toArray();

        $this->actingAs($admin)->post('/items', $form)->assertSessionHas('message');

        $item = Item::where('code', $form['code'])->first();
        $this->assertEquals($form['code'], $item->code);
        $this->assertEquals($form['name'], $item->name);
        $this->assertEquals($form['category_id'], $item->categories->first()->id);

        // Can't update or delete
        $form['name'] = 'Item 2';
        $this->put('/items/' . $item->id, $form)->assertSessionHas('error');
        $this->delete('/items/' . $item->id)->assertSessionHas('error');
        $this->assertFalse($item->refresh()->trashed());

        // New admin with update and delete permissions
        $admin2 = $this->createUserWithPermissions('Admin', ['read-items', 'create-items', 'update-items', 'delete-items'], $admin->account);

        // Can update
        $this->actingAs($admin2)->put('/items/' . $item->id, $form)->assertSessionHas('message');

        $item->refresh();
        $this->assertEquals('Item 2', $item->name);

        // can delete
        $this->actingAs($admin2)->delete('/items/' . $item->id)->assertSessionHas('message');
        $this->assertSoftDeleted($item);

        $this->actingAs($admin2)->delete('/items/' . $item->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($item);
    }
}
