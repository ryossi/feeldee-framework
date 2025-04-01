<?php

use Feeldee\Framework\Models\PublicLevel;
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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->comment('コンテンツ所有者プロフィールID')->constrained('profiles')->cascadeOnDelete();
            $table->dateTime('post_date')->comment('投稿日');
            $table->string('title')->comment('記事タイトル');
            $table->mediumText('value')->nullable()->comment('記事内容');
            $table->mediumText('text')->nullable()->comment('記事テキスト');
            $table->mediumText('thumbnail')->nullable()->comment('記事サムネイル');
            $table->foreignId('category_id')->nullable()->comment('コンテンツカテゴリーID')->constrained('categories')->onDelete('set null');
            $table->boolean('is_public')->default(false)->comment('コンテンツ公開フラグ');
            $table->integer('public_level', false, true)->default(PublicLevel::Private->value)->comment('コンテンツ公開レベル');
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
        Schema::dropIfExists('posts');
    }
};
