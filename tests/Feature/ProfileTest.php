<?php

namespace Tests\Feature;

use Feeldee\Framework\Models\Category;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Post;
use Feeldee\Framework\Models\Profile;
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
