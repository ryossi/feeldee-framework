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
        Schema::create('replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->comment('コメントID')->constrained('comments')->cascadeOnDelete();
            $table->dateTime('replied_at')->comment('返信日時');
            $table->text('body')->nullable()->comment('返信本文');
            $table->foreignId('replyer_profile_id')->nullable()->comment('返信者プロフィールID')->references('id')->on('profiles')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->string('replyer_nickname')->nullable()->comment('返信者ニックネーム');
            $table->boolean('is_public')->default(false)->comment('返信公開フラグ');
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
        Schema::dropIfExists('replies');
    }
};
