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

    /**
     * カテゴリ階層アップ
     * 
     * - カテゴリ階層を一つ上げることができることを確認します。
     * - 移動先階層のカテゴリ表示順で最後に移動することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層
     */
    public function test_hierarchyUp()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()
            ->has(
                Category::factory(1, ['name' => 'ルートカテゴリ', 'type' => Post::type()])
                    ->has(
                        Category::factory(1, ['name' => '2階層カテゴリ', 'type' => Post::type()])->for(Profile::factory())->has(
                            Category::factory(1, ['name' => '3階層カテゴリ', 'type' => Post::type()])->for(Profile::factory()),
                            'children'
                        ),
                        'children'
                    ),
                'categories'
            )->create();
        $rootCategory = Category::where('name', 'ルートカテゴリ')->first();
        $level3Category = Category::where('name', '3階層カテゴリ')->first();

        // 実行
        $level3Category->hierarchyUp();

        // 評価
        $this->assertEquals($rootCategory, $level3Category->parent, 'カテゴリ階層を一つ上げることができること');
        // 移動先階層のカテゴリ表示順で最後に移動することができること
        $this->assertDatabaseHas('categories', [
            'id' => $level3Category->id,
            'parent_id' => $rootCategory->id,
            'order_number' => 2,
        ]);
    }

    /**
     * カテゴリ階層アップ
     * 
     * - ルートカテゴリはカテゴリ階層を上げることができないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層
     */
    public function test_hierarchyUp_root()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()
            ->has(
                Category::factory(1, ['name' => 'ルートカテゴリ', 'type' => Post::type()]),
                'categories'
            )->create();
        $rootCategory = Category::where('name', 'ルートカテゴリ')->first();

        // 実行
        $rootCategory->hierarchyUp();

        // 評価
        $this->assertNull($rootCategory->parent, 'ルートカテゴリはカテゴリ階層を上げることができないこと');
    }

    /**
     * カテゴリ階層アップ
     * 
     * - ２階層目のカテゴリをルートカテゴリに移動することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層
     */
    public function test_hierarchyUp_2nd()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()
            ->has(
                Category::factory(1, ['name' => 'ルートカテゴリ', 'type' => Post::type()])
                    ->has(
                        Category::factory(1, ['name' => '2階層カテゴリ', 'type' => Post::type()])->for(Profile::factory()),
                        'children'
                    ),
                'categories'
            )->create();
        $rootCategory = Category::where('name', 'ルートカテゴリ')->first();
        $level2Category = Category::where('name', '2階層カテゴリ')->first();

        // 実行
        $level2Category->hierarchyUp();

        // 評価
        $this->assertEquals($rootCategory, $level2Category->parent, '２階層目のカテゴリをルートカテゴリに移動すること');
        $this->assertDatabaseHas('categories', [
            'id' => $level2Category->id,
            'parent_id' => $rootCategory->id,
            'order_number' => 1,
        ]);
    }

    /**
     * 親カテゴリ
     * 
     * - カテゴリ階層構造の親となるカテゴリであることを確認します。
     * - 親カテゴリは、親カテゴリのIDであることを確認します。
     * - 親をもつカテゴリは、ルートカテゴリでないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#親カテゴリ
     */
    public function test_parent()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $parentCategory = Category::create([
            'profile' => $profile,
            'name' => '親カテゴリ',
            'type' => Post::type(),
        ]);
        $childCategory = Category::create([
            'profile' => $profile,
            'name' => '子カテゴリ',
            'type' => Post::type(),
            'parent' => $parentCategory,
        ]);

        // 評価
        $this->assertEquals($parentCategory->id, $childCategory->parent->id, '親カテゴリは、親カテゴリのIDであること');
        // 親カテゴリのIDが、子カテゴリの親カテゴリIDに設定されていること
        $this->assertDatabaseHas('categories', [
            'parent_id' => $parentCategory->id,
        ]);
        $this->assertFalse($childCategory->isRoot, '親をもつカテゴリは、ルートカテゴリでないこと');
    }

    /**
     * 親カテゴリ
     * 
     * - 親をもたないカテゴリは、ルートカテゴリであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#親カテゴリ
     */
    public function test_parent_root()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $category = Category::create([
            'profile' => $profile,
            'name' => 'ルートカテゴリ',
            'type' => Post::type(),
        ]);

        // 評価
        $this->assertTrue($category->isRoot, '親をもたないカテゴリは、ルートカテゴリであること');
        $this->assertDatabaseHas('categories', [
            'parent_id' => null,
        ]);
    }
}
