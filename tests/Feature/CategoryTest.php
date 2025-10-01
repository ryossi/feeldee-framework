<?php

namespace Tests\Feature;

use Auth;
use Exception;
use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Category;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Journal;
use Feeldee\Framework\Models\Like;
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
            'type' => Journal::type(),
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
                'type' => Journal::type(),
            ]);
        }, ApplicationException::class, 'CategoryProfileRequired');
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
        }, ApplicationException::class, 'CategoryTypeRequired');
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
            'type' => Journal::type(),
        ]);

        // 評価
        $this->assertEquals(Journal::type(), $category->type, '投稿のカテゴリタイプであること');
        $this->assertDatabaseHas('categories', [
            'type' => Journal::type(),
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
            'type' => Journal::type(),
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
                'type' => Journal::type(),
            ]);
        }, ApplicationException::class, 'CategoryNameRequired');
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
        $profile = Profile::factory()->has(Category::factory(1, ['name' => 'テストカテゴリ', 'type' => Journal::type()]))->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->categories()->create([
                'name' => 'テストカテゴリ',
                'type' => Journal::type(),
            ]);
        }, ApplicationException::class, 'CategoryNameDuplicated');
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
        $profile = Profile::factory()->has(Category::factory(1, ['name' => 'テストカテゴリ', 'type' => Journal::type()]))->create();

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
        Profile::factory()->has(Category::factory(1, ['name' => 'テストカテゴリ', 'type' => Journal::type()]))->create();
        $otherProfile = Profile::factory()->create();

        // 実行
        $category = $otherProfile->categories()->create([
            'name' => 'テストカテゴリ',
            'type' => Journal::type(),
        ]);

        // 評価
        $this->assertEquals('テストカテゴリ', $category->name, 'カテゴリ所有プロフィールが異なる場合は、登録できること');
        $this->assertDatabaseHas('categories', [
            'name' => 'テストカテゴリ',
            'type' => Journal::type(),
            'profile_id' => $otherProfile->id,
        ]);
    }

    /**
     * カテゴリイメージ
     * 
     * - カテゴリのイメージ画像であることを確認します。
     * - URL形式で保存できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリイメージ
     */
    public function test_image_url()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $image = '/path/to/image.jpg';

        // 実行
        $category = $profile->categories()->create([
            'name' => 'テストカテゴリ',
            'type' => Journal::type(),
            'image' => $image,
        ]);

        // 検証
        $this->assertEquals($image, $category->image, 'カテゴリのイメージ画像であること');
        // URL形式で保存できること
        $this->assertDatabaseHas('categories', [
            'image' => $image,
        ]);
    }

    /**
     * カテゴリイメージ
     * 
     * - カテゴリのイメージ画像であることを確認します。
     * - Base64形式で保存できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリイメージ
     */
    public function test_image_base64()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $image = 'data:image/jpeg;base64,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';

        // 実行
        $category = $profile->categories()->create([
            'name' => 'テストカテゴリ',
            'type' => Journal::type(),
            'image' => $image,
        ]);

        // 検証
        $this->assertEquals($image, $category->image, 'カテゴリのイメージ画像であること');
        // Base64形式で保存できること
        $this->assertDatabaseHas('categories', [
            'image' => $image,
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
            'type' => Journal::type(),
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'parent_id' => $rootCategory->id,
        ]);
        $categoryB = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
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
            'type' => Journal::type(),
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
            'type' => Journal::type(),
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
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
            'type' => Journal::type(),
            'name' => 'ルート',
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'parent_id' => $rootCategory->id,
            'name' => 'カテゴリA',
        ]);
        $categoryB = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'parent_id' => $categoryA->id,
            'name' => 'カテゴリB',
        ]);
        $categoryC = Category::factory()->create([
            'profile_id' => $rootCategory->profile->id,
            'type' => Journal::type(),
            'parent_id' => $categoryA->id,
            'name' => 'カテゴリC',
        ]);
        $categoryD = Category::factory()->create([
            'profile_id' => $rootCategory->profile->id,
            'type' => Journal::type(),
            'parent_id' => $categoryA->id,
            'name' => 'カテゴリD',
        ]);
        $categoryE = Category::factory()->create([
            'profile_id' => $rootCategory->profile->id,
            'type' => Journal::type(),
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
            'type' => Journal::type(),
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
            'type' => Journal::type(),
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
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
            'type' => Journal::type(),
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'parent_id' => $rootCategory->id,
        ]);
        $categoryB = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'parent_id' => $categoryA->id,
        ]);
        $categoryC = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
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
            'type' => Journal::type(),
            'name' => 'ルート'
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'name' => 'カテゴリA',
            'parent_id' => $rootCategory->id,
        ]);
        $categoryB = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'name' => 'カテゴリB',
            'parent_id' => $categoryA->id,
        ]);
        $categoryC = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'name' => 'カテゴリC',
            'parent_id' => $rootCategory->id,
        ]);
        $categoryD = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
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
            'type' => Journal::type(),
            'name' => 'ルート'
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'name' => 'カテゴリA',
            'parent_id' => $rootCategory->id,
        ]);
        $categoryB = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'name' => 'カテゴリB',
            'parent_id' => $categoryA->id,
        ]);
        $categoryC = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'name' => 'カテゴリC',
            'parent_id' => $rootCategory->id,
        ]);
        $categoryD = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
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
            'type' => Journal::type(),
            'name' => 'ルート'
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'name' => 'カテゴリA',
            'parent_id' => $rootCategory->id,
        ]);
        $otherProfile = Profile::factory()->create();
        $categoryB = Category::factory()->create([
            'profile_id' => $otherProfile->id,
            'type' => Journal::type(),
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
            'type' => Journal::type(),
            'name' => 'ルート'
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
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
            'type' => Journal::type(),
            'name' => 'ルート'
        ]);
        $categoryA = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
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
            'type' => Journal::type(),
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
            'type' => Journal::type(),
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
            'type' => Journal::type(),
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
                Category::factory(1, ['name' => '親カテゴリ', 'type' => Journal::type()])
                    ->has(
                        Category::factory(3, ['type' => Journal::type()]),
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
            'type' => Journal::type(),
            'name' => '親カテゴリ'
        ]);
        $otherCategory = Category::factory()->create([
            'profile_id' => $otherProfile->id,
            'type' => Journal::type(),
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
            'type' => Journal::type(),
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
            'type' => Journal::type(),
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
                Category::factory(1, ['name' => '親カテゴリ', 'type' => Journal::type()])
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
                Category::factory(1, ['name' => '親カテゴリ', 'type' => Journal::type()]),
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
                Category::factory(1, ['name' => '親カテゴリ', 'type' => Journal::type()]),
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
                Category::factory(1, ['name' => '親カテゴリ', 'type' => Journal::type()])
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
            'type' => Journal::type(),
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
            'type' => Journal::type(),
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
            'type' => Journal::type(),
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
            'type' => Journal::type(),
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
            'type' => Journal::type(),
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
     * 投稿リスト
     * 
     * - カテゴリに分類分けされている投稿のコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#投稿リスト
     */
    public function test_posts()
    {
        //  準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
        ]);
        Journal::factory()->count(3)->create([
            'profile_id' => $profile->id,
            'category_id' => $category->id,
            'is_public' => false,
        ]);
        $category->refresh();

        // 実行
        $posts = $category->posts;

        // 評価
        $this->assertEquals(3, $posts->count());
        foreach ($posts as $post) {
            $this->assertEquals($post->category->id, $category->id, 'カテゴリに分類分けされている投稿のリストであること');
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
            'type' => Journal::type(),
            'name' => 'カテゴリ1',
        ]);
        $category2 = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'name' => 'カテゴリ2',
            'parent_id' => $category1->id,
        ]);
        $category3 = Category::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
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

    /**
     * カテゴリ所有者による絞り込み
     * 
     * - カテゴリ所有者によりカテゴリを絞り込むことができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ所有者による絞り込み
     */
    public function test_filter_by_owner()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(Category::factory()->count(2)->sequence(
            ['name' => 'Feeldeeのカテゴリ', 'type' => Journal::type()],
            ['name' => 'Feeldeeのカテゴリ', 'type' => Photo::type()],
        ))->create();
        Profile::factory(['nickname' => 'Other'])->has(Category::factory()->count(3)->sequence(
            ['name' => 'Otherのカテゴリ', 'type' => Journal::type()],
            ['name' => 'Otherのカテゴリ', 'type' => Photo::type()],
            ['name' => 'Otherのカテゴリ', 'type' => Location::type()],
        ))->create();

        // 実行
        $categories = Category::by(Profile::of('Feeldee')->first())->get();

        // 評価
        $this->assertEquals(2, $categories->count());
        foreach ($categories as $category) {
            $this->assertEquals('Feeldeeのカテゴリ', $category->name, 'カテゴリ所有者によりカテゴリを絞り込むことができること');
        }
    }

    /**
     * カテゴリ所有者による絞り込み
     * 
     * - ニックネームによりカテゴリ所有者を特定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ所有者による絞り込み
     */
    public function test_filter_by_owner_nickname()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(Category::factory()->count(2)->sequence(
            ['name' => 'Feeldeeのカテゴリ', 'type' => Journal::type()],
            ['name' => 'Feeldeeのカテゴリ', 'type' => Photo::type()],
        ))->create();
        Profile::factory(['nickname' => 'Other'])->has(Category::factory()->count(3)->sequence(
            ['name' => 'Otherのカテゴリ', 'type' => Journal::type()],
            ['name' => 'Otherのカテゴリ', 'type' => Photo::type()],
            ['name' => 'Otherのカテゴリ', 'type' => Location::type()],
        ))->create();

        // 実行
        $categories = Category::by('Feeldee')->get();

        // 評価
        $this->assertEquals(2, $categories->count());
        foreach ($categories as $category) {
            $this->assertEquals('Feeldeeのカテゴリ', $category->name, 'カテゴリ所有者によりカテゴリを絞り込むことができること');
        }
    }

    /**
     * カテゴリタイプによる絞り込み
     * 
     * - カテゴリタイプによりカテゴリを絞り込むことができることを確認します。
     * - 投稿の抽象クラスを継承した具象クラスを使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリタイプによる絞り込み
     */
    public function test_filter_by_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(Category::factory()->count(5)->sequence(
            ['name' => 'JournalCategory_1', 'type' => Journal::type()],
            ['name' => 'JournalCategory_2', 'type' => Journal::type()],
            ['name' => 'PhotoCategory', 'type' => Photo::type()],
            ['name' => 'LocationCategory', 'type' => Location::type()],
            ['name' => 'ItemCategory', 'type' => Item::type()],
        ))->create();

        // 実行
        $journalCategories = Category::by('Feeldee')->of(Journal::class)->get();

        // 評価
        $this->assertEquals(2, $journalCategories->count());
    }

    /**
     * カテゴリタイプによる絞り込み
     * 
     * - カテゴリタイプによりカテゴリを絞り込むことができることを確認します。
     * - 投稿種別の文字列を使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリタイプによる絞り込み
     */
    public function test_filter_by_type_string()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(Category::factory()->count(7)->sequence(
            ['name' => 'JournalCategory_1', 'type' => Journal::type()],
            ['name' => 'JournalCategory_2', 'type' => Journal::type()],
            ['name' => 'PhotoCategory', 'type' => Photo::type()],
            ['name' => 'LocationCategory', 'type' => Location::type()],
            ['name' => 'ItemCategory_1', 'type' => Item::type()],
            ['name' => 'ItemCategory_2', 'type' => Item::type()],
            ['name' => 'ItemCategory_3', 'type' => Item::type()],
        ))->create();

        // 実行
        $itemCategories = Category::by('Feeldee')->of(Item::type())->get();

        // 評価
        $this->assertEquals(3, $itemCategories->count());
    }

    /**
     * カテゴリ名による絞り込み
     * 
     * - カテゴリ名を指定してカテゴリを絞り込むことができることを確認します。
     * - カテゴリ名を完全一致検索できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ名による絞り込み
     */
    public function test_filter_by_name()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(Category::factory()->count(6)->sequence(
            ['name' => 'JournalCategory_1', 'type' => Journal::type()],
            ['name' => 'JournalCategory_2', 'type' => Journal::type()],
            ['name' => 'PhotoCategory', 'type' => Photo::type()],
            ['name' => 'LocationCategory', 'type' => Location::type()],
            ['name' => 'NewItem', 'type' => Item::type()],
            ['name' => 'NewItem2', 'type' => Item::type()],
        ))->create();

        // 実行
        $category = Category::by('Feeldee')->of(Item::type())->name('NewItem')->first();

        // 評価
        $this->assertEquals('NewItem', $category->name);
    }

    /**
     * カテゴリ名による絞り込み
     * 
     * - カテゴリ名を指定してカテゴリを絞り込むことができることを確認します。
     * - カテゴリ名を前方一致検索できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ名による絞り込み
     */
    public function test_filter_by_name_prefix()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(Category::factory()->count(6)->sequence(
            ['name' => 'MyPet1', 'type' => Journal::type()],
            ['name' => 'JournalCategory_2', 'type' => Journal::type()],
            ['name' => 'PhotoCategory', 'type' => Photo::type()],
            ['name' => 'LocationCategory', 'type' => Location::type()],
            ['name' => 'MyPet3', 'type' => Item::type()],
            ['name' => 'MyPet2', 'type' => Item::type()],
        ))->create();

        // 実行
        $mypets = Category::by('Feeldee')->name('MyPet', Like::Prefix)->get();

        // 評価
        $this->assertEquals(3, $mypets->count());
    }

    /**
     * カテゴリ名による絞り込み
     * 
     * - カテゴリ名を指定してカテゴリを絞り込むことができることを確認します。
     * - カテゴリ名を後方一致検索できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ名による絞り込み
     */
    public function test_filter_by_name_suffix()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(Category::factory()->count(6)->sequence(
            ['name' => 'HomePark', 'type' => Journal::type()],
            ['name' => 'JournalCategory_2', 'type' => Journal::type()],
            ['name' => 'PhotoCategory', 'type' => Photo::type()],
            ['name' => 'HomePark', 'type' => Location::type()],
            ['name' => 'BollPark', 'type' => Location::type()],
            ['name' => 'Park', 'type' => Location::type()],
        ))->create();

        // 実行
        $mypets = Category::by('Feeldee')->of(Location::class)->name('Park', Like::Suffix)->get();

        // 評価
        $this->assertEquals(3, $mypets->count());
    }
}
