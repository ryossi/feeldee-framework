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
}
