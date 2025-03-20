<?php

use App\Models\PublicLevel;
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
        Schema::create('locations', function (Blueprint $table) {
            $precision_latitude = config('feeldee.location.precision.latitude', 7);
            $precision_longitude = config('feeldee.location.precision.longitude', 7);

            $table->id();
            $table->foreignId('profile_id')->comment('プロフィールID')->constrained('profiles')->cascadeOnDelete();
            $table->string('title')->comment('タイトル');
            $table->mediumText('value')->nullable()->comment('内容');
            $table->mediumText('text')->nullable()->comment('テキスト');
            $table->decimal('latitude', $precision_latitude + 2, $precision_latitude, true)->comment('緯度');
            $table->decimal('longitude', $precision_longitude + 3, $precision_longitude, true)->comment('経度');
            $table->integer('zoom')->comment('縮尺');
            $table->mediumText('thumbnail')->nullable()->comment('サムネイル');
            $table->foreignId('category_id')->nullable()->comment('カテゴリーID')->constrained('categories')->onDelete('set null');;
            $table->boolean('is_public')->default(false)->comment('公開フラグ');
            $table->integer('public_level', false, true)->default(PublicLevel::Private->value)->comment('公開レベル');
            $table->bigInteger('created_by')->comment('登録者');
            $table->bigInteger('updated_by')->comment('更新者');
            $table->timestamps();

            $table->unique(['profile_id', 'latitude', 'longitude'], 'uk_location');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations');
    }
};
