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
}
