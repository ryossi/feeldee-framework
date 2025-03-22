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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_box_id')->comment('メディアボックスID')->constrained()->cascadeOnDelete();
            $table->integer('size')->default(0)->comment('サイズ');
            $table->integer('width')->comment('幅');
            $table->integer('height')->comment('高さ');
            $table->string('content_type', 255)->comment('コンテンツタイプ');
            $table->string('subdirectory')->nullable()->comment('サブディレクトリ');
            $table->string('filename')->comment('ファイル名');
            $table->string('uri')->nullable()->unique()->comment('URI');
            $table->integer('rounds')->unsigned()->default(0)->comment('ラウンド');
            $table->dateTime('uploaded_at')->comment('アップロード日時');
            $table->bigInteger('created_by')->comment('登録者');
            $table->bigInteger('updated_by')->comment('更新者');
            $table->timestamps();

            $table->unique(['media_box_id', 'subdirectory', 'filename'], 'uk_media');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
};
