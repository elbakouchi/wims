<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckoutsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('checkouts');
    }

    public function up()
    {
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('reference')->index();
            $table->string('hash')->index()->nullable();
            $table->boolean('draft')->default('0')->nullable();
            $table->foreignId('contact_id')->index()->constrained();
            $table->foreignId('warehouse_id')->index()->constrained();
            $table->foreignId('user_id')->index()->constrained();
            $table->foreignId('approved_by')->nullable()->index()->constrained('users');
            $table->foreignId('recurring_id')->nullable()->constrained('checkouts');
            $table->foreignId('account_id')->index()->constrained();
            $table->text('details')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('checkout_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checkout_id')->index()->constrained();
            $table->foreignId('item_id')->index()->constrained();
            $table->decimal('weight', 25, 4)->nullable();
            $table->decimal('quantity', 15, 4);
            $table->boolean('draft')->default('0')->nullable();
            $table->foreignId('unit_id')->nullable()->index()->constrained();
            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('warehouse_id')->index()->constrained();
            $table->foreignId('account_id')->index()->constrained();
            $table->index(['checkout_id', 'item_id']);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('serials', function (Blueprint $table) {
            $table->foreignId('checkout_id')->index()->constrained('checkouts');
            $table->foreignId('checkout_item_id')->index()->constrained('checkout_items');
            $table->index(['checkout_id', 'checkout_item_id']);
        });

        Schema::create('checkout_item_serial', function (Blueprint $table) {
            $table->foreignId('serial_id')->index()->constrained()->onDelete('cascade');
            $table->foreignId('checkout_item_id')->index()->constrained()->onDelete('cascade');
        });

        Schema::create('checkout_item_variation', function (Blueprint $table) {
            $table->foreignId('checkout_item_id')->index()->constrained()->onDelete('cascade');
            $table->foreignId('variation_id')->index()->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->index()->constrained();
            $table->decimal('quantity', 25, 4)->nullable();
            $table->decimal('weight', 25, 4)->nullable();
        });
    }
}
