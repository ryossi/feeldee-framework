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
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->comment('投稿者プロフィールID')->constrained('profiles')->cascadeOnDelete();
            $table->string('title')->comment('投稿タイトル');
            $table->mediumText('value')->nullable()->comment('投稿内容');
            $table->mediumText('text')->nullable()->comment('投稿内容テキスト');
            $table->boolean('is_public')->default(false)->comment('投稿公開フラグ');
            $table->integer('public_level', false, true)->default(PublicLevel::Private->value)->comment('投稿公開レベル');
            $table->foreignId('category_id')->nullable()->comment('投稿カテゴリーID')->constrained('categories')->onDelete('set null');
            $table->dateTime('posted_at')->comment('投稿日時');
            $table->mediumText('thumbnail')->nullable()->comment('投稿サムネイル');
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
        Schema::dropIfExists('journals');
    }
};
