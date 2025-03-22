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
            $table->foreignId('item_group_id')->comment('アイテムグループID')->constrained('item_groups')->cascadeOnDelete();
            $table->foreignId('item_id')->comment('アイテムID')->constrained('items')->cascadeOnDelete();
            $table->primary(['item_group_id', 'item_id']);
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
