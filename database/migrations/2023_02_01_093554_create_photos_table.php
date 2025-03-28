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
            $table->string('title')->nullable()->comment('タイトル');
            $table->mediumText('value')->nullable()->comment('内容');
            $table->mediumText('text')->nullable()->comment('テキスト');
            $table->string('photo_type', 255)->comment('写真タイプ');
            $table->string('src', 767)->comment('ソース');
            $table->dateTime('regist_datetime')->comment('登録日時');
            $table->integer('width')->nullable()->comment('幅');
            $table->integer('height')->nullable()->comment('高さ');
            $table->decimal('latitude', 9, 7, true)->nullable()->comment('緯度');
            $table->decimal('longitude', 10, 7, true)->nullable()->comment('経度');
            $table->foreignId('category_id')->nullable()->comment('カテゴリーID')->constrained('categories')->onDelete('set null');;
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
        Schema::dropIfExists('photos');
    }
};
