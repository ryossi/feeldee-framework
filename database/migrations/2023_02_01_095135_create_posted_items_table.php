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
        Schema::create('posted_items', function (Blueprint $table) {
            $table->bigInteger('post_id')->comment('投稿ID');
            $table->string('post_type')->comment('投稿タイプ');
            $table->foreignId('item_id')->comment('アイテムID')->constrained('items')->cascadeOnDelete();
            $table->string('label')->nullable()->comment('投稿アイテムラベル');
            $table->integer('order_number')->default('0')->comment('投稿アイテム表示順');
            $table->bigInteger('created_by')->nullable()->comment('登録者');
            $table->bigInteger('updated_by')->nullable()->comment('更新者');
            $table->timestamps();
            $table->primary(['post_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posted_items');
    }
};
