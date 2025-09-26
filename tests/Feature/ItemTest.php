<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Feeldee\Framework\Casts\HTML;
use Feeldee\Framework\Casts\URL;
use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Category;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Journal;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\PublicLevel;
use Feeldee\Framework\Models\Recorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\Hooks\CustomHtmlHook;
use Tests\Hooks\CustomUrlHook;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 投稿種別
     * 
     * - アイテムの投稿種別は、"item"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿種別
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
        $this->assertEquals('item', $item->type(), 'アイテムの投稿種別は、"item"であること');
    }

    /**
     * 投稿者プロフィール
     * 
     * - アイテムを作成したユーザのプロフィールであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿者プロフィール
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
     * 投稿タイトル
     * 
     * - 登録したアイテムに付けるタイトルであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿タイトル
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
     * 投稿タイトル
     * 
     * - 登録時に必ず指定する必要があることを確認します。
     * - 例外コード:50001のメッセージであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿タイトル
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
     * 投稿内容
     * 
     * - アイテムの説明またはメモ書きなどであることを確認します。
     * - HTMLが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿内容
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
     * 投稿内容
     * 
     * - アイテムの説明またはメモ書きなどであることを確認します。
     * - テキストが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿内容
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
     * 投稿内容テキスト
     * 
     * - 投稿内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - 投稿内容の登録時に、自動変換されることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿内容テキスト
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
        $this->assertEquals($expected, $item->text, '投稿内容から、HTMLタグのみを排除したテキスト表現であること');
        // 投稿内容の登録時に、自動変換されること
        $this->assertDatabaseHas('items', [
            'text' => $expected,
        ]);
    }

    /**
     * 投稿内容テキスト
     * 
     * - 投稿内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - 投稿内容の編集時に、自動変換されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿内容テキスト
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
        $this->assertEquals($expected, $item->text, '投稿内容から、HTMLタグのみを排除したテキスト表現であること');
        // 投稿内容の編集時に、自動変換されること
        $this->assertDatabaseHas('items', [
            'text' => $expected,
        ]);
    }

    /**
     * 投稿公開フラグ
     * 
     * - デフォルトは、非公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿公開フラグ
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
     * 投稿公開フラグ
     * 
     * - 公開できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿公開フラグ
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
     * 投稿公開フラグ
     * 
     * - 非公開にできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿公開フラグ
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
     * 投稿公開レベル
     * 
     * - デフォルトは、"自分"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿公開レベル
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
     * 投稿公開レベル
     * 
     * - 投稿公開レベルを指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿公開レベル
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
        $this->assertEquals(PublicLevel::Private, $item->public_level, '投稿公開レベルを指定できること');
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'public_level' => PublicLevel::Private,
        ]);
    }

    /**
     * 投稿公開レベル
     * 
     * - 投稿公開レベルを変更できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿公開レベル
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
        $this->assertEquals(PublicLevel::Private, $item->public_level, '投稿公開レベルを変更できること');
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'public_level' => PublicLevel::Private,
        ]);
    }

    /**
     * 投稿カテゴリ
     * 
     * - カテゴリを指定できることを確認します。
     * - 指定したカテゴリのカテゴリ所有プロフィールが、投稿者プロフィールと一致していることを確認します。
     * - 指定したカテゴリが、アイテムのカテゴリであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿カテゴリ
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
     * 投稿カテゴリ
     * 
     * - カテゴリIDを指定できることを確認します。
     * - 指定したカテゴリのカテゴリ所有プロフィールが、投稿者プロフィールと一致していることを確認します。
     * - 指定したカテゴリが、アイテムのカテゴリであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿カテゴリ
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
     * 投稿カテゴリ
     * 
     * - カテゴリ所有プロフィールが投稿者プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿カテゴリ
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
        }, ApplicationException::class, 'CategoryProfileMissmatch');
    }

    /**
     * 投稿カテゴリ
     * 
     * - 投稿種別と同じカテゴリタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿カテゴリ
     */
    public function test_category_type_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
        ])->create();

        // 実行
        $this->assertThrows(function () use ($profile, $category) {
            $profile->items()->create([
                'title' => 'テストアイテム',
                'category' => $category,
            ]);
        }, ApplicationException::class, 'CategoryTypeMissmatch');
    }

    /**
     * 投稿カテゴリ
     * 
     * - カテゴリ名を指定した場合は、カテゴリ所有プロフィールと投稿者プロフィールが一致し、かつ投稿種別と同じカテゴリタイプのカテゴリの中からカテゴリ名が一致するカテゴリのIDが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿カテゴリ
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
        $this->assertEquals($category->id, $item->category->id, 'カテゴリ名を指定した場合は、カテゴリ所有プロフィールと投稿者プロフィールが一致し、かつ投稿種別と同じカテゴリタイプのカテゴリの中からカテゴリ名が一致するカテゴリのIDが設定されること');
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'category_id' => $category->id,
        ]);
    }

    /**
     * 投稿カテゴリ
     * 
     * - 一致するカテゴリが存在しない場合は無視されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿カテゴリ
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
     * 投稿カテゴリ
     * 
     * - 対応するカテゴリが削除された場合は、自動的にNullが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿カテゴリ
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
     * 投稿タグリスト
     * 
     * - タグ付けできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿タグリスト
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
     * 投稿タグリスト
     * 
     * - タグIDを指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿タグリスト
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
     * 投稿タグリスト
     * 
     * - タグ所有プロフィールが投稿者プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿タグリスト
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
        }, ApplicationException::class, 'TagProfileMissmatch');
    }

    /**
     * 投稿タグリスト
     * 
     * - タグタイプが投稿種別と一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿タグリスト
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
            'type' => Journal::type(),
        ]);

        // 実行
        $this->assertThrows(function () use ($profile, $tag1, $tag2) {
            $profile->Items()->create([
                'title' => 'テストアイテム',
                'tags' => [$tag1->id, $tag2->id],
            ]);
        }, ApplicationException::class, 'TagTypeMissmatch');
    }

    /**
     * 投稿タグリスト
     * 
     * - タグ名を指定した場合は、タグ所有プロフィールと投稿者プロフィールが一致し、かつ投稿種別と同じタグタイプのタグの中からタグ名が一致するタグのIDが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿タグリスト
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
        $this->assertEquals(2, $Item->tags->count(), 'タグ名を指定した場合は、タグ所有プロフィールと投稿者プロフィールが一致し、かつ投稿種別と同じタグタイプのタグの中からタグ名が一致するタグのIDが設定されること');
        foreach ($Item->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $Item->id,
                'taggable_type' => Item::type(),
            ]);
        }
    }

    /**
     * 投稿タグリスト
     * 
     * - 一致するタグが存在しない場合は無視されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿タグリスト
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
     * 投稿タグリスト
     * 
     * - 対応するタグが削除された場合は、投稿タグリストから自動的に除外されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿タグリスト
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
        $this->assertEquals(1, $Item->tags->count(), '対応するタグが削除された場合は、投稿タグリストから自動的に除外されること');
        foreach ($Item->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $Item->id,
                'taggable_type' => Item::type(),
            ]);
        }
    }

    /**
     * 投稿レコードリスト
     * 
     * - レコーダによって記録されたアイテムのレコードリストであることを確認します。
     * - レコーダの指定は、レコーダそのものを指定することができることを確認します。
     * - レコーダの指定は、レコーダIDを指定することができることを確認します。
     * - レコーダの指定は、レコーダ名を指定することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿レコードリスト
     */
    public function test_content_records()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $item = Item::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder1 = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Item::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);
        $recorder2 = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Item::type(),
            'data_type' => 'bool',
            'name' => 'テストレコーダ2',
        ]);
        $recorder3 = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Item::type(),
            'data_type' => 'date',
            'name' => 'テストレコーダ3',
        ]);

        // 実行
        $item->record($recorder1, 1);
        $item->record($recorder2->id, true);
        $item->record('テストレコーダ3', '2025-04-22');

        // 評価
        $this->assertEquals(3, $item->records->count(), 'レコーダによって記録されたアイテムのレコードリストであること');
        foreach ($item->records as $i => $record) {
            if ($i == 0) {
                $this->assertEquals($recorder1->id, $record->recorder_id, 'レコーダそのものを指定することができること');
            } elseif ($i == 1) {
                $this->assertEquals($recorder2->id, $record->recorder_id, 'レコーダIDを指定することができること');
            } elseif ($i == 2) {
                $this->assertEquals($recorder3->id, $record->recorder_id, 'レコーダ名を指定することができること');
            }
        }
    }

    /**
     * 投稿レコードリスト
     * 
     * - レコーダを指定する場合は、レコーダ所有プロフィールが投稿者プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿レコードリスト
     */
    public function test_content_records_recorder_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $item = Item::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder = Recorder::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
            'type' => Item::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);

        // 実行
        $this->expectException(ApplicationException::class);
        $this->expectExceptionCode(73007);
        $item->record($recorder, 1);
    }

    /**
     * 投稿レコードリスト
     * 
     * - レコーダを指定する場合は、レコーダタイプが投稿種別と一致していることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿レコードリスト
     */
    public function test_content_records_recorder_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $item = Item::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);

        // 実行
        $this->expectException(ApplicationException::class);
        $this->expectExceptionCode(73008);
        $item->record($recorder, 1);
    }

    /**
     * 投稿レコードリスト
     * 
     * - レコーダIDを指定する場合は、レコーダ所有プロフィールが投稿者プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿レコードリスト
     */
    public function test_content_records_recorder_id_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $item = Item::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder = Recorder::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
            'type' => Item::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);

        // 実行
        $this->expectException(ApplicationException::class);
        $this->expectExceptionCode(73007);
        $item->record($recorder->id, 1);
    }

    /**
     * 投稿レコードリスト
     * 
     * - レコーダIDを指定する場合は、レコーダタイプが投稿種別と一致していることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿レコードリスト
     */
    public function test_content_records_recorder_id_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $item = Item::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);

        // 実行
        $this->expectException(ApplicationException::class);
        $this->expectExceptionCode(73008);
        $item->record($recorder->id, 1);
    }

    /**
     * 投稿レコードリスト
     * 
     * - 対応するレコーダが削除された場合は、投稿レコードリストからも自動的に除外されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿レコードリスト
     */
    public function test_content_records_recorder_delete()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $item = Item::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Item::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);

        // 実行
        $item->record($recorder, 1);
        $recorder->delete();

        // 評価
        $this->assertEquals(0, $item->records->count(), '対応するレコーダが削除された場合は、投稿レコードリストからも自動的に除外されること');
        $this->assertDatabaseEmpty('records');
    }

    /**
     * 投稿内容
     * 
     * - 取得時にHTMLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿内容
     */
    public function test_content_value_html_cast_hook_get()
    {

        // 準備
        Config::set(HTML::CONFIG_KEY_HTML_CAST_HOOKS, [
            CustomHtmlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>テスト投稿</p>';
        $item = Item::factory()->create([
            'profile_id' => $profile->id,
            'value' => $value,
        ]);

        // 実行
        $expected = $item->value;

        // 評価
        $this->assertEquals(CustomHtmlHook::PREFIX . $value, $expected, '取得時にHTMLキャストフックが利用できることを確認します。');
        $this->assertDatabaseHas('items', [
            'profile_id' => $profile->id,
            'value' => $value,
        ]);
    }

    /**
     * 投稿内容
     * 
     * - 設定時にHTMLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿内容
     */
    public function test_content_value_html_cast_hook_set()
    {

        // 準備
        Config::set(HTML::CONFIG_KEY_HTML_CAST_HOOKS, [
            CustomHtmlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>テスト投稿</p>';

        // 実行
        $profile->items()->create([
            'title' => 'テストアイテム',
            'value' => CustomHtmlHook::PREFIX . $value,
        ]);

        // 評価
        // 設定時にHTMLキャストフックが利用できること
        $this->assertDatabaseHas('items', [
            'profile_id' => $profile->id,
            'value' => $value,
        ]);
    }

    /**
     * 投稿サムネイル
     * 
     * - 取得時にURLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿サムネイル
     */
    public function test_item_thumbnail_url_cast_hook_get()
    {
        // 準備
        Config::set(URL::CONFIG_KEY_URL_CAST_HOOKS, [
            CustomUrlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $image = 'https://example.com/image.jpg';
        $item = Item::factory()->create([
            'profile_id' => $profile->id,
            'thumbnail' => $image,
        ]);

        // 実行
        $expected = $item->thumbnail;

        // 評価
        $this->assertEquals(CustomUrlHook::PREFIX . $image, $expected, '取得時にURLキャストフックが利用できること');
    }

    /**
     * 投稿サムネイル
     * 
     * - 設定時にURLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿サムネイル
     */
    public function test_item_thumbnail_url_cast_hook_set()
    {
        // 準備
        Config::set(URL::CONFIG_KEY_URL_CAST_HOOKS, [
            CustomUrlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $image = 'https://example.com/image.jpg';

        // 実行
        $profile->items()->create([
            'title' => 'テストアイテム',
            'thumbnail' => CustomUrlHook::PREFIX . $image,
        ]);

        // 評価
        // 設定時にURLキャストフックが利用できること
        $this->assertDatabaseHas('items', [
            'profile_id' => $profile->id,
            'thumbnail' => $image,
        ]);
    }

    /**
     * 新規作成
     * 
     * - アイテムの作成は、アイテムを追加したいプロフィールのアイテムリストに追加することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#新規作成
     */
    public function test_create_item()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $title = 'テストアイテム';
        $value = 'これはテストアイテムです。';
        $postedAt = now();

        // 実行
        $item = $profile->items()->create([
            'title' => $title,
            'posted_at' => $postedAt,
            'value' => $value,
        ]);

        // 評価
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'profile_id' => $profile->id,
            'title' => $title,
            'posted_at' => $postedAt->format('Y-m-d H:i:s'),
            'value' => $value,
        ]);
    }

    /**
     * 新規作成
     * 
     * - 投稿タイトルは、必須であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#新規作成
     */
    public function test_create_item_title_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->items()->create([
                'posted_at' => now(),
            ]);
        }, ApplicationException::class, 'ItemTitleRequired');
    }

    /**
     * 新規作成
     * 
     * - 投稿日時を省略した場合は、システム日付が設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#新規作成
     */
    public function test_create_item_posted_at_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $title = 'テストアイテム';
        $value = 'これはテストアイテムです。';

        // 実行
        $item = $profile->items()->create([
            'title' => $title,
            'value' => $value,
        ]);

        // 評価
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'profile_id' => $profile->id,
            'title' => $title,
            'posted_at' => $item->posted_at->format('Y-m-d H:i:s'),
            'value' => $value,
        ]);
    }

    /**
     * アイテムリストの並び順
     * 
     * - アイテムリストのデフォルトの並び順は、1.表示順昇順、2.投稿日時降順（最新順）であることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#アイテムリストの並び順
     */
    public function test_collection_sort_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        $itemA = Item::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-22 10:00:00'),
            'order_number' => 1,
        ]);
        $itemB = Item::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-23 10:00:00'),
            'order_number' => 2,
        ]);
        $itemC = Item::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-21 10:00:00'),
            'order_number' => 3,
        ]);
        $itemD = Item::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-23 10:00:00'),
            'order_number' => 3,
        ]);

        // 実行
        $items = Profile::of('Feeldee')->first()->items;

        // 評価
        $this->assertEquals(4, $items->count());
        $this->assertEquals($itemA->id, $items[0]->id);
        $this->assertEquals($itemB->id, $items[1]->id);
        $this->assertEquals($itemD->id, $items[2]->id);
        $this->assertEquals($itemC->id, $items[3]->id);
    }
}
