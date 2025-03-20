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
            $table->text('body')->nullable()->comment('返信本体');
            $table->foreignId('replyer_profile_id')->nullable()->comment('返信者プロフィールID')->references('id')->on('profiles')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->string('replyer_nickname')->comment('返信者ニックネーム');
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
        Schema::dropIfExists('replies');
    }
};
