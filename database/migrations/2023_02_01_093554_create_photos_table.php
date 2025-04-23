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
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->comment('コンテンツ所有者プロフィールID')->constrained('profiles')->cascadeOnDelete();
            $table->string('title')->nullable()->comment('写真タイトル');
            $table->mediumText('value')->nullable()->comment('写真内容');
            $table->mediumText('text')->nullable()->comment('写真テキスト');
            $table->string('photo_type', 255)->comment('写真タイプ');
            $table->string('src', 767)->comment('写真ソース');
            $table->dateTime('regist_datetime')->comment('写真登録日時');
            $table->integer('width')->nullable()->comment('写真イメージ幅');
            $table->integer('height')->nullable()->comment('写真イメージ高さ');
            $table->decimal('latitude', 9, 7, true)->nullable()->comment('撮影緯度');
            $table->decimal('longitude', 10, 7, true)->nullable()->comment('撮影経度');
            $table->foreignId('category_id')->nullable()->comment('コンテンツカテゴリーID')->constrained('categories')->onDelete('set null');;
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
        Schema::dropIfExists('photos');
    }
};
