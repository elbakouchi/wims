<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_validation()
    {
        $this->actingAs($this->createUser());
        $response = $this->post('/categories', []);
        $response->assertSessionHasErrors(['name', 'code']);
    }

    public function test_guest_can_not_create_category()
    {
        $this->post('/categories', ['name' => 'Category'])->assertRedirect(route('login'));
    }

    public function test_super_admin_can_manage_categories()
    {
        $this->actingAs($this->createUser());

        // Can create
        $this->post('/categories', ['code' => 'c', 'name' => 'Category'])
            ->assertSessionHas('message', __choice('action_text', ['record' => 'Category', 'action' => 'created']));

        $category = Category::first();
        $this->assertEquals('c', $category->code);
        $this->assertEquals('Category', $category->name);

        // Can update
        $this->put('/categories/' . $category->id, ['code' => 'c1', 'name' => 'Category 1'])->assertSessionHas('message');

        $category->refresh();
        $this->assertEquals('c1', $category->code);
        $this->assertEquals('Category 1', $category->name);

        // Add child category
        $this->post('/categories', ['code' => 'cs', 'name' => 'Child Category', 'parent_id' => $category->id])
            ->assertSessionHas('message', __choice('action_text', ['record' => 'Category', 'action' => 'created']));

        $this->assertDatabaseCount('categories', 2);

        // Can't delete parent when has child
        $this->delete('/categories/' . $category->id)->assertSessionHas('error');
        $this->delete('/categories/' . $category->id . '/permanently')->assertSessionHas('error');

        $child_category = Category::where('code', 'cs')->first();

        // Can delete
        $this->delete('/categories/' . $child_category->id)->assertSessionHas('message');
        $this->delete('/categories/' . $category->id)->assertSessionHas('message');
        $this->assertSoftDeleted($category);
        $this->assertTrue($category->refresh()->trashed());

        // can restore
        $this->put('/categories/' . $category->id . '/restore')->assertSessionHas('message');
        $this->assertFalse($category->refresh()->trashed());

        // Can delete permanently
        $this->delete('/categories/' . $category->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($category);

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_user_with_no_permissions_can_not_create_category()
    {
        $this->actingAs($this->createUser('Admin'));
        $this->post('/categories', ['code' => 'c', 'name' => 'Category'])->assertSessionHas('error');
    }

    public function test_user_with_permissions_can_manage_categories()
    {
        $admin = $this->createUserWithPermissions('Admin', ['read-categories', 'create-categories']);

        // can create
        $this->actingAs($admin)->post('/categories', ['code' => 'c', 'name' => 'Category'])->assertSessionHas('message');

        $category = Category::first();
        $this->assertEquals('c', $category->code);
        $this->assertEquals('Category', $category->name);

        // Can't update or delete
        $this->put('/categories/' . $category->id, ['code' => 'c1', 'name' => 'Category 1'])->assertSessionHas('error');
        $this->delete('/categories/' . $category->id)->assertSessionHas('error');
        $this->assertFalse($category->refresh()->trashed());

        // New admin with update and delete permissions
        $admin2 = $this->createUserWithPermissions('Admin', ['read-categories', 'create-categories', 'update-categories', 'delete-categories'], $admin->account);

        // Can update
        $this->actingAs($admin2)
            ->put('/categories/' . $category->id, ['code' => 'c2', 'name' => 'Category 2'])->assertSessionHas('message');

        $category->refresh();
        $this->assertEquals('c2', $category->code);
        $this->assertEquals('Category 2', $category->name);

        // can delete
        $this->actingAs($admin2)->delete('/categories/' . $category->id)->assertSessionHas('message');
        $this->assertSoftDeleted($category);

        $this->actingAs($admin2)->delete('/categories/' . $category->id . '/permanently')->assertSessionHas('message');
        $this->assertDeleted($category);
    }
}
