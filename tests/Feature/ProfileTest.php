<?php

namespace Tests\Feature;

use Feeldee\Framework\Models\Category;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Post;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\Tag;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

/**
 * プロフィールの用語を担保するための機能テストです。
 * 
 * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#プロフィール
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ユーザID
     * 
     * - プロフィールの所有者を特定するための数値型の外部情報であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#ユーザID
     */
    public function test_user_id()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->create(['user_id' => 100, 'nickname' => 'プロフィール100']);
        $expected = Profile::factory()->create(['user_id' => 200, 'nickname' => 'プロフィール200']);
        Profile::factory()->create(['user_id' => 300, 'nickname' => 'プロフィール300']);

        // 実行
        $profile = Profile::ofUserId($expected->user_id)->first();

        // 評価
        $this->assertEquals($expected->nickname, $profile->nickname, 'プロフィールの所有者を特定するための数値型の外部情報であること');
    }

    /**
     * ユーザID
     * 
     * - プロフィールの所有者を特定するための数値型の外部情報であることを確認します。
     * - Laravel標準の認証システムのAuth::id()の値を設定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#ユーザID
     */
    public function test_user_id_laravel_auth()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(153893094);
        $user_id = Auth::id();

        // 実行
        $profile = Profile::create([
            'user_id' => Auth::id(),
            'nickname' => 'テストプロフィール',
            'title' => 'ユーザIDテスト'
        ]);

        // 評価
        $this->assertEquals($user_id, $profile->user_id, 'Laravel標準の認証システムのAuth::id()の値を設定できること');
    }

    /**
     * カテゴリリスト
     * 
     * - プロフィールに紐付けられたカテゴリのコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#カテゴリリスト
     */
    public function test_categories()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Category::factory(2))->create();

        // 評価
        $this->assertEquals(2, $profile->categories->count());
        foreach ($profile->categories as $category) {
            $this->assertEquals($category->profile->id, $profile->id, 'プロフィールに紐付けられたカテゴリのコレクションであること');
        }
    }

    /**
     * タグリリスト
     * 
     * - プロフィールに紐付けられたタグのコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#タグリスト
     */
    public function test_tags()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Tag::factory(3))->create();

        // 評価
        $this->assertEquals(3, $profile->tags->count());
        foreach ($profile->tags as $tag) {
            $this->assertEquals($tag->profile->id, $profile->id, 'プロフィールに紐付けられたタグのコレクションであること');
        }
    }

    /**
     * 投稿リスト
     * 
     * - プロフィールに紐付けられた投稿のコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#投稿リスト
     */
    public function test_posts()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(2))->create();

        // 評価
        $this->assertEquals(2, $profile->posts->count());
        foreach ($profile->posts as $post) {
            $this->assertEquals($post->profile->id, $profile->id, 'プロフィールに紐付けられた投稿のコレクションであること');
        }
    }

    /**
     * 写真リスト
     * 
     * - プロフィールに紐付けられた投稿のコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#写真リスト
     */
    public function test_photos()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Photo::factory(3))->create();

        // 評価
        $this->assertEquals(3, $profile->photos->count());
        foreach ($profile->photos as $photo) {
            $this->assertEquals($photo->profile->id, $profile->id, 'プロフィールに紐付けられた写真のコレクションであること');
        }
    }

    /**
     * 場所リスト
     * 
     * - プロフィールに紐付けられた場所のコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#場所リスト
     */
    public function test_locations()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Location::factory(5))->create();

        // 評価
        $this->assertEquals(5, $profile->locations->count());
        foreach ($profile->locations as $location) {
            $this->assertEquals($location->profile->id, $profile->id, 'プロフィールに紐付けられた場所のコレクションであること');
        }
    }

    /**
     * アイテムリスト
     * 
     * - プロフィールに紐付けられたアイテムのコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#アイテムリスト
     */
    public function test_items()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory(2))->create();

        // 評価
        $this->assertEquals(2, $profile->items->count());
        foreach ($profile->items as $item) {
            $this->assertEquals($item->profile->id, $profile->id, 'プロフィールに紐付けられたアイテムのコレクションであること');
        }
    }
}
