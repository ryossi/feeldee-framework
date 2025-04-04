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
        Schema::create('recorders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->comment('プロフィールID')->constrained('profiles')->cascadeOnDelete();
            $table->string('type', 255)->comment('タイプ');
            $table->string('name', 100)->comment('名前');
            $table->string('data_type', 10)->comment('データ型');
            $table->string('unit', 30)->nullable()->comment('単位');
            $table->string('description')->nullable()->comment('説明');
            $table->integer('order_number')->default('0')->comment('表示順');
            $table->mediumText('image')->nullable()->comment('イメージ');
            $table->bigInteger('created_by')->comment('登録者');
            $table->bigInteger('updated_by')->comment('更新者');
            $table->timestamps();
            $table->unique(['profile_id', 'type', 'name'], 'uk_ecorder');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recorders');
    }
};
