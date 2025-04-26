<?php

namespace Tests\Feature;

use Auth;
use Exception;
use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Category;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
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
        $category = $profile->categories()->create([
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
            $profile->categories()->create([
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
        $category = $profile->categories()->create([
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
        $category = $profile->categories()->create([
            'name' => 'テストカテゴリ',
            'type' => Photo::type(),
        ]);

        // 評価
        $this->assertEquals(Photo::type(), $category->type, '写真のカテゴリタイプであること');
        $this->assertDatabaseHas('categories', [
            'type' => Photo::type(),
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
        $category = $profile->categories()->create([
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
        $category = $profile->categories()->create([
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
        $category = $profile->categories()->create([
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
            $profile->categories()->create([
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
            $profile->categories()->create([
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
        $category = $profile->categories()->create([
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
        $category = $otherProfile->categories()->create([
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
            'profile_id' => $profile->id,
            'type' => Post::type(),
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'parent_id' => $rootCategory->id,
        ]);
        $categoryB = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'parent_id' => $categoryA->id,
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
        $rootCategory = Category::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
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
            'profile_id' => $profile->id,
            'type' => Post::type(),
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'parent_id' => $rootCategory->id,
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
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'ルート',
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'parent_id' => $rootCategory->id,
            'name' => 'カテゴリA',
        ]);
        $categoryB = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'parent_id' => $categoryA->id,
            'name' => 'カテゴリB',
        ]);
        $categoryC = Category::factory()->create([
            'profile_id' => $rootCategory->profile->id,
            'type' => Post::type(),
            'parent_id' => $categoryA->id,
            'name' => 'カテゴリC',
        ]);
        $categoryD = Category::factory()->create([
            'profile_id' => $rootCategory->profile->id,
            'type' => Post::type(),
            'parent_id' => $categoryA->id,
            'name' => 'カテゴリD',
        ]);
        $categoryE = Category::factory()->create([
            'profile_id' => $rootCategory->profile->id,
            'type' => Post::type(),
            'parent_id' => $rootCategory->id,
            'name' => 'カテゴリE',
        ]);

        // 実行
        $categoryE->hierarchyDown();

        // 評価
        $this->assertEquals(3, $categoryE->level, 'カテゴリ階層を一つ下げることができること');
        $this->assertEquals($categoryA->id, $categoryE->parent->id, '新たな親カテゴリは、移動前のカテゴリ階層のカテゴリ表示順で直前のカテゴリとなること');
        // 移動先のカテゴリ階層のカテゴリ表示順で最後に移動すること
        $this->assertDatabaseHas('categories', [
            'id' => $categoryE->id,
            'parent_id' => $categoryA->id,
            'order_number' => 4,
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
            'profile_id' => $profile->id,
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
            'profile_id' => $profile->id,
            'type' => Post::type(),
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'parent_id' => $rootCategory->id,
        ]);


        // 実行
        $categoryA->hierarchyDown();

        // 評価
        $this->assertEquals(2, $categoryA->level, 'カテゴリ表示順の先頭に位置するカテゴリのカテゴリ階層をダウンすることはできないこと');
    }

    /**
     * カテゴリ入替
     * 
     * - 同一カテゴリ階層でカテゴリの入替ができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層
     */
    public function test_swap_hierarchy_same()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $rootCategory = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'parent_id' => $rootCategory->id,
        ]);
        $categoryB = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'parent_id' => $categoryA->id,
        ]);
        $categoryC = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'parent_id' => $rootCategory->id,
        ]);

        // 実行
        $categoryA->swap($categoryC);

        // 評価
        $this->assertEquals(2, $categoryA->level, '入替元カテゴリのカテゴリ階層レベルが維持されていること');
        $this->assertEquals(2, $categoryC->level, '対象カテゴリのカテゴリ階層レベルが維持されていること');
        $this->assertDatabaseHas('categories', [
            'id' => $categoryA->id,
            'parent_id' => $rootCategory->id,
            'order_number' => 2,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $categoryC->id,
            'parent_id' => $rootCategory->id,
            'order_number' => 1,
        ]);
    }

    /**
     * カテゴリ入替
     * 
     * - カテゴリ階層を跨いでも入替ができることを確認します。
     * - カテゴリ階層レベルが2以上異なるカテゴリどうしの入替ができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層
     */
    public function test_swap_hierarchy_ptn1()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $rootCategory = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'ルート'
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'カテゴリA',
            'parent_id' => $rootCategory->id,
        ]);
        $categoryB = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'カテゴリB',
            'parent_id' => $categoryA->id,
        ]);
        $categoryC = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'カテゴリC',
            'parent_id' => $rootCategory->id,
        ]);
        $categoryD = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'カテゴリD',
            'parent_id' => $categoryB->id,
        ]);

        // 実行
        $rootCategory->swap($categoryB);

        // 評価
        $this->assertEquals(3, $rootCategory->level, '入替元カテゴリのカテゴリ階層レベルが対象カテゴリと入れ替わっていること');
        $this->assertEquals(1, $categoryB->level, '対象カテゴリのカテゴリ階層レベルが入替元カテゴリと入れ替わっていること');
        $this->assertEquals($rootCategory->parent->id, $categoryA->id);
        $categoryD->refresh();
        $this->assertEquals($categoryD->parent->id, $rootCategory->id);
        $categoryA->refresh();
        $this->assertEquals($categoryA->parent->id, $categoryB->id);
        $this->assertDatabaseHas('categories', [
            'id' => $rootCategory->id,
            'parent_id' => $categoryA->id,
            'order_number' => 1,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $categoryA->id,
            'parent_id' => $categoryB->id,
            'order_number' => 1,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $categoryB->id,
            'parent_id' => null,
            'order_number' => 1,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $categoryC->id,
            'parent_id' => $categoryB->id,
            'order_number' => 2,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $categoryD->id,
            'parent_id' => $rootCategory->id,
            'order_number' => 1,
        ]);
    }

    /**
     * カテゴリ入替
     * 
     * - カテゴリ階層を跨いでも入替ができることを確認します。
     * - カテゴリ階層レベルが隣のカテゴリどうしを入替ができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層
     */
    public function test_swap_hierarchy_ptn2()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $rootCategory = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'ルート'
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'カテゴリA',
            'parent_id' => $rootCategory->id,
        ]);
        $categoryB = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'カテゴリB',
            'parent_id' => $categoryA->id,
        ]);
        $categoryC = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'カテゴリC',
            'parent_id' => $rootCategory->id,
        ]);
        $categoryD = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'カテゴリD',
            'parent_id' => $categoryB->id,
        ]);

        // 実行
        $categoryA->swap($categoryB);

        // 評価
        $this->assertEquals(3, $categoryA->level, '入替元カテゴリのカテゴリ階層レベルが対象カテゴリと入れ替わっていること');
        $this->assertEquals(2, $categoryB->level, '対象カテゴリのカテゴリ階層レベルが入替元カテゴリと入れ替わっていること');
        $categoryD->refresh();
        $this->assertEquals($categoryD->parent->id, $categoryA->id);
        $this->assertDatabaseHas('categories', [
            'id' => $rootCategory->id,
            'parent_id' => null,
            'order_number' => 1,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $categoryA->id,
            'parent_id' => $categoryB->id,
            'order_number' => 1,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $categoryB->id,
            'parent_id' => $rootCategory->id,
            'order_number' => 1,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $categoryC->id,
            'parent_id' => $rootCategory->id,
            'order_number' => 2,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $categoryD->id,
            'parent_id' => $categoryA->id,
            'order_number' => 1,
        ]);
    }

    /**
     * カテゴリ入替
     * 
     * - カテゴリ所有プロフィールが異なる場合は、入替できないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層
     */
    public function test_swap_different_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $rootCategory = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'ルート'
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'カテゴリA',
            'parent_id' => $rootCategory->id,
        ]);
        $otherProfile = Profile::factory()->create();
        $categoryB = Category::factory()->create([
            'profile_id' => $otherProfile->id,
            'type' => Post::type(),
            'name' => 'カテゴリB',
        ]);

        // 実行
        $this->assertThrows(function () use ($categoryA, $categoryB) {
            $categoryA->swap($categoryB);
        }, ApplicationException::class, 'CategorySwapProfileMissmatch');
    }

    /**
     * カテゴリ入替
     * 
     * - カテゴリタイプが異なる場合は、入替できないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層
     */
    public function test_swap_different_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $rootCategory = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'ルート'
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'カテゴリA',
            'parent_id' => $rootCategory->id,
        ]);
        $categoryB = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Item::type(),
            'name' => 'カテゴリB',
        ]);

        // 実行
        $this->assertThrows(function () use ($categoryA, $categoryB) {
            $categoryA->swap($categoryB);
        }, ApplicationException::class, 'CategorySwapTypeMissmatch');
    }

    /**
     * カテゴリ入替
     * 
     * - 同一カテゴリどうしの場合でもエラーとならないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層
     */
    public function test_swap_same_category()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $rootCategory = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'ルート'
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'カテゴリA',
            'parent_id' => $rootCategory->id,
        ]);

        // 実行
        $categoryA->swap($categoryA);

        // 評価
        $this->assertEquals(2, $categoryA->level, '同一カテゴリどうしの場合でもエラーとならないこと');
        $this->assertDatabaseHas('categories', [
            'id' => $rootCategory->id,
            'parent_id' => null,
            'order_number' => 1,
        ]);
        $this->assertDatabaseHas('categories', [
            'id' => $categoryA->id,
            'parent_id' => $rootCategory->id,
            'order_number' => 1,
        ]);
    }

    /**
     * 親カテゴリ
     * 
     * - カテゴリ階層構造の親となるカテゴリであることを確認します。
     * - 親カテゴリは、親カテゴリのIDであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#親カテゴリ
     */
    public function test_parent()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $parentCategory = $profile->categories()->create([
            'name' => '親カテゴリ',
            'type' => Post::type(),
        ]);
        $childCategory = $parentCategory->children()->create([
            'name' => '子カテゴリ',
        ]);

        // 評価
        $this->assertEquals($parentCategory->id, $childCategory->parent->id, '親カテゴリは、親カテゴリのIDであること');
        // 親カテゴリのIDが、子カテゴリの親カテゴリIDに設定されていること
        $this->assertDatabaseHas('categories', [
            'parent_id' => $parentCategory->id,
        ]);
    }

    /**
     * ルートカテゴリ
     * 
     * - 親カテゴリがない場合は、ルートカテゴリであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#ルートカテゴリ
     */
    public function test_root_category()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $category = $profile->categories()->create([
            'name' => 'ルートカテゴリ',
            'type' => Post::type(),
        ]);

        // 評価
        $this->assertTrue($category->isRoot, '親カテゴリがない場合は、ルートカテゴリであること');
    }

    /**
     * ルートカテゴリ
     * 
     * - 親カテゴリがある場合は、ルートカテゴリでないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#ルートカテゴリ
     */
    public function test_not_root_category()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $parentCategory = $profile->categories()->create([
            'name' => '親カテゴリ',
            'type' => Post::type(),
        ]);
        $childCategory = $parentCategory->children()->create([
            'name' => '子カテゴリ',
        ]);

        // 評価
        $this->assertFalse($childCategory->isRoot, '親カテゴリがある場合は、ルートカテゴリでないこと');
    }

    /**
     * 子カテゴリリスト
     * 
     * - 同じカテゴリを親にもつカテゴリのコレクションを取得できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#子カテゴリリスト
     */
    public function test_children()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()
            ->has(
                Category::factory(1, ['name' => '親カテゴリ', 'type' => Post::type()])
                    ->has(
                        Category::factory(3, ['type' => Post::type()]),
                        'children'
                    ),
                'categories'
            )->create();
        $category = Category::where('name', '親カテゴリ')->first();

        // 評価
        $this->assertEquals(3, $category->children->count());
        foreach ($category->children as $child) {
            $this->assertEquals($category->id, $child->parent_id, '同じカテゴリを親にもつカテゴリのコレクションを取得できること');
        }
    }

    /**
     * 子カテゴリリスト
     * 
     * - 子カテゴリリストに追加する全てのカテゴリは、親カテゴリのカテゴリ所有プロフィールと同じにしなければならないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#子カテゴリリスト
     */
    public function test_children_create_different_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $otherProfile = Profile::factory()->create();
        $category = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => '親カテゴリ'
        ]);
        $otherCategory = Category::factory()->create([
            'profile_id' => $otherProfile->id,
            'type' => Post::type(),
            'name' => '他カテゴリ'
        ]);

        // 実行
        $this->assertThrows(function () use ($category, $otherCategory) {
            $category->children()->save($otherCategory);
        }, ApplicationException::class, 'CategoryParentProfileMissmatch');
    }

    /**
     * 子カテゴリリスト
     * 
     * - 子カテゴリリストに追加する全てのカテゴリは、親カテゴリのカテゴリタイプと同じにしなければならないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#子カテゴリリスト
     */
    public function test_children_create_different_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => '親カテゴリ'
        ]);

        // 実行
        $this->assertThrows(function () use ($category) {
            $category->children()->create([
                'type' => Item::type(),
                'name' => '子カテゴリ'
            ]);
        }, ApplicationException::class, 'CategoryParentTypeMissmatch');
    }

    /**
     * 子カテゴリリスト
     * 
     * - 親カテゴリのカテゴリ所有プロフィールを継承していることを確認します。
     * - 親カテゴリのカテゴリタイプを継承していることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#子カテゴリリスト
     */
    public function test_children_create()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => '親カテゴリ'
        ]);

        // 実行
        $category->children()->create([
            'name' => '子カテゴリ1',
        ]);
        $category->children()->create([
            'name' => '子カテゴリ2',
        ]);

        // 評価
        $this->assertEquals(2, $category->children->count());
        foreach ($category->children as $child) {
            $this->assertEquals($category->profile, $child->profile, '親カテゴリのカテゴリ所有プロフィールを継承していること');
            $this->assertEquals($category->type, $child->type, '親カテゴリのカテゴリタイプを継承していること');
        }
    }


    /**
     * 子カテゴリリスト
     * 
     * - 子カテゴリが存在する場合は、hasChildがtrueであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#子カテゴリリスト
     */
    public function test_has_child()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()
            ->has(
                Category::factory(1, ['name' => '親カテゴリ', 'type' => Post::type()])
                    ->withChildren(
                        3
                    ),
                'categories'
            )->create();
        $category = Category::where('name', '親カテゴリ')->first();

        // 評価
        $this->assertTrue($category->hasChild, '子カテゴリが存在する場合は、hasChildがtrueであること');
    }

    /**
     * 子カテゴリリスト
     * 
     * - 子カテゴリが存在しない場合は、hasChildがfalseであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#子カテゴリリスト
     */
    public function test_not_has_child()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()
            ->has(
                Category::factory(1, ['name' => '親カテゴリ', 'type' => Post::type()]),
                'categories'
            )->create();
        $category = Category::where('name', '親カテゴリ')->first();

        // 評価
        $this->assertFalse($category->hasChild, '子カテゴリが存在しない場合は、hasChildがfalseであること');
    }

    /**
     * 子カテゴリリスト
     * 
     * - 子カテゴリが存在しないカテゴリは削除できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#子カテゴリリスト
     */
    public function test_delete_not_has_child()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()
            ->has(
                Category::factory(1, ['name' => '親カテゴリ', 'type' => Post::type()]),
                'categories'
            )->create();
        $category = Category::where('name', '親カテゴリ')->first();

        // 実行
        $condition = $category->delete();

        // 評価
        $this->assertTrue($condition, '子カテゴリが存在しないカテゴリは削除できること');
    }

    /**
     * 子カテゴリリスト
     * 
     * - 子カテゴリが存在する場合はカテゴリを削除できないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#子カテゴリリスト
     */
    public function test_delete_has_child()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()
            ->has(
                Category::factory(1, ['name' => '親カテゴリ', 'type' => Post::type()])
                    ->withChildren(
                        3
                    ),
                'categories'
            )->create();
        $category = Category::where('name', '親カテゴリ')->first();

        // 実行
        $this->assertThrows(function () use ($category) {
            $category->delete();
        }, ApplicationException::class, 'CategoryDeleteHasChild');
    }

    /**
     * カテゴリ表示順
     * 
     * - 同じカテゴリ階層内でのカテゴリの表示順を決定するための番号であることを確認します。
     * - 作成時に自動採番されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ表示順
     */
    public function test_order_number()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => '親カテゴリ'
        ]);

        // 実行
        $child1 = $category->children()->create([
            'name' => '子カテゴリ1',
        ]);
        $child2 = $category->children()->create([
            'name' => '子カテゴリ2',
        ]);
        $child3 = $category->children()->create([
            'name' => '子カテゴリ3',
        ]);

        // 評価
        $this->assertEquals(1, $child1->order_number, '階層構造をもつカテゴリは、同一階層の中での表示順を持つこと');
        $this->assertEquals(2, $child2->order_number, '階層構造をもつカテゴリは、同一階層の中での表示順を持つこと');
        $this->assertEquals(3, $child3->order_number, '階層構造をもつカテゴリは、同一階層の中での表示順を持つこと');
        // カテゴリ表示順は、作成時に自動採番されること
        foreach ($category->children as $child) {
            $this->assertDatabaseHas('categories', [
                'id' => $child->id,
                'order_number' => $child->order_number,
            ]);
        }
    }

    /**
     * カテゴリ表示順
     * 
     * - 同じカテゴリ階層内であれば、表示順で前のカテゴリに容易にアクセスすることができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ表示順
     */
    public function test_order_number_previous()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => '親カテゴリ'
        ]);
        $child1 = $category->children()->create([
            'name' => '子カテゴリ1',
        ]);
        $child2 = $category->children()->create([
            'name' => '子カテゴリ2',
        ]);

        // 実行
        $previousCategory = $child2->previous();

        // 評価
        $this->assertEquals($child1->id, $previousCategory->id, '同じカテゴリ階層内であれば、表示順で前のカテゴリに容易にアクセスすることができること');
    }

    /**
     * カテゴリ表示順
     * 
     * - 同じカテゴリ階層内であれば、表示順で後のカテゴリに容易にアクセスすることができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ表示順
     */
    public function test_order_number_next()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => '親カテゴリ'
        ]);
        $child1 = $category->children()->create([
            'name' => '子カテゴリ1',
        ]);
        $child2 = $category->children()->create([
            'name' => '子カテゴリ2',
        ]);

        // 実行
        $nextCategory = $child1->next();

        // 評価
        $this->assertEquals($child2->id, $nextCategory->id, '同じカテゴリ階層内であれば、表示順で後のカテゴリに容易にアクセスすることができること');
    }

    /**
     * カテゴリ表示順
     * 
     * - 直接編集しなくても同一カテゴリ階層で表示順を上へ移動することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ表示順
     */
    public function test_order_number_up()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => '親カテゴリ'
        ]);
        $child1 = $category->children()->create([
            'name' => '子カテゴリ1',
        ]);
        $child2 = $category->children()->create([
            'name' => '子カテゴリ2',
        ]);

        // 実行
        $child2->orderUp();

        // 評価
        $this->assertEquals(1, $child2->order_number, '同一カテゴリ階層で表示順を上へ移動することができること');
        $this->assertDatabaseHas('categories', [
            'id' => $child2->id,
            'order_number' => 1,
        ]);
        $child1->refresh();
        $this->assertEquals(2, $child1->order_number, '同一カテゴリ階層で表示順を上へ移動することができること');
        $this->assertDatabaseHas('categories', [
            'id' => $child1->id,
            'order_number' => 2,
        ]);
    }

    /**
     * カテゴリ表示順
     * 
     * - 直接編集しなくても同一カテゴリ階層で表示順を下へ移動することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ表示順
     */
    public function test_order_number_down()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => '親カテゴリ'
        ]);
        $child1 = $category->children()->create([
            'name' => '子カテゴリ1',
        ]);
        $child2 = $category->children()->create([
            'name' => '子カテゴリ2',
        ]);

        // 実行
        $child1->orderDown();

        // 評価
        $this->assertEquals(2, $child1->order_number, '同一カテゴリ階層で表示順を下へ移動することができること');
        $this->assertDatabaseHas('categories', [
            'id' => $child1->id,
            'order_number' => 2,
        ]);
        $child2->refresh();
        $this->assertEquals(1, $child2->order_number, '同一カテゴリ階層で表示順を下へ移動することができること');
        $this->assertDatabaseHas('categories', [
            'id' => $child2->id,
            'order_number' => 1,
        ]);
    }

    /**
     * コンテンツリスト
     * 
     * - カテゴリに分類分けされているコンテンツのコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#コンテンツリスト
     */
    public function test_contents()
    {
        //  準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
        ]);
        Post::factory()->count(3)->create([
            'profile_id' => $profile->id,
            'category_id' => $category->id,
            'is_public' => false,
        ]);
        $category->refresh();

        // 実行
        $contents = $category->contents;

        // 評価
        $this->assertEquals(3, $contents->count());
        foreach ($contents as $content) {
            $this->assertEquals($content->category->id, $category->id, 'カテゴリに分類分けされているコンテンツのリストであること');
        }
    }

    /**
     * カテゴリ階層連続リスト
     * 
     * - カテゴリ階層を親から子へ順番に直列化したカテゴリのコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ階層連続リスト
     */
    public function test_serials()
    {
        //  準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category1 = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'カテゴリ1',
        ]);
        $category2 = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'カテゴリ2',
            'parent_id' => $category1->id,
        ]);
        $category3 = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Post::type(),
            'name' => 'カテゴリ3',
            'parent_id' => $category2->id,
        ]);

        // 実行
        $serials = $category3->serials;

        // 評価
        $this->assertEquals(3, $serials->count());
        foreach ($serials as $index => $serial) {
            $this->assertEquals('カテゴリ' . ($index + 1), $serial->name, 'カテゴリ階層を親から子へ順番に直列化したカテゴリのコレクションであること');
        }
    }
}
