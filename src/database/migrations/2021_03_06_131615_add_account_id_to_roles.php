<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountIdToRoles extends Migration
{
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }

    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('account_id')->index()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->softDeletes();

            $table->dropUnique(['name', 'guard_name']);
            $table->unique(['name', 'guard_name', 'account_id']);
        });
    }
}
