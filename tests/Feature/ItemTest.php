<?php

namespace Tests\Feature;

use Feeldee\Framework\Contracts\HssProfile;
use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Category;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Post;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\PublicLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * コンテンツ種別
     * 
     * - アイテムのコンテンツ種別は、"item"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツ種別
     */
    public function test_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $item = $profile->items()->create([
            'title' => 'テストアイテム',
        ]);

        // 検証
        $this->assertEquals('item', $item->type(), 'アイテムのコンテンツ種別は、"item"であること');
    }

    /**
     * コンテンツ所有プロフィール
     * 
     * - アイテムを作成したユーザのプロフィールであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツ所有プロフィール
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $item = $profile->items()->create([
            'title' => 'テストアイテム',
        ]);

        // 検証
        $this->assertEquals($profile->id, $item->profile->id, 'アイテムを作成したユーザのプロフィールであること');
        $this->assertDatabaseHas('items', [
            'profile_id' => $profile->id,
        ]);
    }

    /**
     * コンテンツタイトル
     * 
     * - 登録したアイテムに付けるタイトルであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツタイトル
     */
    public function test_title()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $title = 'アイテムのタイトル';

        // 実行
        $item = $profile->items()->create([
            'title' => $title,
        ]);

        // 検証
        $this->assertEquals($title, $item->title, '登録したアイテムに付けるタイトルであること');
    }

    /**
     * コンテンツタイトル
     * 
     * - 登録時に必ず指定する必要があることを確認します。
     * - 例外コード:50001のメッセージであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツタイトル
     */
    public function test_title_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->items()->create();
        }, ApplicationException::class, 'ItemTitleRequired');
    }

    /**
     * コンテンツ内容
     * 
     * - アイテムの説明またはメモ書きなどであることを確認します。
     * - HTMLが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツ内容
     */
    public function test_value_html()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>アイテム内容の本文</p>';

        // 実行
        $item = $profile->items()->create([
            'title' => 'アイテムタイトル',
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($value, $item->value, 'アイテムの説明またはメモ書きなどであること');
        // HTMLが使用できること
        $this->assertDatabaseHas('items', [
            'value' => $value,
        ]);
    }

    /**
     * コンテンツ内容
     * 
     * - アイテムの説明またはメモ書きなどであることを確認します。
     * - テキストが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツ内容
     */
    public function test_value_text()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = 'アイテムの本文';

        // 実行
        $item = $profile->items()->create([
            'title' => 'アイテムタイトル',
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($value, $item->value, 'アイテムの説明またはメモ書きなどであること');
        // テキストが使用できること
        $this->assertDatabaseHas('items', [
            'value' => $value,
        ]);
    }

    /**
     * コンテンツテキスト
     * 
     * - コンテンツ内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - コンテンツ内容の登録時に、自動変換されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツテキスト
     */
    public function test_text_create()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>アイテム内容の本文</p>';
        $expected = 'アイテム内容の本文';

        // 実行
        $item = $profile->items()->create([
            'title' => 'アイテムタイトル',
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($expected, $item->text, 'コンテンツ内容から、HTMLタグのみを排除したテキスト表現であること');
        // コンテンツ内容の登録時に、自動変換されること
        $this->assertDatabaseHas('items', [
            'text' => $expected,
        ]);
    }

    /**
     * コンテンツテキスト
     * 
     * - コンテンツ内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - コンテンツ内容の編集時に、自動変換されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツテキスト
     */
    public function test_text_update()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory()->count(1))->create();
        $item = $profile->items->first();
        $value = '<p>アイテム内容の本文</p>';
        $expected = 'アイテム内容の本文';

        // 実行
        $item->update([
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($expected, $item->text, 'コンテンツ内容から、HTMLタグのみを排除したテキスト表現であること');
        // コンテンツ内容の編集時に、自動変換されること
        $this->assertDatabaseHas('items', [
            'text' => $expected,
        ]);
    }

    /**
     * コンテンツ公開フラグ
     * 
     * - デフォルトは、非公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツ公開フラグ
     */
    public function test_is_public_default()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $item = $profile->items()->create([
            'title' => 'テストアイテム',
        ]);

        // 評価
        $this->assertFalse($item->isPublic, 'デフォルトは、非公開であること');
    }

    /**
     * コンテンツ公開フラグ
     * 
     * - 公開できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツ公開フラグ
     */
    public function test_is_public_doPublic()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $item = Item::factory([
            'is_public' => false,
            'profile_id' => $profile->id,
        ])->create();

        // 実行
        $item->doPublic();

        // 評価
        $this->assertTrue($item->isPublic, '公開できること');
    }

    /**
     * コンテンツ公開フラグ
     * 
     * - 非公開にできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツ公開フラグ
     */
    public function test_is_public_doPrivate()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $item = Item::factory([
            'is_public' => true,
            'profile_id' => $profile->id,
        ])->create();

        // 実行
        $item->doPrivate();

        // 評価
        $this->assertFalse($item->isPublic, '非公開にできること');
    }

    /**
     * コンテンツ公開レベル
     * 
     * - デフォルトは、"自分"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツ公開レベル
     */
    public function test_public_level_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $item = $profile->items()->create([
            'title' => 'テストアイテム',
        ]);

        // 評価
        $this->assertEquals(PublicLevel::Private, $item->public_level, 'デフォルトは、"自分"であること');
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'public_level' => PublicLevel::Private,
        ]);
    }

    /**
     * コンテンツ公開レベル
     * 
     * - コンテンツ公開レベルを指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツ公開レベル
     */
    public function test_public_level()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $item = $profile->items()->create([
            'title' => 'テストアイテム',
            'public_level' => PublicLevel::Private,
        ]);

        // 評価
        $this->assertEquals(PublicLevel::Private, $item->public_level, 'コンテンツ公開レベルを指定できること');
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'public_level' => PublicLevel::Private,
        ]);
    }

    /**
     * コンテンツ公開レベル
     * 
     * - コンテンツ公開レベルを変更できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツ公開レベル
     */
    public function test_public_level_update()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $item = Item::factory([
            'profile_id' => $profile->id,
            'public_level' => PublicLevel::Public,
        ])->create();

        // 実行
        $item->public_level = PublicLevel::Private;
        $item->save();

        // 評価
        $this->assertEquals(PublicLevel::Private, $item->public_level, 'コンテンツ公開レベルを変更できること');
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'public_level' => PublicLevel::Private,
        ]);
    }

    /**
     * コンテンツカテゴリ
     * 
     * - カテゴリを指定できることを確認します。
     * - 指定したカテゴリのカテゴリ所有プロフィールが、コンテンツ所有プロフィールと一致していることを確認します。
     * - 指定したカテゴリが、アイテムのカテゴリであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツカテゴリ
     */
    public function test_category()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'type' => Item::type(),
        ])->create();

        // 実行
        $item = $profile->items()->create([
            'title' => 'テストアイテム',
            'category' => $category,
        ]);

        // 評価
        $this->assertEquals($category->id, $item->category->id, 'カテゴリを指定できること');
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'category_id' => $category->id,
        ]);
    }

    /**
     * コンテンツカテゴリ
     * 
     * - カテゴリIDを指定できることを確認します。
     * - 指定したカテゴリのカテゴリ所有プロフィールが、コンテンツ所有プロフィールと一致していることを確認します。
     * - 指定したカテゴリが、アイテムのカテゴリであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツカテゴリ
     */
    public function test_category_id()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'type' => Item::type(),
        ])->create();

        // 実行
        $item = $profile->items()->create([
            'title' => 'テストアイテム',
            'category_id' => $category->id,
        ]);

        // 評価
        $this->assertEquals($category->id, $item->category->id, 'カテゴリを指定できること');
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'category_id' => $category->id,
        ]);
    }

    /**
     * コンテンツカテゴリ
     * 
     * - カテゴリ所有プロフィールがコンテンツ所有プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツカテゴリ
     */
    public function test_category_profile_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $otherProfile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'type' => Item::type(),
        ])->create();

        // 実行
        $this->assertThrows(function () use ($otherProfile, $category) {
            $otherProfile->items()->create([
                'title' => 'テストアイテム',
                'category' => $category,
            ]);
        }, ApplicationException::class, 'CategoryContentProfileMissmatch');
    }

    /**
     * コンテンツカテゴリ
     * 
     * - コンテンツ種別と同じカテゴリタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツカテゴリ
     */
    public function test_category_type_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'type' => Post::type(),
        ])->create();

        // 実行
        $this->assertThrows(function () use ($profile, $category) {
            $profile->items()->create([
                'title' => 'テストアイテム',
                'category' => $category,
            ]);
        }, ApplicationException::class, 'CategoryContentTypeMissmatch');
    }

    /**
     * コンテンツカテゴリ
     * 
     * - カテゴリ名を指定した場合は、カテゴリ所有プロフィールとコンテンツ所有プロフィールが一致し、かつコンテンツ種別と同じカテゴリタイプのカテゴリの中からカテゴリ名が一致するカテゴリのIDが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツカテゴリ
     */
    public function test_category_name()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'name' => 'テストカテゴリ',
            'type' => Item::type(),
        ])->create();

        // 実行
        $item = $profile->items()->create([
            'title' => 'テストアイテム',
            'category' => 'テストカテゴリ',
        ]);

        // 評価
        $this->assertEquals($category->id, $item->category->id, 'カテゴリ名を指定した場合は、カテゴリ所有プロフィールとコンテンツ所有プロフィールが一致し、かつコンテンツ種別と同じカテゴリタイプのカテゴリの中からカテゴリ名が一致するカテゴリのIDが設定されること');
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'category_id' => $category->id,
        ]);
    }

    /**
     * コンテンツカテゴリ
     * 
     * - 一致するカテゴリが存在しない場合は無視されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツカテゴリ
     */
    public function test_category_name_nomatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        Category::factory([
            'profile_id' => $profile->id,
            'name' => 'テストカテゴリ',
            'type' => Item::type(),
        ])->create();

        // 実行
        $item = $profile->items()->create([
            'title' => 'テストアイテム',
            'category' => 'テストカテゴリ2',
        ]);

        // 評価
        $this->assertNull($item->category, '一致するカテゴリが存在しない場合は無視されること');
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'category_id' => null,
        ]);
    }

    /**
     * コンテンツカテゴリ
     * 
     * - 対応するカテゴリが削除された場合は、自動的にNullが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツカテゴリ
     */
    public function test_category_delete()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'name' => 'テストカテゴリ',
            'type' => Item::type(),
        ])->create();
        $item = Item::factory([
            'profile_id' => $profile->id,
            'category_id' => $category->id,
        ])->create();
        $this->assertNotNull($item->category);

        // 実行
        $category->delete();
        $item->refresh();

        // 評価
        $this->assertNull($item->category, '対応するカテゴリが削除された場合は、自動的にNullが設定されること');
    }

    /**
     * コンテンツタグリスト
     * 
     * - タグ付けできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツタグリスト
     */
    public function test_tags()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Item::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Item::type(),
        ]);

        // 実行
        $Item = $profile->Items()->create([
            'title' => 'テストアイテム',
            'tags' => [$tag1, $tag2],
        ]);

        // 評価
        $this->assertEquals(2, $Item->tags->count(), 'タグ付けできること');
        foreach ($Item->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $Item->id,
                'taggable_type' => Item::type(),
            ]);
        }
    }

    /**
     * コンテンツタグリスト
     * 
     * - タグIDを指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツタグリスト
     */
    public function test_tags_id()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Item::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Item::type(),
        ]);

        // 実行
        $Item = $profile->Items()->create([
            'title' => 'テストアイテム',
            'tags' => [$tag1->id, $tag2->id],
        ]);

        // 評価
        $this->assertEquals(2, $Item->tags->count(), 'タグIDを指定できること');
        foreach ($Item->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $Item->id,
                'taggable_type' => Item::type(),
            ]);
        }
    }

    /**
     * コンテンツタグリスト
     * 
     * - タグ所有プロフィールがコンテンツ所有プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツタグリスト
     */
    public function test_tags_profile_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $otherProfile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Item::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Item::type(),
        ]);

        // 実行
        $this->assertThrows(function () use ($otherProfile, $tag1, $tag2) {
            $otherProfile->Items()->create([
                'title' => 'テストアイテム',
                'tags' => [$tag1->id, $tag2->id],
            ]);
        }, ApplicationException::class, 'TagContentProfileMissmatch');
    }

    /**
     * コンテンツタグリスト
     * 
     * - タグタイプがコンテンツ種別と一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツタグリスト
     */
    public function test_tags_type_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Item::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Post::type(),
        ]);

        // 実行
        $this->assertThrows(function () use ($profile, $tag1, $tag2) {
            $profile->Items()->create([
                'title' => 'テストアイテム',
                'tags' => [$tag1->id, $tag2->id],
            ]);
        }, ApplicationException::class, 'TagContentTypeMissmatch');
    }

    /**
     * コンテンツタグリスト
     * 
     * - タグ名を指定した場合は、タグ所有プロフィールとコンテンツ所有プロフィールが一致し、かつコンテンツ種別と同じタグタイプのタグの中からタグ名が一致するタグのIDが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツタグリスト
     */
    public function test_tags_name()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Item::type(),
        ]);
        $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Item::type(),
        ]);

        // 実行
        $Item = $profile->Items()->create([
            'title' => 'テストアイテム',
            'tags' => ['タグ1', 'タグ2'],
        ]);

        // 評価
        $this->assertEquals(2, $Item->tags->count(), 'タグ名を指定した場合は、タグ所有プロフィールとコンテンツ所有プロフィールが一致し、かつコンテンツ種別と同じタグタイプのタグの中からタグ名が一致するタグのIDが設定されること');
        foreach ($Item->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $Item->id,
                'taggable_type' => Item::type(),
            ]);
        }
    }

    /**
     * コンテンツタグリス
     * 
     * - 一致するタグが存在しない場合は無視されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツタグリスト
     */
    public function test_tags_name_nomatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Item::type(),
        ]);
        $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Item::type(),
        ]);

        // 実行
        $Item = $profile->Items()->create([
            'title' => 'テストアイテム',
            'tags' => ['タグ3', 'タグ2'],
        ]);

        // 評価
        $this->assertEquals(1, $Item->tags->count(), '一致するタグが存在しない場合は無視されること');
        foreach ($Item->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $Item->id,
                'taggable_type' => Item::type(),
            ]);
        }
    }

    /**
     * コンテンツタグリスト
     * 
     * - 対応するタグが削除された場合は、コンテンツタグリストから自動的に除外されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツタグリスト
     */
    public function test_tags_delete()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Item::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Item::type(),
        ]);
        $Item = $profile->Items()->create([
            'title' => 'テストアイテム',
            'tags' => [$tag1, $tag2],
        ]);

        // 実行
        $tag1->delete();

        // 評価
        $this->assertEquals(1, $Item->tags->count(), '対応するタグが削除された場合は、コンテンツタグリストから自動的に除外されること');
        foreach ($Item->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $Item->id,
                'taggable_type' => Item::type(),
            ]);
        }
    }
}
