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
        Schema::create('item_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->comment('投稿ID')->constrained('posts')->cascadeOnDelete();
            $table->string('name')->nullable()->comment('アイテムグループ名');
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
        Schema::dropIfExists('item_groups');
    }
};
