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
        Schema::create('timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->comment('投稿ID')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('location_id')->comment('場所ID')->constrained('locations')->cascadeOnDelete();
            $table->dateTime('start_datetime')->nullable()->comment('開始日時');
            $table->dateTime('end_datetime')->nullable()->comment('終了日時');
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
        Schema::dropIfExists('timelines');
    }
};
