<?php

namespace Tests\Feature;

use Auth;
use Exception;
use Feeldee\Framework\Models\Category;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Post;
use Feeldee\Framework\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * カテゴリ所有プロフィール
     * 
     * - カテゴリを作成したユーザのプロフィールであることを確認します。
     * - プロフィールのIDが、カテゴリ所有プロフィールIDに設定されていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ所有プロフィール
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $category = Category::create([
            'profile' => $profile,
            'name' => 'テストカテゴリ',
            'type' => Post::type(),
        ]);

        // 評価
        $this->assertEquals($profile->id, $category->profile->id, 'カテゴリを作成したユーザのプロフィールであること');
        // プロフィールのIDが、カテゴリ所有プロフィールIDに設定されていること
        $this->assertDatabaseHas('categories', [
            'profile_id' => $profile->id,
        ]);
    }

    /** 
     * カテゴリ所有プロフィール
     * 
     * - カテゴリ所有プロフィールは必須項目であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ所有プロフィール
     */
    public function test_profile_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);

        // 実行
        $this->assertThrows(function () {
            Category::create([
                'name' => 'テストカテゴリ',
                'type' => Post::type(),
            ]);
        }, Exception::class);
    }

    /**
     * カテゴリタイプ
     * 
     * - カテゴリタイプは必須項目であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリタイプ
     */
    public function test_type_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            Category::create([
                'profile' => $profile,
                'name' => 'テストカテゴリ',
            ]);
        }, Exception::class);
    }

    /**
     * カテゴリタイプ
     * 
     * - 投稿のカテゴリは、投稿のカテゴリタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリタイプ
     */
    public function test_type_post()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $category = Category::create([
            'profile' => $profile,
            'name' => 'テストカテゴリ',
            'type' => Post::type(),
        ]);

        // 評価
        $this->assertEquals(Post::type(), $category->type, '投稿のカテゴリタイプであること');
        $this->assertDatabaseHas('categories', [
            'type' => Post::type(),
        ]);
    }

    /**
     * カテゴリタイプ
     * 
     * - 写真のカテゴリは、写真のカテゴリタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリタイプ
     */
    public function test_type_photo()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $category = Category::create([
            'profile' => $profile,
            'name' => 'テストカテゴリ',
            'type' => Post::type(),
        ]);

        // 評価
        $this->assertEquals(Post::type(), $category->type, '写真のカテゴリタイプであること');
        $this->assertDatabaseHas('categories', [
            'type' => Post::type(),
        ]);
    }

    /**
     * カテゴリタイプ
     * 
     * - 場所のカテゴリは、場所のカテゴリタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリタイプ
     */
    public function test_type_location()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $category = Category::create([
            'profile' => $profile,
            'name' => 'テストカテゴリ',
            'type' => Location::type(),
        ]);

        // 評価
        $this->assertEquals(Location::type(), $category->type, '場所のカテゴリタイプであること');
        $this->assertDatabaseHas('categories', [
            'type' => Location::type(),
        ]);
    }

    /**
     * カテゴリタイプ
     * 
     * - アイテムのカテゴリは、アイテムのカテゴリタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリタイプ
     */
    public function test_type_item()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $category = Category::create([
            'profile' => $profile,
            'name' => 'テストカテゴリ',
            'type' => Item::type(),
        ]);

        // 評価
        $this->assertEquals(Item::type(), $category->type, 'アイテムのカテゴリタイプであること');
        $this->assertDatabaseHas('categories', [
            'type' => Item::type(),
        ]);
    }

    /**
     * カテゴリ名
     * 
     * - カテゴリの名前であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ名
     */
    public function test_name()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $category = Category::create([
            'profile' => $profile,
            'name' => 'テストカテゴリ',
            'type' => Post::type(),
        ]);

        // 評価
        $this->assertEquals('テストカテゴリ', $category->name, 'カテゴリの名前であること');
        $this->assertDatabaseHas('categories', [
            'name' => 'テストカテゴリ',
        ]);
    }

    /**
     * カテゴリ名
     * 
     * - カテゴリ名は必須項目であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ名
     */
    public function test_name_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            Category::create([
                'profile' => $profile,
                'type' => Post::type(),
            ]);
        }, Exception::class);
    }

    /**
     * カテゴリ名
     * 
     * - カテゴリ所有プロフィールとカテゴリタイプの中でユニークであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ名
     */
    public function test_name_unique()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Category::factory(1, ['name' => 'テストカテゴリ', 'type' => Post::type()]))->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            Category::create([
                'profile' => $profile,
                'name' => 'テストカテゴリ',
                'type' => Post::type(),
            ]);
        }, Exception::class);
    }

    /**
     * カテゴリ名
     * 
     * - カテゴリ所有プロフィールとカテゴリタイプの中でユニークであることを確認します。
     * - カテゴリタイプが異なる場合は、登録できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ名
     */
    public function test_name_unique_with_different_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Category::factory(1, ['name' => 'テストカテゴリ', 'type' => Post::type()]))->create();

        // 実行
        $category = Category::create([
            'profile' => $profile,
            'name' => 'テストカテゴリ',
            'type' => Item::type(),
        ]);

        // 評価
        $this->assertEquals('テストカテゴリ', $category->name, 'カテゴリタイプが異なる場合は、登録できること');
        $this->assertDatabaseHas('categories', [
            'name' => 'テストカテゴリ',
            'type' => Item::type(),
        ]);
    }

    /**
     * カテゴリ名
     * 
     * - カテゴリ所有プロフィールとカテゴリタイプの中でユニークであることを確認します。
     * - カテゴリ所有プロフィールが異なる場合は、登録できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ名
     */
    public function test_name_unique_with_different_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(Category::factory(1, ['name' => 'テストカテゴリ', 'type' => Post::type()]))->create();
        $otherProfile = Profile::factory()->create();

        // 実行
        $category = Category::create([
            'profile' => $otherProfile,
            'name' => 'テストカテゴリ',
            'type' => Post::type(),
        ]);

        // 評価
        $this->assertEquals('テストカテゴリ', $category->name, 'カテゴリ所有プロフィールが異なる場合は、登録できること');
        $this->assertDatabaseHas('categories', [
            'name' => 'テストカテゴリ',
            'type' => Post::type(),
            'profile_id' => $otherProfile->id,
        ]);
    }
}
