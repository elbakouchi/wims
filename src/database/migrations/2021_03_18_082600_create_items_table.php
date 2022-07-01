<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('variations');
        Schema::dropIfExists('items');
    }

    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->index();
            $table->string('sku')->unique()->index()->nullable();
            $table->string('symbology')->default('code128');
            $table->boolean('track_weight')->nullable();
            $table->boolean('track_quantity')->nullable();
            $table->boolean('has_variants')->nullable();
            $table->boolean('has_serials')->nullable();
            $table->decimal('alert_quantity', 15, 4)->nullable();
            $table->json('variants')->nullable();
            $table->text('details')->nullable();
            $table->string('rack_location')->nullable();
            $table->foreignId('unit_id')->nullable()->index()->constrained();
            $table->foreignId('account_id')->index()->constrained();
            $table->schemalessAttributes('extra_attributes');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('variations', function (Blueprint $table) {
            $table->id();
            $table->uuid('sku')->unique()->index();
            $table->foreignId('item_id')->index()->constrained();
            $table->string('rack_location')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('account_id')->index()->constrained();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->index()->constrained();
            $table->foreignId('warehouse_id')->index()->constrained();
            $table->foreignId('variation_id')->nullable()->index()->constrained();
            $table->string('rack_location')->nullable();
            $table->decimal('weight', 25, 4)->nullable();
            $table->decimal('quantity', 25, 4)->nullable();
            $table->decimal('alert_quantity', 15, 4)->nullable();
            $table->foreignId('account_id')->index()->constrained();
            $table->unique(['item_id', 'variation_id', 'warehouse_id']);
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
