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
     * 投稿タイプ
     * 
     * - アイテムの投稿タイプは、"item"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#投稿タイプ
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
        $this->assertEquals('item', $item->type(), 'アイテムの投稿タイプは、"item"であること');
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
     * 新規作成
     * 
     * - アイテムリストのデフォルトの並び順は、1.表示順昇順、2.投稿日時降順（最新順）であることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#新規作成
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
        $items = Profile::nickname('Feeldee')->first()->items;

        // 評価
        $this->assertEquals(4, $items->count());
        $this->assertEquals($itemA->id, $items[0]->id);
        $this->assertEquals($itemB->id, $items[1]->id);
        $this->assertEquals($itemD->id, $items[2]->id);
        $this->assertEquals($itemC->id, $items[3]->id);
    }
}
