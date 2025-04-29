<?php

namespace Tests\Feature;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Post;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /**
     * タグ所有プロフィール
     * 
     * - タグを作成したユーザのプロフィールであることを確認します。
     * - プロフィールのIDが、タグ所有プロフィールIDに設定されていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ所有プロフィール
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag = $profile->tags()->create([
            'name' => 'テストタグ',
            'type' => Post::type(),
        ]);

        // 評価
        $this->assertEquals($profile->id, $tag->profile->id, 'タグを作成したユーザのプロフィールであること');
        // プロフィールのIDが、タグ所有プロフィールIDに設定されていること
        $this->assertDatabaseHas('tags', [
            'profile_id' => $profile->id,
        ]);
    }

    /** 
     * タグ所有プロフィール
     * 
     * - タグ所有プロフィールは必須項目であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ所有プロフィール
     */
    public function test_profile_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);

        // 実行
        $this->assertThrows(function () {
            Tag::create([
                'name' => 'テストタグ',
                'type' => Post::type(),
            ]);
        }, ApplicationException::class, 'TagProfileRequired');
    }

    /**
     * タグタイプ
     * 
     * - タグタイプは必須項目であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグタイプ
     */
    public function test_type_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->tags()->create([
                'name' => 'テストタグ',
            ]);
        }, ApplicationException::class, 'TagTypeRequired');
    }

    /**
     * タグタイプ
     * 
     * - 投稿のタグは、投稿のタグタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグタイプ
     */
    public function test_type_post()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag = $profile->categories()->create([
            'name' => 'テストタグ',
            'type' => Post::type(),
        ]);

        // 評価
        $this->assertEquals(Post::type(), $tag->type, '投稿のタグタイプであること');
        $this->assertDatabaseHas('categories', [
            'type' => Post::type(),
        ]);
    }

    /**
     * タグタイプ
     * 
     * - 写真のタグは、写真のタグタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグタイプ
     */
    public function test_type_photo()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag = $profile->categories()->create([
            'name' => 'テストタグ',
            'type' => Photo::type(),
        ]);

        // 評価
        $this->assertEquals(Photo::type(), $tag->type, '写真のタグタイプであること');
        $this->assertDatabaseHas('categories', [
            'type' => Photo::type(),
        ]);
    }

    /**
     * タグタイプ
     * 
     * - 場所のタグは、場所のタグタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグタイプ
     */
    public function test_type_location()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag = $profile->categories()->create([
            'name' => 'テストタグ',
            'type' => Location::type(),
        ]);

        // 評価
        $this->assertEquals(Location::type(), $tag->type, '場所のタグタイプであること');
        $this->assertDatabaseHas('categories', [
            'type' => Location::type(),
        ]);
    }

    /**
     * タグタイプ
     * 
     * - アイテムのタグは、アイテムのタグタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグタイプ
     */
    public function test_type_item()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag = $profile->categories()->create([
            'name' => 'テストタグ',
            'type' => Item::type(),
        ]);

        // 評価
        $this->assertEquals(Item::type(), $tag->type, 'アイテムのタグタイプであること');
        $this->assertDatabaseHas('categories', [
            'type' => Item::type(),
        ]);
    }

    /**
     * タグ名
     * 
     * - タグの名前であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ名
     */
    public function test_name()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag = $profile->tags()->create([
            'name' => 'テストタグ',
            'type' => Post::type(),
        ]);

        // 評価
        $this->assertEquals('テストタグ', $tag->name, 'タグの名前であること');
        $this->assertDatabaseHas('tags', [
            'name' => 'テストタグ',
        ]);
    }

    /**
     * タグ名
     * 
     * - タグ名は必須項目であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ名
     */
    public function test_name_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->tags()->create([
                'type' => Post::type(),
            ]);
        }, ApplicationException::class, 'TagNameRequired');
    }

    /**
     * タグ名
     * 
     * - タグ所有プロフィールとタグタイプの中でユニークであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ名
     */
    public function test_name_unique()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Tag::factory(1, ['name' => 'テストタグ', 'type' => Post::type()]))->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->tags()->create([
                'name' => 'テストタグ',
                'type' => Post::type(),
            ]);
        }, ApplicationException::class, 'TagNameDuplicated');
    }

    /**
     * タグ名
     * 
     * - タグ所有プロフィールとタグタイプの中でユニークであることを確認します。
     * - タグタイプが異なる場合は、登録できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ名
     */
    public function test_name_unique_with_different_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Tag::factory(1, ['name' => 'テストタグ', 'type' => Post::type()]))->create();

        // 実行
        $tag = $profile->categories()->create([
            'name' => 'テストタグ',
            'type' => Item::type(),
        ]);

        // 評価
        $this->assertEquals('テストタグ', $tag->name, 'タグタイプが異なる場合は、登録できること');
        $this->assertDatabaseHas('categories', [
            'name' => 'テストタグ',
            'type' => Item::type(),
        ]);
    }

    /**
     * タグ名
     * 
     * - タグ所有プロフィールとタグタイプの中でユニークであることを確認します。
     * - タグ所有プロフィールが異なる場合は、登録できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ名
     */
    public function test_name_unique_with_different_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(Tag::factory(1, ['name' => 'テストタグ', 'type' => Post::type()]))->create();
        $otherProfile = Profile::factory()->create();

        // 実行
        $tag = $otherProfile->categories()->create([
            'name' => 'テストタグ',
            'type' => Post::type(),
        ]);

        // 評価
        $this->assertEquals('テストタグ', $tag->name, 'タグ所有プロフィールが異なる場合は、登録できること');
        $this->assertDatabaseHas('categories', [
            'name' => 'テストタグ',
            'type' => Post::type(),
            'profile_id' => $otherProfile->id,
        ]);
    }

    /**
     * タグ表示順
     * 
     * - 同じタグ所有プロフィール、タグタイプでタグの表示順を決定するための番号であることを確認します。
     * - 作成時に自動採番されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ表示順
     */
    public function test_order_number()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Post::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Post::type(),
        ]);
        $tag3 = $profile->tags()->create([
            'name' => 'タグ3',
            'type' => Post::type(),
        ]);

        // 評価
        $this->assertEquals(1, $tag1->order_number, '同じタグ所有プロフィール、タグタイプの中での表示順を持つこと');
        $this->assertEquals(2, $tag2->order_number, '同じタグ所有プロフィール、タグタイプの中での表示順を持つこと');
        $this->assertEquals(3, $tag3->order_number, '同じタグ所有プロフィール、タグタイプの中での表示順を持つこと');
        // タグ表示順は、作成時に自動採番されること
        foreach ($profile->tags as $tag) {
            $this->assertDatabaseHas('tags', [
                'id' => $tag->id,
                'order_number' => $tag->order_number,
            ]);
        }
    }

    /**
     * タグ表示順
     * 
     * - 表示順で前のタグに容易にアクセスすることができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ表示順
     */
    public function test_order_number_previous()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Post::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Post::type(),
        ]);

        // 実行
        $previousTag = $tag2->previous();

        // 評価
        $this->assertEquals($tag1->id, $previousTag->id, '表示順で前のタグに容易にアクセスすることができること');
    }

    /**
     * タグ表示順
     * 
     * - 表示順で後のタグに容易にアクセスすることができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ表示順
     */
    public function test_order_number_next()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Post::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Post::type(),
        ]);

        // 実行
        $nextTag = $tag1->next();

        // 評価
        $this->assertEquals($tag2->id, $nextTag->id, '表示順で後のタグに容易にアクセスすることができること');
    }

    /**
     * タグ表示順
     * 
     * - 直接編集しなくても同じタグ所有プロフィール、タグタイプの中で表示順を上へ移動することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ表示順
     */
    public function test_order_number_up()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Post::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Post::type(),
        ]);

        // 実行
        $tag2->orderUp();

        // 評価
        $this->assertEquals(1, $tag2->order_number, '同じタグ所有プロフィール、タグタイプの中で表示順を上へ移動することができること');
        $this->assertDatabaseHas('tags', [
            'id' => $tag2->id,
            'order_number' => 1,
        ]);
        $tag1->refresh();
        $this->assertEquals(2, $tag1->order_number, '同じタグ所有プロフィール、タグタイプの中で表示順を上へ移動することができること');
        $this->assertDatabaseHas('tags', [
            'id' => $tag1->id,
            'order_number' => 2,
        ]);
    }

    /**
     * タグ表示順
     * 
     * - 直接編集しなくても同じタグ所有プロフィール、タグタイプの中で表示順を下へ移動することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ表示順
     */
    public function test_order_number_down()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Post::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Post::type(),
        ]);

        // 実行
        $tag1->orderDown();

        // 評価
        $this->assertEquals(2, $tag1->order_number, '同じタグ所有プロフィール、タグタイプの中で表示順を下へ移動することができること');
        $this->assertDatabaseHas('tags', [
            'id' => $tag1->id,
            'order_number' => 2,
        ]);
        $tag2->refresh();
        $this->assertEquals(1, $tag2->order_number, '同じタグ所有プロフィール、タグタイプの中で表示順を下へ移動することができること');
        $this->assertDatabaseHas('tags', [
            'id' => $tag2->id,
            'order_number' => 1,
        ]);
    }

    /**
     * コンテンツリスト
     * 
     * - タグ付けされているコンテンツのコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#コンテンツリスト
     */
    public function test_contents()
    {
        //  準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag = $profile->tags()->create([
            'name' => 'テストタグ',
            'type' => Post::type(),
            'contents' => Post::factory(3)->create(['profile_id' => $profile->id]),
        ]);

        // 評価
        $this->assertEquals(3, $tag->contents->count());
        foreach ($tag->contents as $content) {
            $this->assertEquals($content->tags()->first()->id, $tag->id, 'タグ付けされているコンテンツのリストであること');
        }
    }

    /**
     * コンテンツリスト
     * 
     * - コンテンツリストに直接コンテンツのコレクションを指定する場合、タグ所有プロフィールがコンテンツ所有プロフィールと一致している必要があることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#コンテンツリスト
     */
    public function test_contents_with_different_profile()
    {
        //  準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $otherProfile = Profile::factory()->create();

        // 評価
        $this->assertThrows(function () use ($profile, $otherProfile) {
            // 実行
            $profile->tags()->create([
                'name' => 'テストタグ',
                'type' => Post::type(),
                'contents' => Post::factory(3)->create(['profile_id' => $otherProfile->id]),
            ]);
        }, ApplicationException::class, 'TagContentProfileMissmatch');
    }

    /**
     * コンテンツリスト
     * 
     * - コンテンツリストに直接コンテンツのコレクションを指定する場合、タグタイプがコンテンツ種別と一致している必要があることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#コンテンツリスト
     */
    public function test_contents_with_different_type()
    {
        //  準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 評価
        $this->assertThrows(function () use ($profile) {
            // 実行
            $profile->tags()->create([
                'name' => 'テストタグ',
                'type' => Post::type(),
                'contents' => Item::factory(1)->create(['profile_id' => $profile->id]),
            ]);
        }, ApplicationException::class, 'TagContentTypeMissmatch');
    }
}
