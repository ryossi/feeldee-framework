<?php

namespace Tests\Feature;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Post;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\Recorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RecordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * レコーダ所有プロフィール
     * 
     * - レコードを作成したユーザのプロフィールであることを確認します。
     * - プロフィールのIDが、レコーダ所有プロフィールIDに設定されていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ所有プロフィール
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $recorder = $profile->recorders()->create([
            'name' => 'テストレコード',
            'type' => Post::type(),
            'data_type' => 'int',
        ]);

        // 評価
        $this->assertEquals($profile->id, $recorder->profile->id, 'レコーダを作成したユーザのプロフィールであること');
        // プロフィールのIDが、レコーダ所有プロフィールIDに設定されていること
        $this->assertDatabaseHas('recorders', [
            'profile_id' => $profile->id,
        ]);
    }

    /** 
     * レコーダ所有プロフィール
     * 
     * - レコーダ所有プロフィールは必須項目であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ所有プロフィール
     */
    public function test_profile_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);

        // 実行
        $this->assertThrows(function () {
            Recorder::create([
                'name' => 'テストレコード',
                'type' => Post::type(),
                'data_type' => 'int',
            ]);
        }, ApplicationException::class, 'RecordRecorderProfileRequired');
    }

    /**
     * レコーダタイプ
     * 
     * - レコーダタイプは必須項目であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダタイプ
     */
    public function test_type_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->recorders()->create([
                'name' => 'テストレコード',
                'data_type' => 'int',
            ]);
        }, ApplicationException::class, 'RecordRecorderTypeRequired');
    }

    /**
     * レコーダタイプ
     * 
     * - 投稿のレコーダは、投稿のレコーダタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダタイプ
     */
    public function test_type_post()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $recorder = $profile->recorders()->create([
            'name' => 'テストレコード',
            'type' => Post::type(),
            'data_type' => 'int',
        ]);

        // 評価
        $this->assertEquals(Post::type(), $recorder->type, '投稿のレコーダタイプであること');
        $this->assertDatabaseHas('recorders', [
            'type' => Post::type(),
        ]);
    }

    /**
     * レコーダタイプ
     * 
     * - 写真のレコーダは、写真のレコーダタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダタイプ
     */
    public function test_type_photo()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $recorder = $profile->recorders()->create([
            'name' => 'テストレコード',
            'type' => Photo::type(),
            'data_type' => 'int',
        ]);

        // 評価
        $this->assertEquals(Photo::type(), $recorder->type, '写真のレコーダタイプであること');
        $this->assertDatabaseHas('recorders', [
            'type' => Photo::type(),
        ]);
    }

    /**
     * レコーダタイプ
     * 
     * - 場所のレコーダは、場所のレコーダタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダタイプ
     */
    public function test_type_location()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $recorder = $profile->recorders()->create([
            'name' => 'テストレコード',
            'type' => Location::type(),
            'data_type' => 'int',
        ]);

        // 評価
        $this->assertEquals(Location::type(), $recorder->type, '場所のレコーダタイプであること');
        $this->assertDatabaseHas('recorders', [
            'type' => Location::type(),
        ]);
    }

    /**
     * レコーダタイプ
     * 
     * - アイテムのレコーダは、アイテムのレコーダタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダタイプ
     */
    public function test_type_item()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $recorder = $profile->recorders()->create([
            'name' => 'テストレコード',
            'type' => Item::type(),
            'data_type' => 'int',
        ]);

        // 評価
        $this->assertEquals(Item::type(), $recorder->type, 'アイテムのレコーダタイプであること');
        $this->assertDatabaseHas('recorders', [
            'type' => Item::type(),
        ]);
    }
}
