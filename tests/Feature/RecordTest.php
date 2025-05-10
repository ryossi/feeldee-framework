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

    /**
     * レコーダ名
     * 
     * - レコーダの名前であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ名
     */
    public function test_name()
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
        $this->assertEquals('テストレコード', $recorder->name, 'レコーダの名前であること');
        $this->assertDatabaseHas('recorders', [
            'name' => 'テストレコード',
        ]);
    }

    /**
     * レコーダ名
     * 
     * - レコーダ名は必須項目であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ名
     */
    public function test_name_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->recorders()->create([
                'type' => Photo::type(),
                'data_type' => 'int',
            ]);
        }, ApplicationException::class, 'RecordRecorderNameRequired');
    }

    /**
     * レコーダ名
     * 
     * - レコーダ所有プロフィールとレコーダタイプの中でユニークであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ名
     */
    public function test_name_unique()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Recorder::factory(1, ['name' => 'テストレコード', 'type' => Post::type()]))->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->recorders()->create([
                'name' => 'テストレコード',
                'type' => Post::type(),
                'data_type' => 'int',
            ]);
        }, ApplicationException::class, 'RecordRecorderNameDuplicated');
    }

    /**
     * レコーダ名
     * 
     * - レコーダ所有プロフィールとレコーダタイプの中でユニークであることを確認します。
     * - レコーダタイプが異なる場合は、登録できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ名
     */
    public function test_name_unique_with_different_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Recorder::factory(1, ['name' => 'テストレコード', 'type' => Post::type()]))->create();

        // 実行
        $recorder = $profile->recorders()->create([
            'name' => 'テストレコード',
            'type' => Item::type(),
            'data_type' => 'int',
        ]);

        // 評価
        $this->assertEquals('テストレコード', $recorder->name, 'レコーダタイプが異なる場合は、登録できること');
        $this->assertDatabaseHas('recorders', [
            'name' => 'テストレコード',
            'type' => Item::type(),
        ]);
    }

    /**
     * レコーダ名
     * 
     * - レコーダ所有プロフィールとレコーダタイプの中でユニークであることを確認します。
     * - レコーダ所有プロフィールが異なる場合は、登録できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ名
     */
    public function test_name_unique_with_different_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(Recorder::factory(1, ['name' => 'テストレコード', 'type' => Post::type()]))->create();
        $otherProfile = Profile::factory()->create();

        // 実行
        $recorder = $otherProfile->recorders()->create([
            'name' => 'テストレコード',
            'type' => Post::type(),
            'data_type' => 'int',
        ]);

        // 評価
        $this->assertEquals('テストレコード', $recorder->name, 'レコーダ所有プロフィールが異なる場合は、登録できること');
        $this->assertDatabaseHas('recorders', [
            'name' => 'テストレコード',
            'type' => Post::type(),
            'profile_id' => $otherProfile->id,
        ]);
    }

    /**
     * レコードデータ型
     * 
     * - レコーダが記録するレコード値のデータ型であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki//レコード#レコードデータ型
     */
    public function test_data_type()
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
        $this->assertEquals('int', $recorder->data_type, 'レコーダが記録するレコード値のデータ型であること');
        $this->assertDatabaseHas('recorders', [
            'data_type' => 'int',
        ]);
    }

    /**
     * レコードデータ型
     * 
     * - レコードデータ型は必須項目であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki//レコード#レコードデータ型
     */
    public function test_data_type_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->recorders()->create([
                'name' => 'テストレコード',
                'type' => Post::type(),
            ]);
        }, ApplicationException::class, 'RecordDataTypeRequired');
    }

    /**
     * レコード単位ラベル
     * 
     * - 画面表示や印刷時にレコード値の単位ラベルとして使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki//レコード#レコード単位ラベル
     */
    public function test_unit()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $recorder = $profile->recorders()->create([
            'name' => 'テストレコード',
            'type' => Post::type(),
            'data_type' => 'int',
            'unit' => 'km',
        ]);

        // 評価
        $this->assertEquals('km', $recorder->unit, '画面表示や印刷時にレコード値の単位ラベルとして使用できること');
        $this->assertDatabaseHas('recorders', [
            'unit' => 'km',
        ]);
    }

    /**
     * レコーダ説明
     * 
     * - レコーダの説明であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki//レコード#レコーダ説明
     */
    public function test_description()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $recorder = $profile->recorders()->create([
            'name' => 'テストレコード',
            'type' => Post::type(),
            'data_type' => 'int',
            'description' => 'テストレコードの説明',
        ]);

        // 評価
        $this->assertEquals('テストレコードの説明', $recorder->description, 'レコーダの説明であること');
        $this->assertDatabaseHas('recorders', [
            'description' => 'テストレコードの説明',
        ]);
    }
}
