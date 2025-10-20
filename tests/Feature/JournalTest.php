<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Feeldee\Framework\Casts\HTML;
use Feeldee\Framework\Casts\URL;
use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Journal;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\PublicLevel;
use Feeldee\Framework\Models\Recorder;
use Feeldee\Framework\Observers\PostPhotoShareObserver;
use Feeldee\Framework\Observers\PostPhotoSyncObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\Hooks\CustomHtmlHook;
use Tests\Hooks\CustomUrlHook;
use Tests\TestCase;

class JournalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 投稿タイプ
     *
     * - 記録の投稿タイプは、"journal"であることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿タイプ
     */
    public function test_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $post = $profile->journals()->create([
            'title' => 'テスト記録',
            'posted_at' => now(),
        ]);

        // 検証
        $this->assertEquals('journal', $post->type(), '記録の投稿タイプは、"journal"であること');
    }

    /**
     * 投稿者プロフィール
     * 
     * - 投稿を作成したユーザのプロフィールであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿者プロフィール
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $post = $profile->journals()->create([
            'title' => 'テスト記録',
            'posted_at' => now(),
        ]);

        // 検証
        $this->assertEquals($profile->id, $post->profile->id, '投稿を作成したユーザのプロフィールであること');
        $this->assertDatabaseHas('journals', [
            'profile_id' => $profile->id,
        ]);
    }

    /**
     * 投稿タイトル
     * 
     * - 投稿した記事のタイトルであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿タイトル
     */
    public function test_title()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $title = '投稿のタイトル';

        // 実行
        $post = $profile->journals()->create([
            'title' => $title,
            'posted_at' => now(),
        ]);

        // 検証
        $this->assertEquals($title, $post->title, '投稿した記事のタイトルであること');
    }

    /**
     * 投稿内容
     * 
     * - 投稿した記事の本文であることを確認します。
     * - HTMLが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿内容
     */
    public function test_value_html()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>投稿記事の本文</p>';

        // 実行
        $post = $profile->journals()->create([
            'title' => 'テスト記録',
            'posted_at' => now(),
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($value, $post->value, '投稿した記事の本文であること');
        // HTMLが使用できること
        $this->assertDatabaseHas('journals', [
            'value' => $value,
        ]);
    }

    /**
     * 投稿内容
     * 
     * - 投稿した記事の本文であることを確認します。
     * - テキストが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿内容
     */
    public function test_value_text()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '投稿記事の本文';

        // 実行
        $post = $profile->journals()->create([
            'title' => 'テスト記録',
            'posted_at' => now(),
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($value, $post->value, '投稿した記事の本文であること');
        // テキストが使用できること
        $this->assertDatabaseHas('journals', [
            'value' => $value,
        ]);
    }

    /**
     * 投稿内容テキスト
     * 
     * - 投稿内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - 投稿内容の投稿時に、自動変換されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿内容テキスト
     */
    public function test_text_create()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>投稿記事の本文</p>';
        $expected = '投稿記事の本文';

        // 実行
        $post = $profile->journals()->create([
            'title' => 'テスト記録',
            'posted_at' => now(),
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($expected, $post->text, '投稿内容から、HTMLタグのみを排除したテキスト表現であること');
        // 投稿内容の投稿時に、自動変換されること
        $this->assertDatabaseHas('journals', [
            'text' => $expected,
        ]);
    }

    /**
     * 投稿内容テキスト
     * 
     * - 投稿内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - 投稿内容の編集時に、自動変換されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿内容テキスト
     */
    public function test_text_update()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Journal::factory()->count(1))->create();
        $post = $profile->journals->first();
        $value = '<p>投稿内容の本文</p>';
        $expected = '投稿内容の本文';

        // 実行
        $post->update([
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($expected, $post->text, '投稿内容から、HTMLタグのみを排除したテキスト表現であること');
        // 投稿内容の編集時に、自動変換されること
        $this->assertDatabaseHas('journals', [
            'text' => $expected,
        ]);
    }

    /**
     * 投稿公開フラグ
     * 
     * - デフォルトは、非公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿公開フラグ
     */
    public function test_is_public_default()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $post = $profile->journals()->create([
            'title' => 'テスト記録',
            'posted_at' => now(),
        ]);

        // 評価
        $this->assertFalse($post->isPublic, 'デフォルトは、非公開であること');
    }

    /**
     * 投稿公開フラグ
     * 
     * - 公開できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿公開フラグ
     */
    public function test_is_public_doPublic()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $post = Journal::factory([
            'is_public' => false,
            'profile_id' => $profile->id,
        ])->create();

        // 実行
        $post->doPublic();

        // 評価
        $this->assertTrue($post->isPublic, '公開できること');
    }

    /**
     * 投稿公開フラグ
     * 
     * - 非公開にできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿公開フラグ
     */
    public function test_is_public_doPrivate()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $post = Journal::factory([
            'is_public' => true,
            'profile_id' => $profile->id,
        ])->create();

        // 実行
        $post->doPrivate();

        // 評価
        $this->assertFalse($post->isPublic, '非公開にできること');
    }

    /**
     * 投稿公開レベル
     * 
     * - デフォルトは、"自分"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿公開レベル
     */
    public function test_public_level_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $post = $profile->journals()->create([
            'title' => 'テスト記録',
            'posted_at' => now(),
        ]);

        // 評価
        $this->assertEquals(PublicLevel::Private, $post->public_level, 'デフォルトは、"自分"であること');
        $this->assertDatabaseHas('journals', [
            'id' => $post->id,
            'public_level' => PublicLevel::Private,
        ]);
    }

    /**
     * 投稿公開レベル
     * 
     * - 投稿公開レベルを指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿公開レベル
     */
    public function test_public_level()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $post = $profile->journals()->create([
            'title' => 'テスト記録',
            'posted_at' => now(),
            'public_level' => PublicLevel::Public,
        ]);

        // 評価
        $this->assertEquals(PublicLevel::Public, $post->public_level, '投稿公開レベルを指定できること');
        $this->assertDatabaseHas('journals', [
            'id' => $post->id,
            'public_level' => PublicLevel::Public,
        ]);
    }

    /**
     * 投稿公開レベル
     * 
     * - 投稿公開レベルを変更できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿公開レベル
     */
    public function test_public_level_update()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $post = Journal::factory([
            'profile_id' => $profile->id,
        ])->create();

        // 実行
        $post->public_level = PublicLevel::Public;
        $post->save();

        // 評価
        $this->assertEquals(PublicLevel::Public, $post->public_level, '投稿公開レベルを変更できること');
        $this->assertDatabaseHas('journals', [
            'id' => $post->id,
            'public_level' => PublicLevel::Public,
        ]);
    }

    /**
     * 投稿日
     * 
     * - 投稿した日付であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日
     */
    public function test_posted_at()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $posted_at = '2025-04-01';

        // 実行
        $post = $profile->journals()->create([
            'title' => 'テスト記録',
            'posted_at' => $posted_at,
        ]);

        // 検証
        $this->assertEquals($posted_at, $post->posted_at->format('Y-m-d'), '投稿した日付であること');
        $this->assertDatabaseHas('journals', [
            'posted_at' => $posted_at . ' 00:00:00',
        ]);
    }

    /**
     * 投稿サムネイル
     * 
     * - 記録の投稿サムネイルイメージであることを確認します。
     * - URL形式で保存できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿サムネイル
     */
    public function test_thumbnail_url()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $thumbnail = '/path/to/thumbnail.jpg';

        // 実行
        $post = $profile->journals()->create([
            'title' => 'テスト記録',
            'posted_at' => now(),
            'thumbnail' => $thumbnail,
        ]);

        // 検証
        $this->assertEquals($thumbnail, $post->thumbnail, '記録の投稿サムネイルイメージであること');
        // URL形式で保存できること
        $this->assertDatabaseHas('journals', [
            'thumbnail' => $thumbnail,
        ]);
    }

    /**
     * 投稿サムネイル
     *
     * - 記録の投稿サムネイルイメージであることを確認します。
     * - Base64形式で保存できることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿サムネイル
     */
    public function test_thumbnail_base64()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $thumbnail = 'data:image/jpeg;base64,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';

        // 実行
        $post = $profile->journals()->create([
            'title' => 'テスト記録',
            'posted_at' => now(),
            'thumbnail' => $thumbnail,
        ]);

        // 検証
        $this->assertEquals($thumbnail, $post->thumbnail, '記録の投稿サムネイルイメージであること');
        // Base64形式で保存できること
        $this->assertDatabaseHas('journals', [
            'thumbnail' => $thumbnail,
        ]);
    }

    /**
     * 投稿内容
     * 
     * - 取得時にHTMLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿内容
     */
    public function test_content_value_html_cast_hook_get()
    {

        // 準備
        Config::set(HTML::CONFIG_KEY_HTML_CAST_HOOKS, [
            CustomHtmlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>テスト記録</p>';
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
            'value' => $value,
        ]);

        // 実行
        $expected = $post->value;

        // 評価
        $this->assertEquals(CustomHtmlHook::PREFIX . $value, $expected, '取得時にHTMLキャストフックが利用できることを確認します。');
        $this->assertDatabaseHas('journals', [
            'profile_id' => $profile->id,
            'value' => $value,
        ]);
    }

    /**
     * 投稿内容
     * 
     * - 設定時にHTMLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿内容
     */
    public function test_content_value_html_cast_hook_set()
    {

        // 準備
        Config::set(HTML::CONFIG_KEY_HTML_CAST_HOOKS, [
            CustomHtmlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>テスト記録</p>';

        // 実行
        $profile->journals()->create([
            'title' => 'テスト記録',
            'posted_at' => now(),
            'value' => CustomHtmlHook::PREFIX . $value,
        ]);

        // 評価
        // 設定時にHTMLキャストフックが利用できること
        $this->assertDatabaseHas('journals', [
            'profile_id' => $profile->id,
            'value' => $value,
        ]);
    }

    /**
     * 記事サムネイル
     * 
     * - 取得時にURLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#記事サムネイル
     */
    public function test_thumbnail_url_cast_hook_get()
    {
        // 準備
        Config::set(URL::CONFIG_KEY_URL_CAST_HOOKS, [
            CustomUrlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = 'https://example.com/test-thumbnail.jpg';
        $post = Journal::factory()->create([
            'profile_id' => $profile->id,
            'thumbnail' => $value,
        ]);

        // 実行
        $expected = $post->thumbnail;

        // 評価
        $this->assertEquals(CustomUrlHook::PREFIX . $value, $expected, '取得時にURLキャストフックが利用できることを確認します。');
        $this->assertDatabaseHas('journals', [
            'profile_id' => $profile->id,
            'thumbnail' => $value,
        ]);
    }

    /**
     * 記事サムネイル
     * 
     * - 設定時にURLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#記事サムネイル
     */
    public function test_thumbnail_url_cast_hook_set()
    {
        // 準備
        Config::set(URL::CONFIG_KEY_URL_CAST_HOOKS, [
            CustomUrlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = 'https://example.com/test-thumbnail.jpg';

        // 実行
        $profile->journals()->create([
            'title' => 'テスト記録',
            'posted_at' => now(),
            'thumbnail' => CustomUrlHook::PREFIX . $value,
        ]);

        // 評価
        // 設定時にURLキャストフックが利用できること
        $this->assertDatabaseHas('journals', [
            'profile_id' => $profile->id,
            'thumbnail' => $value,
        ]);
    }

    /**
     * 投稿作成
     * 
     * - 投稿の作成は、投稿を追加したいプロフィールの投稿リストに追加することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿作成
     */
    public function test_create_post()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $title = 'テスト記録';
        $value = 'これはテスト記録です。';
        $postedAt = today();

        // 実行
        $post = $profile->journals()->create([
            'title' => $title,
            'posted_at' => $postedAt,
            'value' => $value,
        ]);

        // 評価
        $this->assertDatabaseHas('journals', [
            'id' => $post->id,
            'profile_id' => $profile->id,
            'title' => $title,
            'posted_at' => $postedAt->format('Y-m-d H:i:s'),
            'value' => $value,
        ]);
    }

    /**
     * 投稿作成
     * 
     * - 投稿タイトルは、必須であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿作成
     */
    public function test_create_post_title_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->journals()->create([
                'posted_at' => now(),
            ]);
        }, ApplicationException::class, 'JournalTitleRequired');
    }

    /**
     * 投稿作成
     * 
     * - 投稿日時を省略した場合は、システム日付が設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿作成
     */
    public function test_create_post_posted_at_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $title = 'テスト記録';
        $value = 'これはテスト記録です。';

        // 実行
        $post = $profile->journals()->create([
            'title' => $title,
            'value' => $value,
        ]);

        // 評価
        $this->assertDatabaseHas('journals', [
            'id' => $post->id,
            'profile_id' => $profile->id,
            'title' => $title,
            'posted_at' => today()->format('Y-m-d H:i:s'),
            'value' => $value,
        ]);
    }

    /**
     * 記録リストの並び順
     * 
     * - 記録リストのデフォルトの並び順は、投稿日時降順（最新順）であることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/記録#記録リストの並び順
     */
    public function test_collection_sort_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        $postA = Journal::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-22 10:00:00'),
        ]);
        $postB = Journal::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-23 10:00:00'),
        ]);
        $postC = Journal::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-21 10:00:00'),
        ]);

        // 実行
        $journals = Profile::of('Feeldee')->first()->journals()->get();

        // 評価
        $this->assertEquals(3, $journals->count());
        $this->assertEquals($postB->id, $journals[0]->id);
        $this->assertEquals($postA->id, $journals[1]->id);
        $this->assertEquals($postC->id, $journals[2]->id);
    }

    /**
     * 投稿写真リストの自動維持
     * 
     * - 投稿の記事内容に含まれる写真のコレクションであることを確認します。
     * - 写真ソースは、記事内容の<img />タグのsrc属性の値であることを確認します。
     * - 写真登録日時は、投稿日（時刻は00:00:00）であることを確認します。
     * - 全て個別の写真として登録および更新時に登録されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿写真リストの自動維持
     */
    public function test_photos_sync_mode()
    {
        // 準備
        Journal::observe(PostPhotoSyncObserver::class);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $postA = $profile->journals()->create([
            'posted_at' => Carbon::parse('2025-04-22'),
            'title' => '投稿A',
            'value' => 'これは写真リストのテストです。<br>
                1枚目の写真:<img src="http://photo.test/img/1.png" /><br>
                2枚目の写真:<img src="http://photo.test/img/2.png" /><br>
                3枚目の写真:<img src="http://photo.test/img/2.png" /><br3
                ',
        ]);
        $postB = $profile->journals()->create([
            'posted_at' => Carbon::parse('2025-04-23'),
            'title' => '投稿B',
        ]);
        $postB->value = '
                これは写真リストのテストです。<br>
                1枚目の写真:<img src="http://photo.test/img/2.png" /><br>
                2枚目の写真:<img src="http://photo.test/img/3.png" /><br>
                3枚目の写真:<img src="http://photo.test/img/4.png" /><br>
                ';
        $postB->save();

        // 評価
        $this->assertEquals(2, $postA->photos->count(), '投稿の記事内容に含まれる写真のコレクションであること');
        foreach ($postA->photos as $index => $photo) {
            $fileNo = $index + 1;
            $this->assertEquals("http://photo.test/img/{$fileNo}.png", $photo->src, '写真ソースは、記事内容の<img />タグのsrc属性の値であること');
            $this->assertEquals('2025-04-22 00:00:00', $photo->posted_at->format('Y-m-d H:i:s'), '写真登録日時は、投稿日（時刻は00:00:00）であること');
        }
        $this->assertEquals(3, $postB->photos->count(), '投稿の記事内容に含まれる写真のコレクションであること');
        foreach ($postB->photos as $index => $photo) {
            $fileNo = $index + 2;
            $this->assertEquals("http://photo.test/img/{$fileNo}.png", $photo->src, '写真ソースは、記事内容の<img />タグのsrc属性の値であること');
            $this->assertEquals('2025-04-23 00:00:00', $photo->posted_at->format('Y-m-d H:i:s'), '写真登録日時は、投稿日（時刻は00:00:00）であること');
        }
        $this->assertEquals(5, $profile->photos->count(), '全て個別の写真として登録および更新時に登録されること');
    }

    /**
     * 投稿写真リストの自動維持
     * 
     * - 投稿の削除時には、投稿写真リストに含まれる写真も一緒に削除されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿写真リストの自動維持
     */
    public function test_photos_sync_mode_delete()
    {
        // 準備
        Journal::observe(PostPhotoSyncObserver::class);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $postA = $profile->journals()->create([
            'posted_at' => Carbon::parse('2025-04-22'),
            'title' => '投稿A',
            'value' => 'これは写真リストのテストです。<br>
                1枚目の写真:<img src="http://photo.test/img/1.png" /><br>
                2枚目の写真:<img src="http://photo.test/img/2.png" /><br>
                ',
        ]);
        $postB = $profile->journals()->create([
            'posted_at' => Carbon::parse('2025-04-23'),
            'title' => '投稿B',
            'public_level' => PublicLevel::Member,
        ]);
        $postB->doPublic();
        $postB->value = '
                これは写真リストのテストです。<br>
                1枚目の写真:<img src="http://photo.test/img/2.png" /><br>
                2枚目の写真:<img src="http://photo.test/img/3.png" /><br>
                3枚目の写真:<img src="http://photo.test/img/4.png" /><br>
                ';
        $postB->save();
        $postA->delete();

        // 評価
        $this->assertEquals(3, $profile->photos->count(), '投稿の削除時には、写真リストに含まれる写真も一緒に削除されること');
    }

    /**
     * 投稿写真リストの自動維持
     * 
     * - 投稿の記事内容に含まれる写真のコレクションであることを確認します。
     * - 写真ソースは、記事内容の<img />タグのsrc属性の値であることを確認します。
     * - 写真登録日時は、投稿日（時刻は00:00:00）であることを確認します。
     * - 一致する写真ソースの写真が既に存在する場合には、登録および更新時に写真は登録せずに投稿写真リストに追加のみ行われることを確認します。
     * - 一致する写真ソースの写真が存在しない場合のみ、登録および更新時に写真を登録されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿写真リストの自動維持
     */
    public function test_photos_share_mode()
    {
        // 準備
        Journal::observe(PostPhotoShareObserver::class);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $postA = $profile->journals()->create([
            'posted_at' => Carbon::parse('2025-04-22'),
            'title' => '投稿A',
            'value' => 'これは写真リストのテストです。<br>
                1枚目の写真:<img src="http://photo.test/img/1.png" /><br>
                2枚目の写真:<img src="http://photo.test/img/2.png" /><br>
                3枚目の写真:<img src="http://photo.test/img/2.png" /><br3
                ',
        ]);
        $postB = $profile->journals()->create([
            'posted_at' => Carbon::parse('2025-04-23'),
            'title' => '投稿B',
        ]);
        $postB->value = '
                これは写真リストのテストです。<br>
                1枚目の写真:<img src="http://photo.test/img/2.png" /><br>
                2枚目の写真:<img src="http://photo.test/img/3.png" /><br>
                3枚目の写真:<img src="http://photo.test/img/4.png" /><br>
                ';
        $postB->save();

        // 評価
        $this->assertEquals(2, $postA->photos->count(), '投稿の記事内容に含まれる写真のコレクションであること');
        foreach ($postA->photos as $index => $photo) {
            $fileNo = $index + 1;
            $this->assertEquals("http://photo.test/img/{$fileNo}.png", $photo->src, '写真ソースは、記事内容の<img />タグのsrc属性の値であること');
            $this->assertEquals('2025-04-22 00:00:00', $photo->posted_at->format('Y-m-d H:i:s'), '写真登録日時は、投稿日（時刻は00:00:00）であること');
        }
        $this->assertEquals(3, $postB->photos->count(), '投稿の記事内容に含まれる写真のコレクションであること');
        foreach ($postB->photos()->orderOldest()->get() as $index => $photo) {
            $fileNo = $index + 2;
            $this->assertEquals("http://photo.test/img/{$fileNo}.png", $photo->src, '写真ソースは、記事内容の<img />タグのsrc属性の値であること');
            if ($fileNo == 2) {
                // 共有写真
                $this->assertEquals('2025-04-22 00:00:00', $photo->posted_at->format('Y-m-d H:i:s'), '一致する写真ソースの写真が既に存在する場合には、登録および更新時に写真は登録せずに写真リストに追加のみ行われること');
            } else {
                $this->assertEquals('2025-04-23 00:00:00', $photo->posted_at->format('Y-m-d H:i:s'), '写真登録日時は、投稿日（時刻は00:00:00）であること');
            }
        }
        $this->assertEquals(4, $profile->photos->count(), '一致する写真ソースの写真が存在しない場合のみ、登録および更新時に写真を登録されること');
    }

    /**
     * 投稿写真リストの自動維持
     * 
     * - 投稿を削除した場合は、投稿写真リストからは削除されることを確認します。
     * - 登録した写真そのものは、削除した投稿とは紐付かない写真として残ることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿写真リストの自動維持
     */
    public function test_photos_share_mode_delete()
    {
        // 準備
        Journal::observe(PostPhotoShareObserver::class);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $postA = $profile->journals()->create([
            'posted_at' => Carbon::parse('2025-04-22'),
            'title' => '投稿A',
            'value' => 'これは写真リストのテストです。<br>
                1枚目の写真:<img src="http://photo.test/img/1.png" /><br>
                2枚目の写真:<img src="http://photo.test/img/2.png" /><br>
                ',
        ]);
        $postB = $profile->journals()->create([
            'posted_at' => Carbon::parse('2025-04-23'),
            'title' => '投稿B',
            'public_level' => PublicLevel::Member,
        ]);
        $postB->doPublic();
        $postB->value = '
                これは写真リストのテストです。<br>
                1枚目の写真:<img src="http://photo.test/img/2.png" /><br>
                2枚目の写真:<img src="http://photo.test/img/3.png" /><br>
                3枚目の写真:<img src="http://photo.test/img/4.png" /><br>
                ';
        $postB->save();
        $postA->delete();

        // 評価
        $this->assertEquals(1, $profile->photos()->ofSrc('http://photo.test/img/2.png')->first()->relatedJournals->count(), '投稿を削除した場合は、写真リストからは削除されること');
        $this->assertEquals(4, $profile->photos->count(), '登録した写真そのものは、削除した投稿とは紐付かない写真として残ること');
    }
}
