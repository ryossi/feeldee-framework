<?php

namespace Tests\Feature;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Category;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\PublicLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PhotoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * コンテンツ種別
     * 
     * - 写真のコンテンツ種別は、"photo"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツ種別
     */
    public function test_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
        ]);

        // 検証
        $this->assertEquals('photo', $photo->type(), '写真のコンテンツ種別は、"photo"であること');
    }

    /**
     * コンテンツ所有プロフィール
     * 
     * - 写真を作成したユーザのプロフィールあることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#ココンテンツ所有プロフィール
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
        ]);

        // 検証
        $this->assertEquals($profile->id, $photo->profile->id, '写真を作成したユーザのプロフィールあること');
        $this->assertDatabaseHas('photos', [
            'profile_id' => $profile->id,
        ]);
    }

    /**
     * コンテンツ公開フラグ
     * 
     * - デフォルトは、非公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツ公開フラグ
     */
    public function test_is_public_default()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
        ]);

        // 評価
        $this->assertFalse($photo->isPublic, 'デフォルトは、非公開であること');
    }

    /**
     * コンテンツ公開フラグ
     * 
     * - 公開できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツ公開フラグ
     */
    public function test_is_public_doPublic()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo = Photo::factory([
            'is_public' => false,
            'profile_id' => $profile->id,
        ])->create();

        // 実行
        $photo->doPublic();

        // 評価
        $this->assertTrue($photo->isPublic, '公開できること');
    }

    /**
     * コンテンツ公開フラグ
     * 
     * - 非公開にできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツ公開フラグ
     */
    public function test_is_public_doPrivate()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo = Photo::factory([
            'is_public' => true,
            'profile_id' => $profile->id,
        ])->create();

        // 実行
        $photo->doPrivate();

        // 評価
        $this->assertFalse($photo->isPublic, '非公開にできること');
    }

    /**
     * コンテンツ公開レベル
     * 
     * - デフォルトは、"自分"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツ公開レベル
     */
    public function test_public_level_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
        ]);

        // 評価
        $this->assertEquals(PublicLevel::Private, $photo->public_level, 'デフォルトは、"自分"であること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'public_level' => PublicLevel::Private,
        ]);
    }

    /**
     * コンテンツ公開レベル
     * 
     * - コンテンツ公開レベルを指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツ公開レベル
     */
    public function test_public_level()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
            'public_level' => PublicLevel::Member,
        ]);

        // 評価
        $this->assertEquals(PublicLevel::Member, $photo->public_level, 'コンテンツ公開レベルを指定できること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'public_level' => PublicLevel::Member,
        ]);
    }

    /**
     * コンテンツ公開レベル
     * 
     * - コンテンツ公開レベルを変更できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツ公開レベル
     */
    public function test_public_level_update()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo = Photo::factory([
            'profile_id' => $profile->id,
        ])->create();

        // 実行
        $photo->public_level = PublicLevel::Member;
        $photo->save();

        // 評価
        $this->assertEquals(PublicLevel::Member, $photo->public_level, 'コンテンツ公開レベルを変更できること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'public_level' => PublicLevel::Member,
        ]);
    }

    /**
     * コンテンツカテゴリ
     * 
     * - カテゴリを指定できることを確認します。
     * - 指定したカテゴリのカテゴリ所有プロフィールが、コンテンツ所有プロフィールと一致していることを確認します。
     * - 指定したカテゴリが、写真のカテゴリであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツカテゴリ
     */
    public function test_category()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'type' => Photo::type(),
        ])->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
            'category' => $category,
        ]);

        // 評価
        $this->assertEquals($category->id, $photo->category->id, 'カテゴリを指定できること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'category_id' => $category->id,
        ]);
    }

    /**
     * コンテンツカテゴリ
     * 
     * - カテゴリIDを指定できることを確認します。
     * - 指定したカテゴリのカテゴリ所有プロフィールが、コンテンツ所有プロフィールと一致していることを確認します。
     * - 指定したカテゴリが、写真のカテゴリであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツカテゴリ
     */
    public function test_category_id()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'type' => Photo::type(),
        ])->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
            'category_id' => $category->id,
        ]);

        // 評価
        $this->assertEquals($category->id, $photo->category->id, 'カテゴリを指定できること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'category_id' => $category->id,
        ]);
    }

    /**
     * コンテンツカテゴリ
     * 
     * - カテゴリ所有プロフィールがコンテンツ所有プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツカテゴリ
     */
    public function test_category_profile_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $otherProfile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'type' => Photo::type(),
        ])->create();

        // 実行
        $this->assertThrows(function () use ($otherProfile, $category) {
            $otherProfile->photos()->create([
                'src' => '/mbox/photo.jpg',
                'regist_datetime' => now(),
                'category_id' => $category->id,
            ]);
        }, ApplicationException::class, 'CategoryContentProfileMissmatch');
    }

    /**
     * コンテンツカテゴリ
     * 
     * - コンテンツ種別と同じカテゴリタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツカテゴリ
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
            $profile->photos()->create([
                'src' => '/mbox/photo.jpg',
                'regist_datetime' => now(),
                'category' => $category,
            ]);
        }, ApplicationException::class, 'CategoryContentTypeMissmatch');
    }

    /**
     * コンテンツカテゴリ
     * 
     * - カテゴリ名を指定した場合は、カテゴリ所有プロフィールとコンテンツ所有プロフィールが一致し、かつコンテンツ種別と同じカテゴリタイプのカテゴリの中からカテゴリ名が一致するカテゴリのIDが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツカテゴリ
     */
    public function test_category_name()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'name' => 'テストカテゴリ',
            'type' => Photo::type(),
        ])->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
            'category' => 'テストカテゴリ',
        ]);

        // 評価
        $this->assertEquals($category->id, $photo->category->id, 'カテゴリ名を指定した場合は、カテゴリ所有プロフィールとコンテンツ所有プロフィールが一致し、かつコンテンツ種別と同じカテゴリタイプのカテゴリの中からカテゴリ名が一致するカテゴリのIDが設定されること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'category_id' => $category->id,
        ]);
    }

    /**
     * コンテンツカテゴリ
     * 
     * - 一致するカテゴリが存在しない場合は無視されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツカテゴリ
     */
    public function test_category_name_nomatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        Category::factory([
            'profile_id' => $profile->id,
            'name' => 'テストカテゴリ',
            'type' => Photo::type(),
        ])->create();

        // 実行
        $photo = $profile->photos()->create([
            'title' => 'テスト写真',
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
            'category' => 'テストカテゴリ2',
        ]);

        // 評価
        $this->assertNull($photo->category, '一致するカテゴリが存在しない場合は無視されること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'category_id' => null,
        ]);
    }

    /**
     * コンテンツカテゴリ
     * 
     * - 対応するカテゴリが削除された場合は、自動的にNullが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツカテゴリ
     */
    public function test_category_delete()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'name' => 'テストカテゴリ',
            'type' => Photo::type(),
        ])->create();
        $photo = Photo::factory([
            'profile_id' => $profile->id,
            'category_id' => $category->id,
        ])->create();
        $this->assertNotNull($photo->category);

        // 実行
        $category->delete();
        $photo->refresh();

        // 評価
        $this->assertNull($photo->category, '対応するカテゴリが削除された場合は、自動的にNullが設定されること');
    }

    /**
     * 写真タイトル
     * 
     * - 登録した写真に付けるタイトルであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#写真タイトル
     */
    public function test_title()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $title = '写真のタイトル';

        // 実行
        $photo = $profile->photos()->create([
            'title' => $title,
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
        ]);

        // 検証
        $this->assertEquals($title, $photo->title, '登録した写真に付けるタイトルであること');
    }

    /**
     * 写真内容
     * 
     * - 写真の説明またはメモ書きなどであることを確認します。
     * - HTMLが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#写真内容
     */
    public function test_value_html()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>写真内容の本文</p>';

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($value, $photo->value, '写真の説明またはメモ書きなどであること');
        // HTMLが使用できること
        $this->assertDatabaseHas('photos', [
            'value' => $value,
        ]);
    }

    /**
     * 写真内容
     * 
     * - 写真の説明またはメモ書きなどであることを確認します。
     * - テキストが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#写真内容
     */
    public function test_value_text()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '写真の本文';

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($value, $photo->value, '写真の説明またはメモ書きなどであること');
        // テキストが使用できること
        $this->assertDatabaseHas('photos', [
            'value' => $value,
        ]);
    }

    /**
     * 写真テキスト
     * 
     * - 写真の内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - 写真の登録時に、自動変換されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#写真テキスト
     */
    public function test_text_create()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>写真内容の本文</p>';
        $expected = '写真内容の本文';

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($expected, $photo->text, '写真の内容から、HTMLタグのみを排除したテキスト表現であること');
        // 写真の登録時に、自動変換されること
        $this->assertDatabaseHas('photos', [
            'text' => $expected,
        ]);
    }

    /**
     * 写真テキスト
     * 
     * - 写真の内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - 写真の編集時に、自動変換されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#写真テキスト
     */
    public function test_text_update()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Photo::factory()->count(1))->create();
        $photo = $profile->photos->first();
        $value = '<p>写真内容の本文</p>';
        $expected = '写真内容の本文';

        // 実行
        $photo->update([
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($expected, $photo->text, '写真の内容から、HTMLタグのみを排除したテキスト表現であること');
        // 写真の編集時に、自動変換されること
        $this->assertDatabaseHas('photos', [
            'text' => $expected,
        ]);
    }
}
