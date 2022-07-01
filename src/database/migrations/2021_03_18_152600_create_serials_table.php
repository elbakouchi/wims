<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSerialsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('serials');
    }

    public function up()
    {
        Schema::create('serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->index()->constrained();
            $table->string('number');
            $table->boolean('sold')->nullable();
            $table->foreignId('account_id')->index()->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
