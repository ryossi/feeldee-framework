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
        Schema::create('content_view_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->comment('プロフィールID')->constrained('profiles')->cascadeOnDelete();
            $table->bigInteger('content_id')->comment('コンテンツID');
            $table->string('content_type')->comment('コンテンツ種別');
            $table->timestamp('viewed_at')->comment('閲覧日時');
            $table->string('uid')->nullable()->comment('UID');
            $table->timestamps();

            $table->foreign('uid')->references('uid')->on('tracks')->nullOnDelete();
            $table->index(['profile_id']);
            $table->index(['profile_id', 'content_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_view_histories');
    }
};
