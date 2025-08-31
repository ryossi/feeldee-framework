<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Journal;
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
            'type' => Journal::type(),
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
                'type' => Journal::type(),
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
            'type' => Journal::type(),
            'data_type' => 'int',
        ]);

        // 評価
        $this->assertEquals(Journal::type(), $recorder->type, '投稿のレコーダタイプであること');
        $this->assertDatabaseHas('recorders', [
            'type' => Journal::type(),
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
        $profile = Profile::factory()->has(Recorder::factory(1, ['name' => 'テストレコード', 'type' => Journal::type()]))->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->recorders()->create([
                'name' => 'テストレコード',
                'type' => Journal::type(),
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
        $profile = Profile::factory()->has(Recorder::factory(1, ['name' => 'テストレコード', 'type' => Journal::type()]))->create();

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
        Profile::factory()->has(Recorder::factory(1, ['name' => 'テストレコード', 'type' => Journal::type()]))->create();
        $otherProfile = Profile::factory()->create();

        // 実行
        $recorder = $otherProfile->recorders()->create([
            'name' => 'テストレコード',
            'type' => Journal::type(),
            'data_type' => 'int',
        ]);

        // 評価
        $this->assertEquals('テストレコード', $recorder->name, 'レコーダ所有プロフィールが異なる場合は、登録できること');
        $this->assertDatabaseHas('recorders', [
            'name' => 'テストレコード',
            'type' => Journal::type(),
            'profile_id' => $otherProfile->id,
        ]);
    }

    /**
     * レコーダイメージ
     * 
     * - レコーダのイメージ画像であることを確認します。
     * - URL形式で保存できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダイメージ
     */
    public function test_image_url()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $image = '/path/to/image.jpg';

        // 実行
        $recorder = $profile->recorders()->create([
            'name' => 'テストレコード',
            'type' => Journal::type(),
            'data_type' => 'string',
            'image' => $image,
        ]);

        // 検証
        $this->assertEquals($image, $recorder->image, 'レコーダのイメージ画像であること');
        // URL形式で保存できること
        $this->assertDatabaseHas('recorders', [
            'image' => $image,
        ]);
    }

    /**
     * レコーダイメージ
     * 
     * - レコーダのイメージ画像であることを確認します。
     * - Base64形式で保存できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダイメージ
     */
    public function test_image_base64()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $image = 'data:image/jpeg;base64,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';

        // 実行
        $recorder = $profile->recorders()->create([
            'name' => 'テストレコード',
            'type' => Journal::type(),
            'data_type' => 'string',
            'image' => $image,
        ]);

        // 検証
        $this->assertEquals($image, $recorder->image, 'レコーダのイメージ画像であること');
        // Base64形式で保存できること
        $this->assertDatabaseHas('recorders', [
            'image' => $image,
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
            'type' => Journal::type(),
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
                'type' => Journal::type(),
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
            'type' => Journal::type(),
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
            'type' => Journal::type(),
            'data_type' => 'int',
            'description' => 'テストレコードの説明',
        ]);

        // 評価
        $this->assertEquals('テストレコードの説明', $recorder->description, 'レコーダの説明であること');
        $this->assertDatabaseHas('recorders', [
            'description' => 'テストレコードの説明',
        ]);
    }

    /**
     * レコーダ表示順
     * 
     * - 同じレコーダ所有プロフィール、レコーダタイプでレコーダの表示順を決定するための番号であることを確認します。
     * - 作成時に自動採番されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ表示順
     */
    public function test_order_number()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $recorder1 = $profile->recorders()->create([
            'name' => 'テストレコード1',
            'type' => Journal::type(),
            'data_type' => 'string',
        ]);
        $recorder2 = $profile->recorders()->create([
            'name' => 'テストレコード2',
            'type' => Journal::type(),
            'data_type' => 'string',
        ]);
        $recorder3 = $profile->recorders()->create([
            'name' => 'テストレコード3',
            'type' => Journal::type(),
            'data_type' => 'string',
        ]);

        // 評価
        $this->assertEquals(1, $recorder1->order_number, '同じレコーダ所有プロフィール、レコーダタイプの中での表示順を持つこと');
        $this->assertEquals(2, $recorder2->order_number, '同じレコーダ所有プロフィール、レコーダタイプの中での表示順を持つこと');
        $this->assertEquals(3, $recorder3->order_number, '同じレコーダ所有プロフィール、レコーダタイプの中での表示順を持つこと');
        // タグ表示順は、作成時に自動採番されること
        foreach ($profile->recorders as $recorder) {
            $this->assertDatabaseHas('recorders', [
                'id' => $recorder->id,
                'order_number' => $recorder->order_number,
            ]);
        }
    }

    /**
     * レコーダ表示順
     * 
     * - 表示順で前のレコーダに容易にアクセスすることができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ表示順
     */
    public function test_order_number_previous()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $recorder1 = $profile->recorders()->create([
            'name' => 'テストレコード1',
            'type' => Journal::type(),
            'data_type' => 'string',
        ]);
        $recorder2 = $profile->recorders()->create([
            'name' => 'テストレコード2',
            'type' => Journal::type(),
            'data_type' => 'string',
        ]);

        // 実行
        $previous = $recorder2->previous();

        // 評価
        $this->assertEquals($recorder1->id, $previous->id, '表示順で前のレコーダに容易にアクセスすることができること');
    }

    /**
     * レコーダ表示順
     * 
     * - 表示順で後のレコーダに容易にアクセスすることができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ表示順
     */
    public function test_order_number_next()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $recorder1 = $profile->recorders()->create([
            'name' => 'テストレコード1',
            'type' => Journal::type(),
            'data_type' => 'string',
        ]);
        $recorder2 = $profile->recorders()->create([
            'name' => 'テストレコード2',
            'type' => Journal::type(),
            'data_type' => 'string',
        ]);

        // 実行
        $next = $recorder1->next();

        // 評価
        $this->assertEquals($recorder2->id, $next->id, '表示順で後のレコーダに容易にアクセスすることができること');
    }

    /**
     * レコーダ表示順
     * 
     * - 直接編集しなくても同じレコーダ所有プロフィール、レコーダタイプの中で表示順を上へ移動することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ表示順
     */
    public function test_order_number_up()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder1 = $profile->recorders()->create([
            'name' => 'テストレコード1',
            'type' => Journal::type(),
            'data_type' => 'string',
        ]);
        $recorder2 = $profile->recorders()->create([
            'name' => 'テストレコード2',
            'type' => Journal::type(),
            'data_type' => 'string',
        ]);

        // 実行
        $recorder2->orderUp();

        // 評価
        $this->assertEquals(1, $recorder2->order_number, '同じレコーダ所有プロフィール、レコーダタイプの中で表示順を上へ移動することができること');
        $this->assertDatabaseHas('recorders', [
            'id' => $recorder2->id,
            'order_number' => 1,
        ]);
        $recorder1->refresh();
        $this->assertEquals(2, $recorder1->order_number, '同じレコーダ所有プロフィール、レコーダタイプの中で表示順を上へ移動することができること');
        $this->assertDatabaseHas('recorders', [
            'id' => $recorder1->id,
            'order_number' => 2,
        ]);
    }

    /**
     * レコーダ表示順
     * 
     * - 直接編集しなくても同じレコーダ所有プロフィール、レコーダタイプの中で表示順を下へ移動することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ表示順
     */
    public function test_order_number_down()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder1 = $profile->recorders()->create([
            'name' => 'テストレコード1',
            'type' => Journal::type(),
            'data_type' => 'string',
        ]);
        $recorder2 = $profile->recorders()->create([
            'name' => 'テストレコード2',
            'type' => Journal::type(),
            'data_type' => 'string',
        ]);

        // 実行
        $recorder1->orderDown();

        // 評価
        $this->assertEquals(2, $recorder1->order_number, '同じレコーダ所有プロフィール、レコーダタイプの中で表示順を下へ移動することができること');
        $this->assertDatabaseHas('recorders', [
            'id' => $recorder1->id,
            'order_number' => 2,
        ]);
        $recorder2->refresh();
        $this->assertEquals(1, $recorder2->order_number, '同じレコーダ所有プロフィール、レコーダタイプの中で表示順を下へ移動することができること');
        $this->assertDatabaseHas('recorders', [
            'id' => $recorder2->id,
            'order_number' => 1,
        ]);
    }

    /**
     * レコードリスト
     * 
     * - レコーダで記録されたレコードのコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコードリスト
     */
    public function test_records()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'int',
        ]);
        $posts = Journal::factory()->count(3)->create([
            'profile_id' => $profile->id,
        ]);

        // 実行
        foreach ($posts as $i => $post) {
            $recorder->records()->create([
                'post' => $post,
                'value' => $i,
            ]);
        }

        // 評価
        $this->assertEquals(3, $recorder->records->count(), 'レコーダで記録されたレコードのコレクションであること');
    }

    /**
     * レコードリスト
     * 
     * - レコーダ所有プロフィールが投稿者プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコードリスト
     */
    public function test_records_different_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $otherProfile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'int',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $otherProfile->id,
        ]);

        // 評価
        $this->assertThrows(function () use ($recorder, $post) {
            $recorder->records()->create([
                'recordable_id' => $post->id,
                'value' => 1,
            ]);
        }, ApplicationException::class, 'RecordProfileMissmatch');
    }

    /**
     * レコードリスト
     * 
     * - レコーダタイプが投稿種別と一致していることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコードリスト
     */
    public function test_records_different_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Item::type(),
            'data_type' => 'int',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);

        // 評価
        $this->assertThrows(function () use ($recorder, $post) {
            $recorder->records()->create([
                'post' => $post,
                'value' => 1,
            ]);
        }, ApplicationException::class, 'RecordTypeMissmatch');
    }

    /**
     * レコードリスト
     * 
     * - レコードに紐付く投稿を削除すると、レコードリストからも自動的に除外されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコードリスト
     */
    public function test_records_delete()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'int',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder->records()->create([
            'post' => $post,
            'value' => 1,
        ]);

        // 実行
        $recorder->delete();

        // 評価
        $this->assertEmpty($post->records, 'レコードに紐付く投稿を削除すると、レコードリストからも自動的に除外されること');
    }

    /**
     * レコーダ
     * 
     * - レコードの記録は、レコーダのrecordメソッドを使うことで作成できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコーダ
     */
    public function test_record_create()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'int',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);

        // 実行
        $record = $recorder->record($post, 1);

        // 評価
        $this->assertEquals($post->id, $record->recordable_id, 'レコードの記録は、レコーダのrecordメソッドを使うことで作成できること');
        $this->assertDatabaseHas('records', [
            'id' => $record->id,
            'recordable_id' => $post->id,
            'recorder_id' => $recorder->id,
            'value' => 1,
        ]);
    }

    /**
     * レコーダ
     * 
     * - レコードの記録は、レコーダのrecordメソッドを使うことで編集できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコーダ
     */
    public function test_record_update()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'int',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $record = $recorder->records()->create([
            'post' => $post,
            'value' => 1,
        ]);

        // 実行
        $record = $recorder->record($post, 2);

        // 評価
        $this->assertEquals($post->id, $record->recordable_id, 'レコードの記録は、レコーダのrecordメソッドを使うことで編集できること');
        $this->assertDatabaseHas('records', [
            'id' => $record->id,
            'recordable_id' => $post->id,
            'recorder_id' => $recorder->id,
            'value' => 2,
        ]);
    }

    /**
     * レコーダ
     * 
     * - レコードの記録は、レコーダのrecordメソッドを使うことで削除できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコーダ
     */
    public function test_record_delete()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'int',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $record = $recorder->records()->create([
            'post' => $post,
            'value' => 1,
        ]);

        // 実行
        $record = $recorder->record($post, null);

        // 評価
        $this->assertNull($record, 'レコードの記録は、レコーダのrecordメソッドを使うことで削除できること');
        $this->assertDatabaseEmpty('records');
    }

    /**
     * レコード対象投稿
     * 
     * - レコーダによって記録されたレコードに紐付く投稿であることを確認します。
     * - 投稿オブジェクトを指定することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード対象投稿
     */
    public function test_record_post()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'int',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);

        // 実行
        $record = $recorder->records()->create([
            'post' => $post,
            'value' => 1,
        ]);

        // 評価
        $this->assertEquals($post->id, $record->post->id, '投稿オブジェクトを指定することができること');
        // レコーダによって記録されたレコードに紐付く投稿であること
        $this->assertDatabaseHas('records', [
            "id" => $record->id,
            'recordable_id' => $post->id,
            'recorder_id' => $recorder->id
        ]);
    }

    /**
     * レコード対象投稿
     * 
     * - レコーダによって記録されたレコードに紐付く投稿であることを確認します。
     * - 投稿IDを指定することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード対象投稿
     */
    public function test_record_post_id()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'int',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);

        // 実行
        $record = $recorder->records()->create([
            'recordable_id' => $post->id,
            'value' => 1,
        ]);

        // 評価
        $this->assertEquals($post->id, $record->recordable_id, '投稿IDを指定することができること');
        // レコーダによって記録されたレコードに紐付く投稿であること
        $this->assertDatabaseHas('records', [
            "id" => $record->id,
            'recordable_id' => $post->id,
            'recorder_id' => $recorder->id
        ]);
    }

    /**
     * レコード値
     * 
     * - レコーダによってレコード対象投稿毎に記録された値であることを確認します。
     * - 取得時にレコードデータ型に従って型変換が実行されることを確認します。
     * - stringのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_string()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'string',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $expected = 'テストレコード';

        // 実行
        $record = $recorder->record($post, $expected);

        // 評価
        $this->assertEquals($expected, $record->value, 'レコーダによってレコード対象投稿毎に記録された値であること');
        $this->assertTrue(is_string($record->value), '取得時にレコードデータ型に従って型変換が実行されること');
        $this->assertDatabaseHas('records', [
            "id" => $record->id,
            'recordable_id' => $post->id,
            'recorder_id' => $recorder->id,
            'value' => $expected
        ]);
    }

    /**
     * レコード値
     * 
     * - 設定時にレコードデータ型に従って型チェックが実行され、準拠しない値の場合にはエラーとなることを確認します。
     * - stringのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_string_invalid()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'string',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);

        // 評価
        $this->assertThrows(function () use ($recorder, $post) {
            $recorder->record($post, 1);
        }, ApplicationException::class, 'RecordValueDataTypeInvalid');
    }

    /**
     * レコード値
     * 
     * - レコーダによってレコード対象投稿毎に記録された値であることを確認します。
     * - 取得時にレコードデータ型に従って型変換が実行されることを確認します。
     * - intのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_int()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'int',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $expected = 1;

        // 実行
        $record = $recorder->record($post, $expected);

        // 評価
        $this->assertEquals($expected, $record->value, 'レコーダによってレコード対象投稿毎に記録された値であること');
        $this->assertTrue(is_int($record->value), '取得時にレコードデータ型に従って型変換が実行されること');
        $this->assertDatabaseHas('records', [
            "id" => $record->id,
            'recordable_id' => $post->id,
            'recorder_id' => $recorder->id,
            'value' => $expected
        ]);
    }

    /**
     * レコード値
     * 
     * - 設定時にレコードデータ型に従って型チェックが実行され、準拠しない値の場合にはエラーとなることを確認します。
     * - intのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_int_invalid()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'int',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);

        // 評価
        $this->assertThrows(function () use ($recorder, $post) {
            $recorder->record($post, 'テストレコード');
        }, ApplicationException::class, 'RecordValueDataTypeInvalid');
    }

    /**
     * レコード値
     * 
     * - レコーダによってレコード対象投稿毎に記録された値であることを確認します。
     * - 取得時にレコードデータ型に従って型変換が実行されることを確認します。
     * - integerのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_integer()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'integer',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $expected = 1;

        // 実行
        $record = $recorder->record($post, $expected);

        // 評価
        $this->assertEquals($expected, $record->value, 'レコーダによってレコード対象投稿毎に記録された値であること');
        $this->assertTrue(is_int($record->value), '取得時にレコードデータ型に従って型変換が実行されること');
        $this->assertDatabaseHas('records', [
            "id" => $record->id,
            'recordable_id' => $post->id,
            'recorder_id' => $recorder->id,
            'value' => $expected
        ]);
    }

    /**
     * レコード値
     * 
     * - 設定時にレコードデータ型に従って型チェックが実行され、準拠しない値の場合にはエラーとなることを確認します。
     * - integerのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_integer_invalid()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'integer',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);

        // 評価
        $this->assertThrows(function () use ($recorder, $post) {
            $recorder->record($post, 'テストレコード');
        }, ApplicationException::class, 'RecordValueDataTypeInvalid');
    }

    /**
     * レコード値
     * 
     * - レコーダによってレコード対象投稿毎に記録された値であることを確認します。
     * - 取得時にレコードデータ型に従って型変換が実行されることを確認します。
     * - floatのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_float()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'float',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $expected = 1.5;

        // 実行
        $record = $recorder->record($post, $expected);

        // 評価
        $this->assertEquals($expected, $record->value, 'レコーダによってレコード対象投稿毎に記録された値であること');
        $this->assertTrue(is_float($record->value), '取得時にレコードデータ型に従って型変換が実行されること');
        $this->assertDatabaseHas('records', [
            "id" => $record->id,
            'recordable_id' => $post->id,
            'recorder_id' => $recorder->id,
            'value' => $expected
        ]);
    }

    /**
     * レコード値
     * 
     * - 設定時にレコードデータ型に従って型チェックが実行され、準拠しない値の場合にはエラーとなることを確認します。
     * - floatのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_float_invalid()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'float',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);

        // 評価
        $this->assertThrows(function () use ($recorder, $post) {
            $recorder->record($post, 'テストレコード');
        }, ApplicationException::class, 'RecordValueDataTypeInvalid');
    }

    /**
     * レコード値
     * 
     * - レコーダによってレコード対象投稿毎に記録された値であることを確認します。
     * - 取得時にレコードデータ型に従って型変換が実行されることを確認します。
     * - doubleのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_double()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'double',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $expected = 1.56868757577576765868686878;

        // 実行
        $record = $recorder->record($post, $expected);

        // 評価
        $this->assertEquals($expected, $record->value, 'レコーダによってレコード対象投稿毎に記録された値であること');
        $this->assertTrue(is_double($record->value), '取得時にレコードデータ型に従って型変換が実行されること');
        $this->assertDatabaseHas('records', [
            "id" => $record->id,
            'recordable_id' => $post->id,
            'recorder_id' => $recorder->id,
            'value' => $expected
        ]);
    }

    /**
     * レコード値
     * 
     * - 設定時にレコードデータ型に従って型チェックが実行され、準拠しない値の場合にはエラーとなることを確認します。
     * - doubleのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_double_invalid()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'double',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);

        // 評価
        $this->assertThrows(function () use ($recorder, $post) {
            $recorder->record($post, 'テストレコード');
        }, ApplicationException::class, 'RecordValueDataTypeInvalid');
    }

    /**
     * レコード値
     * 
     * - レコーダによってレコード対象投稿毎に記録された値であることを確認します。
     * - 取得時にレコードデータ型に従って型変換が実行されることを確認します。
     * - boolのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_bool()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'bool',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $expected = true;

        // 実行
        $record = $recorder->record($post, $expected);

        // 評価
        $this->assertEquals($expected, $record->value, 'レコーダによってレコード対象投稿毎に記録された値であること');
        $this->assertTrue(is_bool($record->value), '取得時にレコードデータ型に従って型変換が実行されること');
        $this->assertDatabaseHas('records', [
            "id" => $record->id,
            'recordable_id' => $post->id,
            'recorder_id' => $recorder->id,
            'value' => 1
        ]);
    }

    /**
     * レコード値
     * 
     * - 設定時にレコードデータ型に従って型チェックが実行され、準拠しない値の場合にはエラーとなることを確認します。
     * - boolのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_bool_invalid()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'bool',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);

        // 評価
        $this->assertThrows(function () use ($recorder, $post) {
            $recorder->record($post, 1);
        }, ApplicationException::class, 'RecordValueDataTypeInvalid');
    }

    /**
     * レコード値
     * 
     * - レコーダによってレコード対象投稿毎に記録された値であることを確認します。
     * - 取得時にレコードデータ型に従って型変換が実行されることを確認します。
     * - booleanのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_boolean()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'boolean',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $expected = false;

        // 実行
        $record = $recorder->record($post, $expected);

        // 評価
        $this->assertEquals($expected, $record->value, 'レコーダによってレコード対象投稿毎に記録された値であること');
        $this->assertTrue(is_bool($record->value), '取得時にレコードデータ型に従って型変換が実行されること');
        $this->assertDatabaseHas('records', [
            "id" => $record->id,
            'recordable_id' => $post->id,
            'recorder_id' => $recorder->id,
            'value' => 0
        ]);
    }

    /**
     * レコード値
     * 
     * - 設定時にレコードデータ型に従って型チェックが実行され、準拠しない値の場合にはエラーとなることを確認します。
     * - booleanのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_boolean_invalid()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'boolean',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);

        // 評価
        $this->assertThrows(function () use ($recorder, $post) {
            $recorder->record($post, 0);
        }, ApplicationException::class, 'RecordValueDataTypeInvalid');
    }

    /**
     * レコード値
     * 
     * - レコーダによってレコード対象投稿毎に記録された値であることを確認します。
     * - 取得時にレコードデータ型に従って型変換が実行されることを確認します。
     * - dateのレコードデータ型がサポートされていることを確認します。
     * - 時刻は省略されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_date()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'date',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $expected = '2023-10-01';

        // 実行
        $record = $recorder->record($post, '2023-10-01 12:30:00');

        // 評価 
        $this->assertInstanceOf(Carbon::class, $record->value, '取得時にレコードデータ型に従って型変換が実行されること');
        $this->assertEquals($expected, $record->value->format('Y-m-d'), 'レコーダによってレコード対象投稿毎に記録された値であること');
        $this->assertDatabaseHas('records', [
            "id" => $record->id,
            'recordable_id' => $post->id,
            'recorder_id' => $recorder->id,
            'value' => $expected
        ]);
    }

    /**
     * レコード値
     * 
     * - 設定時にレコードデータ型に従って型チェックが実行され、準拠しない値の場合にはエラーとなることを確認します。
     * - dateのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_date_invalid()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'date',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);

        // 評価
        $this->assertThrows(function () use ($recorder, $post) {
            $recorder->record($post, 1);
        }, ApplicationException::class, 'RecordValueDataTypeInvalid');
    }

    /**
     * レコード値
     * 
     * - レコーダによってレコード対象投稿毎に記録された値であることを確認します。
     * - 取得時にレコードデータ型に従って型変換が実行されることを確認します。
     * - datetimeのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_datetime()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'datetime',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $expected = '2023-10-01 12:32:13';

        // 実行
        $record = $recorder->record($post, $expected);

        // 評価
        $this->assertInstanceOf(Carbon::class, $record->value, '取得時にレコードデータ型に従って型変換が実行されること');
        $this->assertEquals($expected, $record->value->format('Y-m-d H:i:s'), 'レコーダによってレコード対象投稿毎に記録された値であること');
        $this->assertDatabaseHas('records', [
            "id" => $record->id,
            'recordable_id' => $post->id,
            'recorder_id' => $recorder->id,
            'value' => $expected
        ]);
    }

    /**
     * レコード値
     * 
     * - レコーダによってレコード対象投稿毎に記録された値であることを確認します。
     * - 取得時にレコードデータ型に従って型変換が実行されることを確認します。
     * - datetimeのレコードデータ型がサポートされていることを確認します。
     * - 時刻が省略された場合は、00:00:00が補完されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_datetime_without_time()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'datetime',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $expected = '2023-10-01 00:00:00';

        // 実行
        $record = $recorder->record($post, '2023-10-01');

        // 評価
        $this->assertInstanceOf(Carbon::class, $record->value, '取得時にレコードデータ型に従って型変換が実行されること');
        $this->assertEquals($expected, $record->value->format('Y-m-d H:i:s'), 'レコーダによってレコード対象投稿毎に記録された値であること');
        $this->assertDatabaseHas('records', [
            "id" => $record->id,
            'recordable_id' => $post->id,
            'recorder_id' => $recorder->id,
            'value' => $expected
        ]);
    }

    /**
     * レコード値
     * 
     * - 設定時にレコードデータ型に従って型チェックが実行され、準拠しない値の場合にはエラーとなることを確認します。
     * - datetimeのレコードデータ型がサポートされていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコード値
     */
    public function test_record_value_datetime_invalid()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'datetime',
        ]);
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
        ]);

        // 評価
        $this->assertThrows(function () use ($recorder, $post) {
            $recorder->record($post, 'テストレコード');
        }, ApplicationException::class, 'RecordValueDataTypeInvalid');
    }
}
