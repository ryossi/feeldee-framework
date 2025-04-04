<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->comment('プロフィールID')->constrained('profiles')->cascadeOnDelete();
            $table->string('type', 255)->comment('タイプ');
            $table->string('name', 255)->comment('カテゴリー名');
            $table->foreignId('parent_id')->nullable()->comment('親カテゴリー')->constrained('categories')->cascadeOnDelete();
            $table->integer('order_number')->default('0')->comment('表示順');
            $table->mediumText('image')->nullable()->comment('イメージ');
            $table->bigInteger('created_by')->comment('登録者');
            $table->bigInteger('updated_by')->comment('更新者');
            $table->timestamps();
            $table->unique(['profile_id', 'type', 'name'], 'uk_category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
