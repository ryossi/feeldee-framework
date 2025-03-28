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
            $table->foreignId('profile_id')->comment('投稿所有者')->constrained('profiles')->cascadeOnDelete();
            $table->dateTime('post_date')->comment('投稿日');
            $table->string('title')->comment('タイトル');
            $table->mediumText('value')->nullable()->comment('内容');
            $table->mediumText('text')->nullable()->comment('テキスト');
            $table->mediumText('thumbnail')->nullable()->comment('サムネイル');
            $table->foreignId('category_id')->nullable()->comment('カテゴリー')->constrained('categories')->onDelete('set null');
            $table->boolean('is_public')->default(false)->comment('公開フラグ');
            $table->integer('public_level', false, true)->default(PublicLevel::Private->value)->comment('公開レベル');
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
