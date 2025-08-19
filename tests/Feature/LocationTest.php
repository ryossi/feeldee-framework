<?php

namespace Tests\Feature;

use Auth;
use Feeldee\Framework\Casts\HTML;
use Feeldee\Framework\Casts\URL;
use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Category;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\PublicLevel;
use Feeldee\Framework\Models\Recorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\Hooks\CustomHtmlHook;
use Tests\Hooks\CustomUrlHook;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * コンテンツ種別
     * 
     * - 場所のコンテンツ種別は、"location"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ種別
     */
    public function test_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $location = $profile->locations()->create([
            'title' => 'テスト',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
        ]);

        // 検証
        $this->assertEquals('location', $location->type(), '場所のコンテンツ種別は、"location"であること');
    }

    /**
     * コンテンツ所有プロフィール
     * 
     * - 場所を作成したユーザのプロフィールであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ所有プロフィール
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $location = $profile->locations()->create([
            'title' => 'テスト',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
        ]);

        // 検証
        $this->assertEquals($profile->id, $location->profile->id, '場所を作成したユーザのプロフィールであること');
        $this->assertDatabaseHas('locations', [
            'profile_id' => $profile->id,
        ]);
    }

    /**
     * コンテンツタイトル
     * 
     * - 登録した場所に付けるタイトルであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツタイトル
     */
    public function test_title()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $title = '場所のタイトル';

        // 実行
        $location = $profile->locations()->create([
            'title' => $title,
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
        ]);

        // 検証
        $this->assertEquals($title, $location->title, '登録した場所に付けるタイトルであること');
    }

    /**
     * コンテンツ内容
     * 
     * - 場所の説明またはメモ書きなどであることを確認します。
     * - HTMLが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ内容
     */
    public function test_value_html()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>場所内容の本文</p>';

        // 実行
        $location = $profile->locations()->create([
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'title' => '場所タイトル',
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($value, $location->value, '場所の説明またはメモ書きなどであること');
        // HTMLが使用できること
        $this->assertDatabaseHas('locations', [
            'value' => $value,
        ]);
    }

    /**
     * コンテンツ内容
     * 
     * - 場所の説明またはメモ書きなどであることを確認します。
     * - テキストが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ内容
     */
    public function test_value_text()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '場所の本文';

        // 実行
        $location = $profile->locations()->create([
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'title' => '場所タイトル',
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($value, $location->value, '場所の説明またはメモ書きなどであること');
        // テキストが使用できること
        $this->assertDatabaseHas('locations', [
            'value' => $value,
        ]);
    }

    /**
     * コンテンツテキスト
     * 
     * - コンテンツ内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - コンテンツ内容の登録時に、自動変換されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツテキスト
     */
    public function test_text_create()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>場所内容の本文</p>';
        $expected = '場所内容の本文';

        // 実行
        $location = $profile->locations()->create([
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'title' => '場所タイトル',
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($expected, $location->text, 'コンテンツ内容から、HTMLタグのみを排除したテキスト表現であること');
        // コンテンツ内容の登録時に、自動変換されること
        $this->assertDatabaseHas('locations', [
            'text' => $expected,
        ]);
    }

    /**
     * コンテンツテキスト
     * 
     * - コンテンツ内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - コンテンツ内容の編集時に、自動変換されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツテキスト
     */
    public function test_text_update()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Location::factory()->count(1))->create();
        $location = $profile->locations->first();
        $value = '<p>場所内容の本文</p>';
        $expected = '場所内容の本文';

        // 実行
        $location->update([
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($expected, $location->text, 'コンテンツ内容から、HTMLタグのみを排除したテキスト表現であること');
        // コンテンツ内容の編集時に、自動変換されること
        $this->assertDatabaseHas('locations', [
            'text' => $expected,
        ]);
    }

    /**
     * コンテンツ公開フラグ
     * 
     * - デフォルトは、非公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ公開フラグ
     */
    public function test_is_public_default()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $location = $profile->locations()->create([
            'title' => 'テスト',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
        ]);

        // 評価
        $this->assertFalse($location->isPublic, 'デフォルトは、非公開であること');
    }

    /**
     * コンテンツ公開フラグ
     * 
     * - 公開できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ公開フラグ
     */
    public function test_is_public_doPublic()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $location = Location::factory([
            'is_public' => false,
            'profile_id' => $profile->id,
        ])->create();

        // 実行
        $location->doPublic();

        // 評価
        $this->assertTrue($location->isPublic, '公開できること');
    }

    /**
     * コンテンツ公開フラグ
     * 
     * - 非公開にできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ公開フラグ
     */
    public function test_is_public_doPrivate()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $location = Location::factory([
            'is_public' => true,
            'profile_id' => $profile->id,
        ])->create();

        // 実行
        $location->doPrivate();

        // 評価
        $this->assertFalse($location->isPublic, '非公開にできること');
    }

    /**
     * コンテンツ公開レベル
     * 
     * - デフォルトは、"自分"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ公開レベル
     */
    public function test_public_level_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $location = $profile->locations()->create([
            'title' => 'テスト',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
        ]);

        // 評価
        $this->assertEquals(PublicLevel::Private, $location->public_level, 'デフォルトは、"自分"であること');
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'public_level' => PublicLevel::Private,
        ]);
    }

    /**
     * コンテンツ公開レベル
     * 
     * - コンテンツ公開レベルを指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ公開レベル
     */
    public function test_public_level()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $location = $profile->locations()->create([
            'title' => 'テスト',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'public_level' => PublicLevel::Friend,
        ]);

        // 評価
        $this->assertEquals(PublicLevel::Friend, $location->public_level, 'コンテンツ公開レベルを指定できること');
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'public_level' => PublicLevel::Friend,
        ]);
    }

    /**
     * コンテンツ公開レベル
     * 
     * - コンテンツ公開レベルを変更できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ公開レベル
     */
    public function test_public_level_update()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $location = Location::factory([
            'profile_id' => $profile->id,
        ])->create();

        // 実行
        $location->public_level = PublicLevel::Friend;
        $location->save();

        // 評価
        $this->assertEquals(PublicLevel::Friend, $location->public_level, 'コンテンツ公開レベルを変更できること');
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'public_level' => PublicLevel::Friend,
        ]);
    }

    /**
     * コンテンツカテゴリ
     * 
     * - カテゴリを指定できることを確認します。
     * - 指定したカテゴリのカテゴリ所有プロフィールが、コンテンツ所有プロフィールと一致していることを確認します。
     * - 指定したカテゴリが、場所のカテゴリであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツカテゴリ
     */
    public function test_category()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'type' => Location::type(),
        ])->create();

        // 実行
        $location = $profile->locations()->create([
            'title' => 'テスト場所',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'category' => $category,
        ]);

        // 評価
        $this->assertEquals($category->id, $location->category->id, 'カテゴリを指定できること');
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'category_id' => $category->id,
        ]);
    }

    /**
     * コンテンツカテゴリ
     * 
     * - カテゴリIDを指定できることを確認します。
     * - 指定したカテゴリのカテゴリ所有プロフィールが、コンテンツ所有プロフィールと一致していることを確認します。
     * - 指定したカテゴリが、場所のカテゴリであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツカテゴリ
     */
    public function test_category_id()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'type' => Location::type(),
        ])->create();

        // 実行
        $location = $profile->locations()->create([
            'title' => 'テスト場所',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'category_id' => $category->id,
        ]);

        // 評価
        $this->assertEquals($category->id, $location->category->id, 'カテゴリを指定できること');
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'category_id' => $category->id,
        ]);
    }

    /**
     * コンテンツカテゴリ
     * 
     * - カテゴリ所有プロフィールがコンテンツ所有プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツカテゴリ
     */
    public function test_category_profile_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $otherProfile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'type' => Location::type(),
        ])->create();

        // 実行
        $this->assertThrows(function () use ($otherProfile, $category) {
            $otherProfile->locations()->create([
                'title' => 'テスト場所',
                'latitude' => 35.681236,
                'longitude' => 139.767125,
                'zoom' => 15,
                'category_id' => $category->id,
            ]);
        }, ApplicationException::class, 'CategoryContentProfileMissmatch');
    }

    /**
     * コンテンツカテゴリ
     * 
     * - コンテンツ種別と同じカテゴリタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツカテゴリ
     */
    public function test_category_type_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'type' => Item::type(),
        ])->create();

        // 実行
        $this->assertThrows(function () use ($profile, $category) {
            $profile->locations()->create([
                'title' => 'テスト場所',
                'latitude' => 35.681236,
                'longitude' => 139.767125,
                'zoom' => 15,
                'category' => $category,
            ]);
        }, ApplicationException::class, 'CategoryContentTypeMissmatch');
    }

    /**
     * コンテンツカテゴリ
     * 
     * - カテゴリ名を指定した場合は、カテゴリ所有プロフィールとコンテンツ所有プロフィールが一致し、かつコンテンツ種別と同じカテゴリタイプのカテゴリの中からカテゴリ名が一致するカテゴリのIDが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツカテゴリ
     */
    public function test_category_name()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'name' => 'テストカテゴリ',
            'type' => Location::type(),
        ])->create();

        // 実行
        $location = $profile->locations()->create([
            'title' => 'テスト場所',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'category' => 'テストカテゴリ',
        ]);

        // 評価
        $this->assertEquals($category->id, $location->category->id, 'カテゴリ名を指定した場合は、カテゴリ所有プロフィールとコンテンツ所有プロフィールが一致し、かつコンテンツ種別と同じカテゴリタイプのカテゴリの中からカテゴリ名が一致するカテゴリのIDが設定されること');
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'category_id' => $category->id,
        ]);
    }

    /**
     * コンテンツカテゴリ
     * 
     * - 一致するカテゴリが存在しない場合は無視されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツカテゴリ
     */
    public function test_category_name_nomatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        Category::factory([
            'profile_id' => $profile->id,
            'name' => 'テストカテゴリ',
            'type' => Location::type(),
        ])->create();

        // 実行
        $location = $profile->locations()->create([
            'title' => 'テスト場所',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'category' => 'テストカテゴリ2',
        ]);

        // 評価
        $this->assertNull($location->category, '一致するカテゴリが存在しない場合は無視されること');
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'category_id' => null,
        ]);
    }

    /**
     * コンテンツカテゴリ
     * 
     * - 対応するカテゴリが削除された場合は、自動的にNullが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツカテゴリ
     */
    public function test_category_delete()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'name' => 'テストカテゴリ',
            'type' => Location::type(),
        ])->create();
        $location = Location::factory([
            'profile_id' => $profile->id,
            'category_id' => $category->id,
        ])->create();
        $this->assertNotNull($location->category);

        // 実行
        $category->delete();
        $location->refresh();

        // 評価
        $this->assertNull($location->category, '対応するカテゴリが削除された場合は、自動的にNullが設定されること');
    }

    /**
     * コンテンツタグリスト
     * 
     * - タグ付けできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツタグリスト
     */
    public function test_tags()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Location::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Location::type(),
        ]);

        // 実行
        $location = $profile->locations()->create([
            'title' => 'テスト場所',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'tags' => [$tag1, $tag2],
        ]);

        // 評価
        $this->assertEquals(2, $location->tags->count(), 'タグ付けできること');
        foreach ($location->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $location->id,
                'taggable_type' => Location::type(),
            ]);
        }
    }

    /**
     * コンテンツタグリスト
     * 
     * - タグIDを指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツタグリスト
     */
    public function test_tags_id()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Location::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Location::type(),
        ]);

        // 実行
        $location = $profile->locations()->create([
            'title' => 'テスト場所',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'tags' => [$tag1->id, $tag2->id],
        ]);

        // 評価
        $this->assertEquals(2, $location->tags->count(), 'タグIDを指定できること');
        foreach ($location->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $location->id,
                'taggable_type' => Location::type(),
            ]);
        }
    }

    /**
     * コンテンツタグリスト
     * 
     * - タグ所有プロフィールがコンテンツ所有プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツタグリスト
     */
    public function test_tags_profile_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $otherProfile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Location::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Location::type(),
        ]);

        // 実行
        $this->assertThrows(function () use ($otherProfile, $tag1, $tag2) {
            $otherProfile->locations()->create([
                'title' => 'テスト場所',
                'latitude' => 35.681236,
                'longitude' => 139.767125,
                'zoom' => 15,
                'tags' => [$tag1->id, $tag2->id],
            ]);
        }, ApplicationException::class, 'TagContentProfileMissmatch');
    }

    /**
     * コンテンツタグリスト
     * 
     * - タグタイプがコンテンツ種別と一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツタグリスト
     */
    public function test_tags_type_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Location::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Item::type(),
        ]);

        // 実行
        $this->assertThrows(function () use ($profile, $tag1, $tag2) {
            $profile->locations()->create([
                'title' => 'テスト場所',
                'latitude' => 35.681236,
                'longitude' => 139.767125,
                'zoom' => 15,
                'tags' => [$tag1->id, $tag2->id],
            ]);
        }, ApplicationException::class, 'TagContentTypeMissmatch');
    }

    /**
     * コンテンツタグリスト
     * 
     * - タグ名を指定した場合は、タグ所有プロフィールとコンテンツ所有プロフィールが一致し、かつコンテンツ種別と同じタグタイプのタグの中からタグ名が一致するタグのIDが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツタグリスト
     */
    public function test_tags_name()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Location::type(),
        ]);
        $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Location::type(),
        ]);

        // 実行
        $location = $profile->locations()->create([
            'title' => 'テスト場所',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'tags' => ['タグ1', 'タグ2'],
        ]);

        // 評価
        $this->assertEquals(2, $location->tags->count(), 'タグ名を指定した場合は、タグ所有プロフィールとコンテンツ所有プロフィールが一致し、かつコンテンツ種別と同じタグタイプのタグの中からタグ名が一致するタグのIDが設定されること');
        foreach ($location->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $location->id,
                'taggable_type' => Location::type(),
            ]);
        }
    }

    /**
     * コンテンツタグリス
     * 
     * - 一致するタグが存在しない場合は無視されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツタグリスト
     */
    public function test_tags_name_nomatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Location::type(),
        ]);
        $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Location::type(),
        ]);

        // 実行
        $location = $profile->locations()->create([
            'title' => 'テスト場所',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'tags' => ['タグ3', 'タグ2'],
        ]);

        // 評価
        $this->assertEquals(1, $location->tags->count(), '一致するタグが存在しない場合は無視されること');
        foreach ($location->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $location->id,
                'taggable_type' => Location::type(),
            ]);
        }
    }

    /**
     * コンテンツタグリスト
     * 
     * - 対応するタグが削除された場合は、コンテンツタグリストから自動的に除外されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツタグリスト
     */
    public function test_tags_delete()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Location::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Location::type(),
        ]);
        $location = $profile->locations()->create([
            'title' => 'テスト場所',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'tags' => [$tag1, $tag2],
        ]);

        // 実行
        $tag1->delete();

        // 評価
        $this->assertEquals(1, $location->tags->count(), '対応するタグが削除された場合は、コンテンツタグリストから自動的に除外されること');
        foreach ($location->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $location->id,
                'taggable_type' => Location::type(),
            ]);
        }
    }

    /**
     * コンテンツレコードリスト
     * 
     * - レコーダによって記録された場所のレコードリストであることを確認します。
     * - レコーダの指定は、レコーダそのものを指定することができることを確認します。
     * - レコーダの指定は、レコーダIDを指定することができることを確認します。
     * - レコーダの指定は、レコーダ名を指定することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツレコードリスト
     */
    public function test_content_records()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $location = Location::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder1 = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Location::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);
        $recorder2 = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Location::type(),
            'data_type' => 'bool',
            'name' => 'テストレコーダ2',
        ]);
        $recorder3 = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Location::type(),
            'data_type' => 'date',
            'name' => 'テストレコーダ3',
        ]);

        // 実行
        $location->record($recorder1, 1);
        $location->record($recorder2->id, true);
        $location->record('テストレコーダ3', '2025-04-22');

        // 評価
        $this->assertEquals(3, $location->records->count(), 'レコーダによって記録された場所のレコードリストであること');
        foreach ($location->records as $i => $record) {
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
     * コンテンツレコードリスト
     * 
     * - レコーダを指定する場合は、レコーダ所有プロフィールがコンテンツ所有プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツレコードリスト
     */
    public function test_content_records_recorder_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $location = Location::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder = Recorder::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
            'type' => Location::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);

        // 実行
        $this->expectException(ApplicationException::class);
        $this->expectExceptionCode(73007);
        $location->record($recorder, 1);
    }

    /**
     * コンテンツレコードリスト
     * 
     * - レコーダを指定する場合は、レコーダタイプがコンテンツ種別と一致していることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツレコードリスト
     */
    public function test_content_records_recorder_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $location = Location::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Item::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);

        // 実行
        $this->expectException(ApplicationException::class);
        $this->expectExceptionCode(73008);
        $location->record($recorder, 1);
    }

    /**
     * コンテンツレコードリスト
     * 
     * - レコーダIDを指定する場合は、レコーダ所有プロフィールがコンテンツ所有プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツレコードリスト
     */
    public function test_content_records_recorder_id_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $location = Location::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder = Recorder::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
            'type' => Location::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);

        // 実行
        $this->expectException(ApplicationException::class);
        $this->expectExceptionCode(73007);
        $location->record($recorder->id, 1);
    }

    /**
     * コンテンツレコードリスト
     * 
     * - レコーダIDを指定する場合は、レコーダタイプがコンテンツ種別と一致していることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツレコードリスト
     */
    public function test_content_records_recorder_id_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $location = Location::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Item::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);

        // 実行
        $this->expectException(ApplicationException::class);
        $this->expectExceptionCode(73008);
        $location->record($recorder->id, 1);
    }

    /**
     * コンテンツレコードリスト
     * 
     * - 対応するレコーダが削除された場合は、コンテンツレコードリストからも自動的に除外されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツレコードリスト
     */
    public function test_content_records_recorder_delete()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $location = Location::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Location::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);

        // 実行
        $location->record($recorder, 1);
        $recorder->delete();

        // 評価
        $this->assertEquals(0, $location->records->count(), '対応するレコーダが削除された場合は、コンテンツレコードリストからも自動的に除外されること');
        $this->assertDatabaseEmpty('records');
    }

    /**
     * コンテンツ内容
     * 
     * - 取得時にHTMLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ内容
     */
    public function test_content_value_html_cast_hook_get()
    {

        // 準備
        Config::set(HTML::CONFIG_KEY_HTML_CAST_HOOKS, [
            CustomHtmlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>テストコンテンツ</p>';
        $location = Location::factory()->create([
            'profile_id' => $profile->id,
            'value' => $value,
        ]);

        // 実行
        $expected = $location->value;

        // 評価
        $this->assertEquals(CustomHtmlHook::PREFIX . $value, $expected, '取得時にHTMLキャストフックが利用できることを確認します。');
        $this->assertDatabaseHas('locations', [
            'profile_id' => $profile->id,
            'value' => $value,
        ]);
    }

    /**
     * コンテンツ内容
     * 
     * - 設定時にHTMLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ内容
     */
    public function test_content_value_html_cast_hook_set()
    {

        // 準備
        Config::set(HTML::CONFIG_KEY_HTML_CAST_HOOKS, [
            CustomHtmlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>テストコンテンツ</p>';

        // 実行
        $profile->locations()->create([
            'title' => 'テスト場所',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'value' => CustomHtmlHook::PREFIX . $value,
        ]);

        // 評価
        // 設定時にHTMLキャストフックが利用できること
        $this->assertDatabaseHas('locations', [
            'profile_id' => $profile->id,
            'value' => $value,
        ]);
    }

    /**
     * 場所サムネイル
     * 
     * - 取得時にURLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#場所サムネイル
     */
    public function test_thumbnail_url_cast_hook_get()
    {
        // 準備
        Config::set(URL::CONFIG_KEY_URL_CAST_HOOKS, [
            CustomUrlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $thumbnail = 'test_thumbnail.jpg';
        $location = Location::factory()->create([
            'profile_id' => $profile->id,
            'thumbnail' => $thumbnail,
        ]);

        // 実行
        $expected = $location->thumbnail;

        // 評価
        $this->assertEquals(CustomUrlHook::PREFIX . $thumbnail, $expected, '取得時にURLキャストフックが利用できることを確認します。');
        $this->assertDatabaseHas('locations', [
            'profile_id' => $profile->id,
            'thumbnail' => $thumbnail,
        ]);
    }

    /**
     * 場所サムネイル
     * 
     * - 設定時にURLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#場所サムネイル
     */
    public function test_thumbnail_url_cast_hook_set()
    {
        // 準備
        Config::set(URL::CONFIG_KEY_URL_CAST_HOOKS, [
            CustomUrlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $thumbnail = 'test_thumbnail.jpg';

        // 実行
        $profile->locations()->create([
            'title' => 'テスト場所',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'thumbnail' => CustomUrlHook::PREFIX . $thumbnail,
        ]);

        // 評価
        // 設定時にURLキャストフックが利用できること
        $this->assertDatabaseHas('locations', [
            'profile_id' => $profile->id,
            'thumbnail' => $thumbnail,
        ]);
    }

    /**
     * 新規作成
     * 
     * - 場所の作成は、場所を追加したいプロフィールの場所リストに追加することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#新規作成
     */
    public function test_create()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $posted_at = now();
        $latitude = 35.681236;
        $longitude = 139.767125;
        $zoom = 15;

        // 実行
        $location = $profile->locations()->create([
            'posted_at' => $posted_at,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoom' => $zoom,
        ]);

        // 評価
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'profile_id' => $profile->id,
            'posted_at' => $posted_at,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoom' => $zoom,
        ]);
    }

    /**
     * 新規作成
     * 
     * - 緯度は必須であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#新規作成
     */
    public function test_create_latitude_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->expectException(ApplicationException::class);
        $this->expectExceptionCode(40001);
        $profile->locations()->create([
            'longitude' => 139.767125,
            'zoom' => 15,
        ]);
    }

    /**
     * 新規作成
     * 
     * - 経度は必須であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#新規作成
     */
    public function test_create_longitude_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->expectException(ApplicationException::class);
        $this->expectExceptionCode(40002);
        $profile->locations()->create([
            'latitude' => 35.681236,
            'zoom' => 15,
        ]);
    }

    /**
     * 新規作成
     * 
     * - 縮尺は必須であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#新規作成
     */
    public function test_create_zoom_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->expectException(ApplicationException::class);
        $this->expectExceptionCode(40003);
        $profile->locations()->create([
            'latitude' => 35.681236,
            'longitude' => 139.767125,
        ]);
    }

    /**
     * 新規作成
     * 
     * - コンテンツ投稿日時を省略した場合は、システム日時が設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#新規作成
     */
    public function test_create_posted_at_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $latitude = 35.681236;
        $longitude = 139.767125;
        $zoom = 15;

        // 実行
        $location = $profile->locations()->create([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoom' => $zoom,
        ]);

        // 評価
        $this->assertNotNull($location->posted_at, 'コンテンツ投稿日時を省略した場合は、システム日時が設定されること');
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'profile_id' => $profile->id,
            'posted_at' => $location->posted_at, // システム日時が設定されていること
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoom' => $zoom,
        ]);
    }
}
