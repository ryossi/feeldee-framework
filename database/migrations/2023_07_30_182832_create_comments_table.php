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
            $table->foreignId('profile_id')->comment('プロフィールID')->constrained('profiles')->cascadeOnDelete();
            $table->dateTime('commented_at')->comment('コメント日時');
            $table->text('body')->nullable()->comment('コメント本体');
            $table->bigInteger('commentable_id')->comment('コメント対象ID');
            $table->string('commentable_type')->comment('コメント対象種別');
            $table->foreignId('commenter_profile_id')->nullable()->comment('コメント者プロフィールID')->references('id')->on('profiles')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->string('commenter_nickname')->comment('コメント者ニックネーム');
            $table->boolean('is_public')->default(false)->comment('公開フラグ');
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
        Schema::dropIfExists('comments');
    }
};
