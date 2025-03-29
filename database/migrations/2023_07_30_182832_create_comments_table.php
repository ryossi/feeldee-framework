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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->comment('コメント所有者プロフィールID')->constrained('profiles')->cascadeOnDelete();
            $table->dateTime('commented_at')->comment('コメント日時');
            $table->text('body')->nullable()->comment('コメント本文');
            $table->bigInteger('commentable_id')->comment('コメント対象コンテンツID');
            $table->string('commentable_type')->comment('コメント対象コンテンツ種別');
            $table->foreignId('commenter_profile_id')->nullable()->comment('コメント者プロフィールID')->references('id')->on('profiles')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->string('commenter_nickname')->nullable()->comment('コメント者ニックネーム');
            $table->boolean('is_public')->default(false)->comment('公開フラグ');
            $table->bigInteger('created_by')->nullable()->comment('登録者');
            $table->bigInteger('updated_by')->nullable()->comment('更新者');
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
        Schema::dropIfExists('comments');
    }
};
