<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('units');
    }

    public function up()
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->char('operator')->nullable();
            $table->decimal('operation_value', 10, 4)->nullable();
            $table->foreignId('base_unit_id')->nullable()->index()->constrained('units')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('account_id')->index()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
