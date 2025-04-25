<?php

namespace Tests\Feature;

use Auth;
use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Category;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\PublicLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
     * コンテンツタイトル
     * 
     * - 登録時に必ず指定する必要があることを確認します。
     * - 例外コード:40001のメッセージであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツタイトル
     */
    public function test_title_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->locations()->create([
                'latitude' => 35.681236,
                'longitude' => 139.767125,
                'zoom' => 15,
            ]);
        }, ApplicationException::class, 'LocationTitleRequired');
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
}
