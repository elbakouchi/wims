<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransfersTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('transfers');
    }

    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('reference')->index();
            $table->string('hash')->index()->nullable();
            $table->boolean('draft')->default('0')->nullable();
            $table->foreignId('to_warehouse_id')->index()->constrained('warehouses');
            $table->foreignId('from_warehouse_id')->index()->constrained('warehouses');
            $table->foreignId('user_id')->index()->constrained();
            $table->foreignId('approved_by')->nullable()->index()->constrained('users');
            $table->foreignId('recurring_id')->nullable()->constrained('transfers');
            $table->foreignId('account_id')->index()->constrained();
            $table->text('details')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->index()->constrained();
            $table->foreignId('item_id')->index()->constrained();
            $table->decimal('weight', 25, 4)->nullable();
            $table->decimal('quantity', 15, 4);
            $table->boolean('draft')->default('0')->nullable();
            $table->foreignId('unit_id')->nullable()->index()->constrained();
            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('to_warehouse_id')->index()->constrained('warehouses');
            $table->foreignId('from_warehouse_id')->index()->constrained('warehouses');
            $table->foreignId('account_id')->index()->constrained();
            $table->index(['transfer_id', 'item_id']);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('serials', function (Blueprint $table) {
            $table->foreignId('transfer_id')->index()->constrained('transfers');
            $table->foreignId('transfer_item_id')->index()->constrained('transfer_items');
            $table->index(['transfer_id', 'transfer_item_id']);
        });

        Schema::create('serial_transfer_item', function (Blueprint $table) {
            $table->foreignId('serial_id')->index()->constrained()->onDelete('cascade');
            $table->foreignId('transfer_item_id')->index()->constrained()->onDelete('cascade');
        });

        Schema::create('transfer_item_variation', function (Blueprint $table) {
            $table->foreignId('transfer_item_id')->index()->constrained()->onDelete('cascade');
            $table->foreignId('variation_id')->index()->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->index()->constrained();
            $table->decimal('quantity', 25, 4)->nullable();
            $table->decimal('weight', 25, 4)->nullable();
        });
    }
}
