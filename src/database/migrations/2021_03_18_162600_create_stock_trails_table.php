<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockTrailsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('stock_trails');
    }

    public function up()
    {
        Schema::create('stock_trails', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('subject');
            $table->string('type')->nullable();
            $table->decimal('weight', 25, 4)->nullable();
            $table->decimal('quantity', 25, 4)->nullable();
            $table->foreignId('item_id')->index()->constrained();
            $table->foreignId('unit_id')->nullable()->index()->constrained();
            $table->foreignId('variation_id')->nullable()->index()->constrained();
            $table->foreignId('warehouse_id')->index()->constrained();
            $table->foreignId('account_id')->index()->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
