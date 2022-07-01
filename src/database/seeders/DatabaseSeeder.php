<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Unit;
use App\Models\User;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Warehouse;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // config(['activitylog.enabled' => false]);
        $account = Account::create(['name' => 'Tecdiary']);
        $role    = $account->roles()->create([
            'guard_name' => 'web',
            'account_id' => $account->id,
            'name'       => 'Super Admin',
        ]);
        $admin = User::factory()->create([
            'username'   => 'admin',
            'account_id' => $account->id,
            'email'      => 'admin@tecdiary.com',
            'password'   => Hash::make('amI$tup!D?n0'),
        ]);
        $admin->roles()->sync($role);
        auth()->login($admin);

        Setting::updateOrCreate(['tec_key' => 'name', 'account_id' => $account->id], ['tec_value' => 'Warehouse']);
        Setting::updateOrCreate(['tec_key' => 'currency_code', 'account_id' => $account->id], ['tec_value' => 'USD']);
        Setting::updateOrCreate(['tec_key' => 'default_locale', 'account_id' => $account->id], ['tec_value' => 'en']);
        Setting::updateOrCreate(['tec_key' => 'sidebar', 'account_id' => $account->id], ['tec_value' => 'full']);
        Setting::updateOrCreate(['tec_key' => 'sidebar_style', 'account_id' => $account->id], ['tec_value' => 'dropdown']);
        Setting::updateOrCreate(['tec_key' => 'fraction', 'account_id' => $account->id], ['tec_value' => '2']);
        Setting::updateOrCreate(['tec_key' => 'modal_position', 'account_id' => $account->id], ['tec_value' => 'center']);
        Setting::updateOrCreate(['tec_key' => 'per_page', 'account_id' => $account->id], ['tec_value' => '10']);
        Setting::updateOrCreate(['tec_key' => 'track_weight', 'account_id' => $account->id], ['tec_value' => '1']);
        Setting::updateOrCreate(['tec_key' => 'weight_unit', 'account_id' => $account->id], ['tec_value' => 'kg']);

        // activity
        Permission::firstOrCreate(['name' => 'read-activity']);
        // reports
        Permission::firstOrCreate(['name' => 'read-reports']);
        // checkins
        Permission::firstOrCreate(['name' => 'read-checkins']);
        Permission::firstOrCreate(['name' => 'update-checkins']);
        Permission::firstOrCreate(['name' => 'create-checkins']);
        Permission::firstOrCreate(['name' => 'delete-checkins']);
        // checkouts
        Permission::firstOrCreate(['name' => 'read-checkouts']);
        Permission::firstOrCreate(['name' => 'update-checkouts']);
        Permission::firstOrCreate(['name' => 'create-checkouts']);
        Permission::firstOrCreate(['name' => 'delete-checkouts']);
        // adjustments
        Permission::firstOrCreate(['name' => 'read-adjustments']);
        Permission::firstOrCreate(['name' => 'update-adjustments']);
        Permission::firstOrCreate(['name' => 'create-adjustments']);
        Permission::firstOrCreate(['name' => 'delete-adjustments']);
        // transfers
        Permission::firstOrCreate(['name' => 'read-transfers']);
        Permission::firstOrCreate(['name' => 'update-transfers']);
        Permission::firstOrCreate(['name' => 'create-transfers']);
        Permission::firstOrCreate(['name' => 'delete-transfers']);
        // items
        Permission::firstOrCreate(['name' => 'read-items']);
        Permission::firstOrCreate(['name' => 'update-items']);
        Permission::firstOrCreate(['name' => 'create-items']);
        Permission::firstOrCreate(['name' => 'delete-items']);
        // units
        Permission::firstOrCreate(['name' => 'read-units']);
        Permission::firstOrCreate(['name' => 'update-units']);
        Permission::firstOrCreate(['name' => 'create-units']);
        Permission::firstOrCreate(['name' => 'delete-units']);
        // warehouses
        Permission::firstOrCreate(['name' => 'read-warehouses']);
        Permission::firstOrCreate(['name' => 'update-warehouses']);
        Permission::firstOrCreate(['name' => 'create-warehouses']);
        Permission::firstOrCreate(['name' => 'delete-warehouses']);
        // categories
        Permission::firstOrCreate(['name' => 'read-categories']);
        Permission::firstOrCreate(['name' => 'update-categories']);
        Permission::firstOrCreate(['name' => 'create-categories']);
        Permission::firstOrCreate(['name' => 'delete-categories']);
        // users
        Permission::firstOrCreate(['name' => 'read-users']);
        Permission::firstOrCreate(['name' => 'update-users']);
        Permission::firstOrCreate(['name' => 'create-users']);
        Permission::firstOrCreate(['name' => 'delete-users']);
        // roles
        Permission::firstOrCreate(['name' => 'read-roles']);
        Permission::firstOrCreate(['name' => 'update-roles']);
        Permission::firstOrCreate(['name' => 'create-roles']);
        Permission::firstOrCreate(['name' => 'delete-roles']);
        Permission::firstOrCreate(['name' => 'update-permissions']);
        // contacts
        Permission::firstOrCreate(['name' => 'read-contacts']);
        Permission::firstOrCreate(['name' => 'update-contacts']);
        Permission::firstOrCreate(['name' => 'create-contacts']);
        Permission::firstOrCreate(['name' => 'delete-contacts']);

        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            $permission->assignRole($role);
        }

        Contact::factory(50)->create(['account_id' => $account->id]);
        Warehouse::factory(4)->create(['account_id' => $account->id]);
        $categories = Category::factory(20)->create(['account_id' => $account->id]);

        // Units
        $units   = [];
        $units[] = Unit::factory()->create(['code' => 'm', 'name' => 'Meter', 'account_id' => $account->id]);
        $units[] = Unit::factory()->create(['code' => 'pc', 'name' => 'Piece', 'account_id' => $account->id]);
        $units[] = Unit::factory()->create(['code' => 'kg', 'name' => 'Kilogram', 'account_id' => $account->id]);
        Unit::factory()->create(['code' => 'cm', 'name' => 'Centimeter', 'base_unit_id' => 1, 'operator' => '*', 'operation_value' => 100, 'account_id' => $account->id]);
        Unit::factory()->create(['code' => 'dz', 'name' => 'Dozen', 'base_unit_id' => 2, 'operator' => '*', 'operation_value' => 12, 'account_id' => $account->id]);

        $items = Item::factory(50)->create(['account_id' => $account->id, 'unit_id' => $units[1]->id]);
        $items->each(function ($item) use ($categories) {
            $item->categories()->sync($categories[mt_rand(0, 19)]->id);
            $item->addVariations($item->has_variants ? $item->variants : []);
        });

        $this->call(OrdersDatabaseSeeder::class);
    }
}
