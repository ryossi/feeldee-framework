<?php

use Feeldee\Framework\Models\User;
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
        Schema::create('media_boxes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("user_id")->comment('ユーザID')->constrained()->cascadeOnDelete();
            $table->string('directory')->comment('ディレクトリ')->unique();
            $table->integer('max_size')->nullable()->comment('最大容量（MB）');
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
        Schema::dropIfExists('media_boxes');
    }
};
