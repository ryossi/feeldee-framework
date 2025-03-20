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
        Schema::create('posted_photos', function (Blueprint $table) {
            $table->foreignId('post_id')->comment('投稿ID')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('photo_id')->comment('写真ID')->constrained('photos')->cascadeOnDelete();
            $table->primary(['photo_id', 'post_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posted_photos');
    }
};
