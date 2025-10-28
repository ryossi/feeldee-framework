<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Feeldee\Framework\Casts\HTML;
use Feeldee\Framework\Casts\URL;
use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Journal;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\PublicLevel;
use Feeldee\Framework\Models\Recorder;
use Feeldee\Framework\Observers\PostPhotoShareObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\Hooks\CustomHtmlHook;
use Tests\Hooks\CustomUrlHook;
use Tests\TestCase;

class PhotoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 投稿タイプ
     * 
     * - 写真の投稿タイプは、"photo"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿タイプ
     */
    public function test_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
        ]);

        // 検証
        $this->assertEquals('photo', $photo->type(), '写真の投稿タイプは、"photo"であること');
    }

    /**
     * 投稿者プロフィール
     * 
     * - 写真を作成したユーザのプロフィールあることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コ投稿者プロフィール
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
        ]);

        // 検証
        $this->assertEquals($profile->id, $photo->profile->id, '写真を作成したユーザのプロフィールあること');
        $this->assertDatabaseHas('photos', [
            'profile_id' => $profile->id,
        ]);
    }

    /**
     * 投稿タイトル
     * 
     * - 登録した写真に付けるタイトルであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿タイトル
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
            'posted_at' => now(),
        ]);

        // 検証
        $this->assertEquals($title, $photo->title, '登録した写真に付けるタイトルであること');
    }

    /**
     * 投稿内容
     * 
     * - 写真の説明またはメモ書きなどであることを確認します。
     * - HTMLが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿内容
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
            'posted_at' => now(),
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
     * 投稿内容
     * 
     * - 写真の説明またはメモ書きなどであることを確認します。
     * - テキストが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿内容
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
            'posted_at' => now(),
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
     * 投稿内容テキスト
     * 
     * - 投稿内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - 投稿内容の登録時に、自動変換されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿内容テキスト
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
            'posted_at' => now(),
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($expected, $photo->text, '投稿内容から、HTMLタグのみを排除したテキスト表現であること');
        // 投稿内容の登録時に、自動変換されること
        $this->assertDatabaseHas('photos', [
            'text' => $expected,
        ]);
    }

    /**
     * 投稿内容テキスト
     * 
     * - 投稿内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - 投稿内容の編集時に、自動変換されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿内容テキスト
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
        $this->assertEquals($expected, $photo->text, '投稿内容から、HTMLタグのみを排除したテキスト表現であること');
        // 投稿内容の編集時に、自動変換されること
        $this->assertDatabaseHas('photos', [
            'text' => $expected,
        ]);
    }

    /**
     * 投稿公開フラグ
     * 
     * - デフォルトは、非公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿公開フラグ
     */
    public function test_is_public_default()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
        ]);

        // 評価
        $this->assertFalse($photo->isPublic, 'デフォルトは、非公開であること');
    }

    /**
     * 投稿公開フラグ
     * 
     * - 公開できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿公開フラグ
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
     * 投稿公開フラグ
     * 
     * - 非公開にできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿公開フラグ
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
     * 投稿公開レベル
     * 
     * - デフォルトは、"自分"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿公開レベル
     */
    public function test_public_level_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
        ]);

        // 評価
        $this->assertEquals(PublicLevel::Private, $photo->public_level, 'デフォルトは、"自分"であること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'public_level' => PublicLevel::Private,
        ]);
    }

    /**
     * 投稿公開レベル
     * 
     * - 投稿公開レベルを指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿公開レベル
     */
    public function test_public_level()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
            'public_level' => PublicLevel::Member,
        ]);

        // 評価
        $this->assertEquals(PublicLevel::Member, $photo->public_level, '投稿公開レベルを指定できること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'public_level' => PublicLevel::Member,
        ]);
    }

    /**
     * 投稿公開レベル
     * 
     * - 投稿公開レベルを変更できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿公開レベル
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
        $this->assertEquals(PublicLevel::Member, $photo->public_level, '投稿公開レベルを変更できること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'public_level' => PublicLevel::Member,
        ]);
    }

    /**
     * 写真リスト
     * 
     * - 記事内容に写真が使用されている投稿のコレクションであることを確認します。
     * - 写真を削除しても、関連する投稿の記事内容には影響はないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿リスト
     */
    public function test_posts()
    {
        // 準備
        Journal::observe(PostPhotoShareObserver::class);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = 'これは写真リストのテストです。<br>
                1枚目の写真:<img src="http://photo.test/img/1.png" /><br>
                2枚目の写真:<img src="http://photo.test/img/2.png" /><br>
                3枚目の写真:<img src="http://photo.test/img/2.png" /><br3
                ';

        // 実行
        $journalA = $profile->journals()->create([
            'posted_at' => Carbon::parse('2025-04-22'),
            'title' => '投稿A',
            'value' => $value,
        ]);
        $journalB = $profile->journals()->create([
            'posted_at' => Carbon::parse('2025-04-23'),
            'title' => '投稿B',
        ]);
        $journalB->value = '
                これは写真リストのテストです。<br>
                1枚目の写真:<img src="http://photo.test/img/2.png" /><br>
                2枚目の写真:<img src="http://photo.test/img/3.png" /><br>
                3枚目の写真:<img src="http://photo.test/img/4.png" /><br>
                ';
        $journalB->save();
        $photo1 = $profile->photos()->ofSrc('http://photo.test/img/1.png')->first();
        $photo1->delete();

        // 評価
        $photo2 = $profile->photos()->ofSrc('http://photo.test/img/2.png')->first();
        $this->assertEquals(2, $photo2->relatedJournals->count(), '投稿内容に写真が使用されている記録のコレクションであること');
        $this->assertEquals($value, $journalA->value, '写真を削除しても、関連する投稿の記事内容には影響はないこと');
    }

    /**
     * 投稿内容
     * 
     * - 取得時にHTMLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿内容
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
        $photo = Photo::factory()->create([
            'profile_id' => $profile->id,
            'value' => $value,
        ]);

        // 実行
        $expected = $photo->value;

        // 評価
        $this->assertEquals(CustomHtmlHook::PREFIX . $value, $expected, '取得時にHTMLキャストフックが利用できることを確認します。');
        $this->assertDatabaseHas('photos', [
            'profile_id' => $profile->id,
            'value' => $value,
        ]);
    }

    /**
     * 投稿内容
     * 
     * - 設定時にHTMLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿内容
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
        $profile->photos()->create([
            'title' => 'テスト写真',
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
            'value' => CustomHtmlHook::PREFIX . $value,
        ]);

        // 評価
        // 設定時にHTMLキャストフックが利用できること
        $this->assertDatabaseHas('photos', [
            'profile_id' => $profile->id,
            'value' => $value,
        ]);
    }

    /**
     * 写真ソース
     * 
     * - 取得時にURLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#写真ソース
     */
    public function test_src_url_cast_hook_get()
    {
        // 準備
        Config::set(URL::CONFIG_KEY_URL_CAST_HOOKS, [
            CustomUrlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $src = 'http://photo.test/img/photo.jpg';
        $photo = Photo::factory()->create([
            'profile_id' => $profile->id,
            'src' => $src,
        ]);

        // 実行
        $expected = $photo->src;

        // 評価
        $this->assertEquals(CustomUrlHook::PREFIX . $src, $expected, '取得時にURLキャストフックが利用できることを確認します。');
        $this->assertDatabaseHas('photos', [
            'profile_id' => $profile->id,
            'src' => $src,
        ]);
    }

    /**
     * 写真ソース
     * 
     * - 設定時にURLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#写真ソース
     */
    public function test_src_url_cast_hook_set()
    {
        // 準備
        Config::set(URL::CONFIG_KEY_URL_CAST_HOOKS, [
            CustomUrlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $src = 'http://photo.test/img/photo.jpg';

        // 実行
        $profile->photos()->create([
            'title' => 'テスト写真',
            'src' => CustomUrlHook::PREFIX . $src,
            'posted_at' => now(),
        ]);

        // 評価
        // 設定時にURLキャストフックが利用できること
        $this->assertDatabaseHas('photos', [
            'profile_id' => $profile->id,
            'src' => $src,
        ]);
    }

    /**
     * 新規作成
     * 
     * - 写真の作成は、写真を追加したいプロフィールの写真リストに追加することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#新規作成
     */
    public function test_create()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $src = '/mbox/photo.jpg';
        $postedAt = now();

        // 実行
        $photo = $profile->photos()->create([
            'src' => $src,
            'posted_at' => $postedAt,
        ]);

        // 評価
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'profile_id' => $profile->id,
            'src' => $src,
            'posted_at' => $postedAt,
        ]);
    }

    /**
     * 新規作成
     * 
     * - 写真ソースは、必須であることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#新規作成
     */
    public function test_create_src_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->photos()->create([
                'posted_at' => now(),
            ]);
        }, ApplicationException::class, 'PhotoSrcRequired');
    }

    /**
     * 新規作成
     * 
     * - 投稿日時を省略した場合は、システム日時が設定されることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#新規作成
     */
    public function test_create_posted_at_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $src = '/mbox/photo.jpg';

        // 実行
        $photo = $profile->photos()->create([
            'src' => $src,
        ]);

        // 評価
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'profile_id' => $profile->id,
            'src' => $src,
            'posted_at' => $photo->posted_at->format('Y-m-d H:i:s'), // システム日時が設定されていること
        ]);
    }

    /**
     * 新規作成
     *
     * - 写真リストのデフォルトの並び順は、投稿日時降順（最新順）であることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#新規作成
     */
    public function test_collection_sort_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        $postA = Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-22 10:00:00'),
        ]);
        $postB = Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-23 10:00:00'),
        ]);
        $postC = Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-21 10:00:00'),
        ]);

        // 実行
        $photos = Profile::nickname('Feeldee')->first()->photos;

        // 評価
        $this->assertEquals(3, $photos->count());
        $this->assertEquals($postB->id, $photos[0]->id);
        $this->assertEquals($postA->id, $photos[1]->id);
        $this->assertEquals($postC->id, $photos[2]->id);
    }

    /**
     * 写真タイプの自動判別
     *
     * - 写真タイプは、写真ソースの設定時に写真タイプマッピングコンフィグレーションの設定値に従って自動判定されることを確認します。
     * - 写真タイプマッピングコンフィグレーションは、写真タイプの値と写真ソースのURLを評価するための文字列（正規表現）の連想配列で指定することを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#写真タイプの自動判定
     */
    public function test_photo_type_auto_detection()
    {
        // 準備
        Config::set('feeldee.photo_types', [
            'google' => '/^(?:https?:\/\/)?photos\.google\.com\//',
            'amazon' => '/^(?:https?:\/\/)?www\.amazon\.com\/photos\//',
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photoGoogle = $profile->photos()->create([
            'src' => 'https://photos.google.com/albums/ABC123',
            'posted_at' => now(),
        ]);
        $photoAmazon = $profile->photos()->create([
            'src' => 'https://www.amazon.com/photos/user/XYZ789',
            'posted_at' => now(),
        ]);
        $photoOther = $profile->photos()->create([
            'src' => 'https://example.com/images/photo.jpg',
            'posted_at' => now(),
        ]);

        // 評価
        $this->assertEquals('google', $photoGoogle->photo_type, '写真タイプは、写真ソースの設定時に写真タイプマッピングコンフィグレーションの設定値に従って自動判定されること');
        $this->assertEquals('amazon', $photoAmazon->photo_type, '写真タイプは、写真ソースの設定時に写真タイプマッピングコンフィグレーションの設定値に従って自動判定されること');
        $this->assertNull($photoOther->photo_type, '写真タイプマッピングコンフィグレーションに該当しない場合は、nullとなること');
    }

    /**
     * 写真タイプの自動判別
     * 
     * - 写真ソースを変更した場合に、写真タイプが自動判定されることを確認します。
     * - 写真タイプマッピングコンフィグレーションは、写真タイプの値と写真ソースのURLを評価するための文字列（正規表現）の連想配列で指定することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#写真タイプの自動判定
     */
    public function test_photo_type_auto_detection_on_src_update()
    {
        // 準備
        Config::set('feeldee.photo_types', [
            'google' => '/^(?:https?:\/\/)?photos\.google\.com\//',
            'amazon' => '/^(?:https?:\/\/)?www\.amazon\.com\/photos\//',
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo = Photo::factory([
            'src' => 'https://photos.google.com/albums/ABC123',
        ])->for($profile)->create();

        // 実行
        $this->assertEquals('google', $photo->photo_type, '初期の写真タイプは、googleであること');
        $photo->src = 'https://www.amazon.com/photos/user/XYZ789';
        $photo->save();

        // 評価
        $this->assertEquals('amazon', $photo->photo_type, '写真ソースを変更した場合に、写真タイプが自動判定されること');
    }

    /**
     * 写真タイプの自動判別
     * 
     * - 写真タイプを一括で修正する"feeldee:refresh-photo-type"コマンドが利用できることを確認します。
     * - 写真タイプ設定済みのも含めて最新の写真タイプマッピングコンフィグレーション設定値に従って写真タイプを一律更新できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#写真タイプの自動判別
     */
    public function test_refresh_photo_type_command()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo1 = Photo::factory([
            'src' => 'https://photos.google.com/albums/ABC123',
            'photo_type' => null,
        ])->for($profile)->create();
        $photo2 = Photo::factory([
            'src' => 'https://www.amazon.com/photos/user/XYZ789',
            'photo_type' => 'google',
        ])->for($profile)->create();
        $photo3 = Photo::factory([
            'src' => 'https://example.com/images/photo.jpg',
            'photo_type' => 'amazon',
        ])->for($profile)->create();

        Config::set('feeldee.photo_types', [
            'google' => '/^(?:https?:\/\/)?photos\.google\.com\//',
            'amazon' => '/^(?:https?:\/\/)?www\.amazon\.com\/photos\//',
        ]);

        // 実行
        $this->artisan('feeldee:refresh-photo-type')
            ->assertExitCode(0);

        // 評価
        $this->assertEquals('google', $photo1->fresh()->photo_type, '写真タイプ設定済みのも含めて最新の写真タイプマッピングコンフィグレーション設定値に従って写真タイプを一律更新できること');
        $this->assertEquals('amazon', $photo2->fresh()->photo_type, '写真タイプ設定済みのも含めて最新の写真タイプマッピングコンフィグレーション設定値に従って写真タイプを一律更新できること');
        $this->assertNull($photo3->fresh()->photo_type, '写真タイプ設定済みのも含めて最新の写真タイプマッピングコンフィグレーション設定値に従って写真タイプを一律更新できること');
    }

    /**
     *  写真タイプの自動判別
     * 
     * - 写真タイプを一括で修正する"feeldee:refresh-photo-type"コマンドが利用できることを確認します。
     * - nullOnlyオプションを指定した場合に、写真タイプが未設定（null）の写真のみを更新できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#写真タイプの自動判別
     */
    public function test_refresh_photo_type_command_nullOnly()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo1 = Photo::factory([
            'src' => 'https://photos.google.com/albums/ABC123',
            'photo_type' => null,
        ])->for($profile)->create();
        $photo2 = Photo::factory([
            'src' => 'https://www.amazon.com/photos/user/XYZ789',
            'photo_type' => 'google',
        ])->for($profile)->create();

        Config::set('feeldee.photo_types', [
            'google' => '/^(?:https?:\/\/)?photos\.google\.com\//',
            'amazon' => '/^(?:https?:\/\/)?www\.amazon\.com\/photos\//',
        ]);

        // 実行
        $this->artisan('feeldee:refresh-photo-type nullOnly')->assertExitCode(0);

        // 評価
        $this->assertEquals('google', $photo1->fresh()->photo_type, '写真タイプが未設定（null）の写真のみを更新できること');
        $this->assertEquals('google', $photo2->fresh()->photo_type, '写真タイプが未設定（null）の写真のみを更新できること');
    }

    /**
     * 写真タイプの自動判別
     * 
     * - 写真タイプを一括で修正する"feeldee:refresh-photo-type"コマンドが利用できることを確認します。
     * - SQLLite環境で"REGEXP"関数をエミュレートして確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#写真タイプの自動判別
     */
    public function test_photo_type_auto_detection_sqlite_regexp()
    {
        // SQLiteのREGEXP関数をエミュレート
        \DB::connection()->getPdo()->sqliteCreateFunction('REGEXP', function ($pattern, $value) {
            return preg_match($pattern, $value) === 1 ? 1 : 0;
        });

        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo1 = Photo::factory([
            'src' => 'https://photos.google.com/albums/ABC123',
            'photo_type' => null,
        ])->for($profile)->create();
        $photo2 = Photo::factory([
            'src' => 'https://www.amazon.com/photos/user/XYZ789',
            'photo_type' => 'google',
        ])->for($profile)->create();
        $photo3 = Photo::factory([
            'src' => 'https://example.com/images/photo.jpg',
            'photo_type' => 'amazon',
        ])->for($profile)->create();

        Config::set('feeldee.photo_types', [
            'google' => '/^(?:https?:\/\/)?photos\.google\.com\//',
            'amazon' => '/^(?:https?:\/\/)?www\.amazon\.com\/photos\//',
        ]);

        // 実行
        $this->artisan('feeldee:refresh-photo-type')
            ->assertExitCode(0);

        // 評価
        $this->assertEquals('google', $photo1->fresh()->photo_type, '写真タイプ設定済みのも含めて最新の写真タイプマッピングコンフィグレーション設定値に従って写真タイプを一律更新できること');
        $this->assertEquals('amazon', $photo2->fresh()->photo_type, '写真タイプ設定済みのも含めて最新の写真タイプマッピングコンフィグレーション設定値に従って写真タイプを一律更新できること');
        $this->assertNull($photo3->fresh()->photo_type, '写真タイプ設定済みのも含めて最新の写真タイプマッピングコンフィグレーション設定値に従って写真タイプを一律更新できること');
    }
}
