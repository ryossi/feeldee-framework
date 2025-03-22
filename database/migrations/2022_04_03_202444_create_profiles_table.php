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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->comment('ユーザID');
            $table->string('nickname')->comment('ニックネーム')->unique();
            $table->mediumText('image')->nullable()->comment('イメージ');
            $table->string('title')->comment('タイトル');
            $table->string('subtitle')->nullable()->comment('サブタイトル');
            $table->string('introduction')->nullable()->comment('紹介文');
            $table->string('home', 20)->nullable()->comment('ホーム');
            $table->boolean('show_members')->default(false)->comment('メンバーリスト表示');
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
        Schema::dropIfExists('profiles');
    }
};
