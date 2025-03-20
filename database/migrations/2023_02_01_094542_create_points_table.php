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
        Schema::create('points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->comment('プロフィールID')->constrained('profiles')->cascadeOnDelete();
            $table->foreignId('post_id')->nullable()->comment('投稿ID')->constrained('posts')->cascadeOnDelete();
            $table->string('title')->comment('タイトル');
            $table->dateTime('point_datetime')->nullable()->comment('ポイント日時');
            $table->text('memo')->nullable()->comment('メモ');
            $table->decimal('latitude', 9, 7, true)->comment('緯度');
            $table->decimal('longitude', 10, 7, true)->comment('経度');
            $table->string('point_type', 100)->nullable()->comment('ポイントタイプ');
            $table->string('image_src', 767)->nullable()->comment('イメージソース');
            $table->bigInteger('created_by')->comment('登録者');
            $table->bigInteger('updated_by')->comment('更新者');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('points');
    }
};
