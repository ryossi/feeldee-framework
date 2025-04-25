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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->comment('コンテンツ所有者プロフィールID')->constrained('profiles')->cascadeOnDelete();
            $table->string('title')->comment('コンテンツタイトル');
            $table->mediumText('value')->nullable()->comment('コンテンツ内容');
            $table->mediumText('text')->nullable()->comment('コンテンツテキスト');
            $table->boolean('is_public')->default(false)->comment('コンテンツ公開フラグ');
            $table->integer('public_level', false, true)->default(PublicLevel::Private->value)->comment('コンテンツ公開レベル');
            $table->foreignId('category_id')->nullable()->comment('カテゴリーID')->constrained('categories')->onDelete('set null');;
            $table->integer('order_number')->default('0')->comment('表示順');
            $table->mediumText('image')->nullable()->comment('イメージ');
            $table->bigInteger('created_by')->comment('登録者');
            $table->bigInteger('updated_by')->comment('更新者');
            $table->timestamps();

            $table->unique(['profile_id', 'title', 'category_id'], 'uk_item');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
};
