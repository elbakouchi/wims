<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('settings');
    }

    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('tec_key')->index();
            $table->text('tec_value');
            $table->foreignId('account_id')->index()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->index(['account_id', 'tec_key']);
        });
    }
}
