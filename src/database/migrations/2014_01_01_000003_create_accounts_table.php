<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('accounts');
    }

    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id('id');
            $table->string('name', 50);
            $table->schemalessAttributes('extra_attributes');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
