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
     * - カテゴリ階層をアップしたときは、カテゴリ表示順は、移動前に親カテゴリだったカテゴリの次に並ぶように調整されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層
     */
    public function test_hierarchyUp()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $rootCategory = Category::factory()->create([
            'profile' => $profile,
            'type' => Post::type(),
        ]);
        $categoryA = Category::factory()->create([
            'profile' => $profile,
            'type' => Post::type(),
            'parent' => $rootCategory,
        ]);
        $categoryB = Category::factory()->create([
            'profile' => $profile,
            'type' => Post::type(),
            'parent' => $categoryA,
        ]);

        // 実行
        $categoryB->hierarchyUp();

        // 評価
        $this->assertEquals(2, $categoryB->level, 'カテゴリ階層を一つ上げることができること');
        // カテゴリ表示順は、移動前に親カテゴリだったカテゴリの次に並ぶように調整されること
        $this->assertDatabaseHas('categories', [
            'id' => $categoryB->id,
            'parent_id' => $rootCategory->id,
            'order_number' => 2,
        ]);
    }

    /**
     * カテゴリ階層アップ
     * 
     * - ルートカテゴリはカテゴリ階層をアップすることはできないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層
     */
    public function test_hierarchyUp_root()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $rootCategory = Category::factory()->create([
            'profile' => $profile,
            'type' => Post::type(),
        ]);

        // 実行
        $rootCategory->hierarchyUp();

        // 評価
        $this->assertNull($rootCategory->parent, 'ルートカテゴリはカテゴリ階層をアップすることはできないこと');
    }

    /**
     * カテゴリ階層アップ
     * 
     * - ２階層目のカテゴリをアップして直接ルートカテゴリにすることもできないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層
     */
    public function test_hierarchyUp_2nd()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $rootCategory = Category::factory()->create([
            'profile' => $profile,
            'type' => Post::type(),
        ]);
        $categoryA = Category::factory()->create([
            'profile' => $profile,
            'type' => Post::type(),
            'parent' => $rootCategory,
        ]);

        // 実行
        $categoryA->hierarchyUp();

        // 評価
        $this->assertEquals(2, $categoryA->level, '２階層目のカテゴリをアップして直接ルートカテゴリにすることもできないこと');
        $this->assertDatabaseHas('categories', [
            'id' => $categoryA->id,
            'parent_id' => $rootCategory->id,
            'order_number' => 1,
        ]);
    }

    /**
     * カテゴリ階層ダウン
     * 
     * - カテゴリ階層を一つ下げることができることを確認します。
     * - カテゴリ階層をダウンしたときは、新たな親カテゴリは、移動前のカテゴリ階層のカテゴリ表示順で直前のカテゴリとなりることを確認します。
     * - 移動先のカテゴリ階層のカテゴリ表示順で最後に移動することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層
     */
    public function test_hierarchyDown()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $rootCategory = Category::factory()->create([
            'profile' => $profile,
            'type' => Post::type(),
        ]);
        $categoryA = Category::factory()->create([
            'profile' => $profile,
            'type' => Post::type(),
            'parent' => $rootCategory,
        ]);
        $categoryB = Category::factory()->create([
            'profile' => $profile,
            'type' => Post::type(),
            'parent' => $categoryA,
        ]);
        $categoryC = Category::factory()->create([
            'profile' => $rootCategory->profile,
            'type' => Post::type(),
            'parent' => $rootCategory,
        ]);

        // 実行
        $categoryC->hierarchyDown();

        // 評価
        $this->assertEquals(3, $categoryC->level, 'カテゴリ階層を一つ下げることができること');
        $this->assertEquals($categoryA->id, $categoryC->parent->id, '新たな親カテゴリは、移動前のカテゴリ階層のカテゴリ表示順で直前のカテゴリとなること');
        // 移動先のカテゴリ階層のカテゴリ表示順で最後に移動すること
        $this->assertDatabaseHas('categories', [
            'id' => $categoryC->id,
            'parent_id' => $categoryA->id,
            'order_number' => 2,
        ]);
    }

    /**
     * カテゴリ階層ダウン
     * 
     * - ルートカテゴリはカテゴリ階層をダウンすることはできないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層
     */
    public function test_hierarchyDown_root()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $rootCategory = Category::factory()->create([
            'profile' => $profile,
            'type' => Post::type(),
        ]);

        // 実行
        $rootCategory->hierarchyDown();

        // 評価
        $this->assertNull($rootCategory->parent, 'ルートカテゴリはカテゴリ階層をダウンすることはできないこと');
    }

    /**
     * カテゴリ階層ダウン
     * 
     * - カテゴリ表示順の先頭に位置するカテゴリのカテゴリ階層をダウンすることはできないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層
     */
    public function test_hierarchyDown_first()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $rootCategory = Category::factory()->create([
            'profile' => $profile,
            'type' => Post::type(),
        ]);
        $categoryA = Category::factory()->create([
            'profile' => $profile,
            'type' => Post::type(),
            'parent' => $rootCategory,
        ]);
        $categoryB = Category::factory()->create([
            'profile' => $profile,
            'type' => Post::type(),
            'parent' => $categoryA,
        ]);
        $categoryC = Category::factory()->create([
            'profile' => $rootCategory->profile,
            'type' => Post::type(),
            'parent' => $rootCategory,
        ]);


        // 実行
        $categoryA->hierarchyDown();

        // 評価
        $this->assertEquals(2, $categoryA->level, 'カテゴリ表示順の先頭に位置するカテゴリのカテゴリ階層をダウンすることはできないこと');
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
