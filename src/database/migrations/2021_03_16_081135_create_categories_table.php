<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('categories');
        Schema::dropIfExists('categorizables');
    }

    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->index();
            $table->foreignId('parent_id')->nullable()->index()->constrained('categories')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('account_id')->index()->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->schemalessAttributes('extra_attributes');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('categorizables', function (Blueprint $table) {
            $table->foreignId('category_id')->index();
            $table->morphs('categorizable');
        });
    }
}
