<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Category;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Journal;
use Feeldee\Framework\Models\Like;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\PublicLevel;
use Feeldee\Framework\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Tests\Models\User;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /**
     * コレクションソート
     *
     * - コンテンツコレクションを最新のものから並び替えできることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#コレクションソート
     */
    public function test_collection_sort_latest()
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
        $journals = Profile::of('Feeldee')->first()->journals()->orderLatest()->get();

        // 評価
        $this->assertEquals(3, $journals->count());
        $this->assertEquals($postB->id, $journals[0]->id);
        $this->assertEquals($postA->id, $journals[1]->id);
        $this->assertEquals($postC->id, $journals[2]->id);
    }

    /**
     * コレクションソート
     *
     * - コンテンツコレクションを古いものから並び替えできることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投コレクションソート
     */
    public function test_collection_sort_oldest()
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
        $photos = Photo::by('Feeldee')->orderOldest()->get();

        // 評価
        $this->assertEquals(3, $photos->count());
        $this->assertEquals($postC->id, $photos[0]->id);
        $this->assertEquals($postA->id, $photos[1]->id);
        $this->assertEquals($postB->id, $photos[2]->id);
    }

    /**
     * コレクションソート
     *
     * - コンテンツコレクションを最新(latest)文字列を直接指定してソートすることもできることを確認します。
     * - コンテンツコレクションを古い(oldest)文字列を直接指定してソートすることもできることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#コレクションソート
     */
    public function test_collection_sort_string_latest_and_oldest()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        $locationA = Location::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-22 10:00:00'),
        ]);
        $locationB = Location::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-23 10:00:00'),
        ]);
        $locationC = Location::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-21 10:00:00'),
        ]);

        // 実行
        $locationsLatest = Location::by('Feeldee')->orderDirection('latest')->get();
        $locationsOldest = Location::by('Feeldee')->orderDirection('oldest')->get();

        // 評価
        $this->assertEquals(3, $locationsLatest->count());
        $this->assertEquals($locationB->id, $locationsLatest[0]->id);
        $this->assertEquals($locationA->id, $locationsLatest[1]->id);
        $this->assertEquals($locationC->id, $locationsLatest[2]->id);

        $this->assertEquals(3, $locationsOldest->count());
        $this->assertEquals($locationC->id, $locationsOldest[0]->id);
        $this->assertEquals($locationA->id, $locationsOldest[1]->id);
        $this->assertEquals($locationB->id, $locationsOldest[2]->id);
    }

    /**
     * コレクションソート
     *
     * - コンテンツコレクションを最新(desc)文字列を直接指定してソートすることもできることを確認します。
     * - コンテンツコレクションを古い(asc)文字列を直接指定してソートすることもできることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#コレクションソート
     */
    public function test_collection_sort_string_desc_and_asc()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        $itemA = Item::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-22 10:00:00'),
        ]);
        $itemB = Item::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-23 10:00:00'),
        ]);
        $itemC = Item::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-21 10:00:00'),
        ]);

        // 実行
        $itemsLatest = Profile::of('Feeldee')->first()->items()->orderDirection('desc')->get();
        $itemsOldest = Profile::of('Feeldee')->first()->items()->orderDirection('asc')->get();

        // 評価
        $this->assertEquals(3, $itemsLatest->count());
        $this->assertEquals($itemB->id, $itemsLatest[0]->id);
        $this->assertEquals($itemA->id, $itemsLatest[1]->id);
        $this->assertEquals($itemC->id, $itemsLatest[2]->id);

        $this->assertEquals(3, $itemsOldest->count());
        $this->assertEquals($itemC->id, $itemsOldest[0]->id);
        $this->assertEquals($itemA->id, $itemsOldest[1]->id);
        $this->assertEquals($itemB->id, $itemsOldest[2]->id);
    }

    /**
     * 投稿者による絞り込み
     * 
     * - コンテンツを投稿者のニックネームで絞り込むことができることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿者による絞り込み
     */
    public function test_filter_by()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(
            ['nickname' => 'Feeldee']
        )->has(Journal::factory()->count(3))->create();
        Profile::factory(
            ['nickname' => 'TestUser']
        )->has(Journal::factory()->count(2))->create();

        // 実行
        $journals = Journal::by('Feeldee')->get();

        // 評価
        $this->assertEquals(3, $journals->count());
    }

    /**
     * 投稿日時による絞り込み
     * 
     * - 投稿日時でコンテンツを絞り込むことができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日時による絞り込み
     */
    public function test_filter_at()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Journal::factory()->create([
            'profile_id' => $profile->id,
            'title' => 'テスト投稿1',
            'posted_at' => Carbon::parse('2025-04-22 10:00:00'),
        ]);
        Journal::factory()->create([
            'profile_id' => $profile->id,
            'title' => 'テスト投稿2',
            'posted_at' => Carbon::parse('2025-04-23 10:00:00'),
        ]);
        Journal::factory()->create([
            'profile_id' => $profile->id,
            'title' => 'テスト投稿3',
            'posted_at' => Carbon::parse('2025-09-12 09:30:00'),
        ]);

        // 実行
        $journal = Journal::by('Feeldee')->at('2025-09-12 09:30:00')->first();

        // 評価
        $this->assertEquals("テスト投稿3", $journal->title);
    }

    /**
     * 投稿日時による絞り込み
     * 
     * - 時刻の一部を省略した場合には、指定した時刻での前方一致検索となることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日時による絞り込み
     */
    public function test_filter_at_partial_time()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Journal::factory()->create([
            'profile_id' => $profile->id,
            'title' => 'テスト投稿1',
            'posted_at' => Carbon::parse('2025-09-12 09:32:00'),
        ]);
        Journal::factory()->create([
            'profile_id' => $profile->id,
            'title' => 'テスト投稿2',
            'posted_at' => Carbon::parse('2025-09-12 09:31:00'),
        ]);
        Journal::factory()->create([
            'profile_id' => $profile->id,
            'title' => 'テスト投稿3',
            'posted_at' => Carbon::parse('2025-09-12 09:30:10'),
        ]);

        // 実行
        $journal = Journal::by('Feeldee')->at('2025-09-12 09:30')->first();

        // 評価
        $this->assertEquals("テスト投稿3", $journal->title);
    }

    /**
     * 投稿日時による絞り込み
     * 
     * - 時刻そのものを省略した場合には、指定した日付での前方一致検索となることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日時による絞り込み
     */
    public function test_filter_at_date_only()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Journal::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-12 09:30:02'),
        ]);
        Journal::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-12 09:30:01'),
        ]);
        Journal::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-12 09:30:00'),
        ]);

        // 実行
        $journals = Journal::by('Feeldee')->at('2025-09-12')->get();

        // 評価
        $this->assertEquals(3, $journals->count());
    }

    /**
     * 投稿日時による絞り込み
     * 
     * - 投稿日時の範囲を指定して取得できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日時による絞り込み
     */
    public function test_filter_between()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-22 10:00:00'),
        ]);
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-23 10:00:00'),
        ]);
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-12 09:30:00'),
        ]);

        // 実行
        $photos = Photo::between('2025-09-01 09:00:00', '2025-09-30 18:00:00')->get();

        // 評価
        $this->assertEquals(2, $photos->count());
    }

    /**
     * 投稿日時による絞り込み
     * 
     * - 範囲指定で時刻の全部を省略した場合には、範囲の開始時刻が00:00:00、終了時刻が23:59:59となるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日時による絞り込み
     */
    public function test_filter_between_time_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-01 00:00:00'),
        ]);
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-30 23:59:59'),
        ]);
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-12 09:30:00'),
        ]);

        // 実行
        $photos = Photo::between('2025-09-01', '2025-09-30')->get();

        // 評価
        $this->assertEquals(3, $photos->count());
    }

    /**
     * 投稿日時による絞り込み
     * 
     * - 範囲指定で時刻の一部を省略した場合には、範囲の開始時刻が00:00:00、終了時刻が23:59:59となるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日時による絞り込み
     */
    public function test_filter_between_time_partial_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-01 09:00:01'),
        ]);
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-30 18:00:59'),
        ]);
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-30 18:30:00'),
        ]);

        // 実行
        $photos = Photo::between('2025-09-01 09:00', '2025-09-30 18:00')->get();

        // 評価
        $this->assertEquals(2, $photos->count());
    }

    /**
     * 投稿日時による絞り込み
     * 
     * - 投稿日時の未満で範囲指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日時による絞り込み
     */
    public function test_filter_before()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Journal::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-08-22 10:00:00'),
        ]);
        Journal::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-08-31 23:59:59'),
        ]);
        Journal::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-01 00:00:00'),
        ]);

        // 実行
        $journals = Journal::before('2025-09-01 00:00:00')->get();

        // 評価
        $this->assertEquals(2, $journals->count());
    }

    /**
     * 投稿日時による絞り込み
     * 
     * - 投稿日時の未満で範囲指定することもできることを確認します。
     * - 時刻の一部または全部を省略した場合には、省略部分が常に00:00:00と同じなるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日時による絞り込み
     */
    public function test_filter_before_partial_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Journal::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-08-22 10:00:00'),
        ]);
        Journal::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-01 09:29:59'),
        ]);
        Journal::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-01 09:30:00'),
        ]);

        // 実行
        $journals = Journal::before('2025-09-01 09:30')->get();

        // 評価
        $this->assertEquals(2, $journals->count());
    }

    /**
     * 投稿日時による絞り込み
     * 
     * - 投稿日時のより先で範囲指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日時による絞り込み
     */
    public function test_filter_after()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-08-31 23:59:59'),
        ]);
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-01 00:00:00'),
        ]);
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-01 00:00:01'),
        ]);

        // 実行
        $photos = Photo::after('2025-09-01 00:00:00')->get();

        // 評価
        $this->assertEquals(1, $photos->count());
    }

    /**
     * 投稿日時による絞り込み
     * 
     * - 投稿日時のより先で範囲指定することもできることを確認します。
     * - 時刻の一部または全部を省略した場合には、省略部分が常に00:00:00と同じなるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日時による絞り込み
     */
    public function test_filter_after_partial_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-08-31 23:59:59'),
        ]);
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-01 00:00:00'),
        ]);
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-01 00:00:01'),
        ]);

        // 実行
        $photos = Photo::after('2025-09-01')->get();

        // 評価
        $this->assertEquals(1, $photos->count());
    }

    /**
     * 投稿日時による絞り込み
     * 
     * - 投稿日時の以前で範囲指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日時による絞り込み
     */
    public function test_filter_beforeEquals()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Location::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-08-22 10:00:00'),
        ]);
        Location::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-08-31 23:59:59'),
        ]);
        Location::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-01 09:30:00'),
        ]);

        // 実行
        $locations = Location::beforeEquals('2025-09-01 09:30:00')->get();

        // 評価
        $this->assertEquals(3, $locations->count());
    }

    /**
     * 投稿日時による絞り込み
     * 
     * - 投稿日時の以前で範囲指定することもできることを確認します。
     * - 時刻の一部または全部を省略した場合には、省略部分が常に00:00:00と同じなるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日時による絞り込み
     */
    public function test_filter_beforeEquals_partial_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Location::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-08-22 10:00:00'),
        ]);
        Location::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-08-31 23:59:59'),
        ]);
        Location::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-01 09:30:00'),
        ]);

        // 実行
        $locations = Location::beforeEquals('2025-09-01 09:30')->get();

        // 評価
        $this->assertEquals(3, $locations->count());
    }

    /**
     * 投稿日時による絞り込み
     * 
     * - 投稿日時の以降で範囲指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日時による絞り込み
     */
    public function test_filter_afterEquals()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Item::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-01 09:59:59'),
        ]);
        Item::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-01 10:00:00'),
        ]);
        Item::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-02 09:30:00'),
        ]);

        // 実行
        $items = Item::afterEquals('2025-09-01 10:00:00')->get();

        // 評価
        $this->assertEquals(2, $items->count());
    }

    /**
     * 投稿日時による絞り込み
     * 
     * - 投稿日時の以降で範囲指定することもできることを確認します。
     * - 時刻の一部または全部を省略した場合には、省略部分が常に00:00:00と同じなるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日時による絞り込み
     */
    public function test_filter_afterEquals_partial_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Item::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-01 09:59:59'),
        ]);
        Item::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-01 10:00:00'),
        ]);
        Item::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-09-02 09:30:00'),
        ]);

        // 実行
        $items = Item::afterEquals('2025-09-01')->get();

        // 評価
        $this->assertEquals(3, $items->count());
    }

    /**
     * 公開・非公開による絞り込み
     * 
     * - 公開の投稿のみを取得できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#公開・非公開による絞り込み
     */
    public function test_filter_public()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'is_public' => true,
        ]);
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'is_public' => true,
        ]);
        Photo::factory()->create([
            'profile_id' => $profile->id,
            'is_public' => false,
        ]);

        // 実行
        $photos = Profile::of('Feeldee')->first()->photos()->public()->get();

        // 評価
        $this->assertEquals(2, $photos->count());
    }

    /**
     * 公開・非公開による絞り込み
     * 
     * - 非公開の投稿のみを取得できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#公開・非公開による絞り込み
     */
    public function test_filter_private()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Location::factory()->create([
            'profile_id' => $profile->id,
            'is_public' => true,
        ]);
        Location::factory()->create([
            'profile_id' => $profile->id,
            'is_public' => true,
        ]);
        Location::factory()->create([
            'profile_id' => $profile->id,
            'is_public' => false,
        ]);

        // 実行
        $locations = Location::private()->get();

        // 評価
        $this->assertEquals(1, $locations->count());
    }

    /**
     * 閲覧可能な投稿の絞り込み
     * 
     * - 閲覧可能なコンテンツのみに絞り込む場合は、viewableローカルスコープが利用できることを確認します。
     * - 匿名ユーザは、公開レベル「全員」のみ閲覧可否であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#閲覧可能な投稿の絞り込み
     */
    public function test_filter_viewable()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Photo::factory(5)->sequence(
                ['is_public' => false, 'public_level' => PublicLevel::Public],
                ['is_public' => true, 'public_level' => PublicLevel::Private],
                ['is_public' => true, 'public_level' => PublicLevel::Friend],
                ['is_public' => true, 'public_level' => PublicLevel::Member],
                ['is_public' => true, 'public_level' => PublicLevel::Public],
            )
        )->create();

        // 実行
        $photos = Photo::viewable()->get();

        // 評価
        $this->assertEquals(1, $photos->count());
        foreach ($photos as $photo) {
            $this->assertTrue($photo->is_public, "Photo ID {$photo->id} is not public");
        }
        $this->assertContains(PublicLevel::Public, $photos->pluck('public_level'));
    }

    /**
     * 閲覧可能な投稿の絞り込み
     * 
     * - プロフィールが関連付けされているユーザEloquentモデルが指定された場合は、デフォルトプロフィールに基づき閲覧可否が判断されることを確認します。
     * - ログインユーザは、公開レベル「全員」「会員」が閲覧可否であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#閲覧可能な投稿の絞り込み
     */
    public function test_filter_viewable_with_user_model()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $user = User::create([
            'name' => 'テストユーザ',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        $user->profiles()->create([
            'nickname' => 'Viewer',
            'title' => '閲覧者'
        ]);
        Auth::shouldReceive('user')->andReturn($user);

        Profile::factory(
            ['nickname' => 'Feeldee']
        )->has(Journal::factory(5)->sequence(
            ['is_public' => false, 'public_level' => PublicLevel::Public],
            ['is_public' => true, 'public_level' => PublicLevel::Private],
            ['is_public' => true, 'public_level' => PublicLevel::Friend],
            ['is_public' => true, 'public_level' => PublicLevel::Member],
            ['is_public' => true, 'public_level' => PublicLevel::Public],
        ))->create();

        // 実行
        $journals = Journal::by('Feeldee')->viewable(Auth::user())->get();

        // 評価
        $this->assertEquals(2, $journals->count());
        foreach ($journals as $journal) {
            $this->assertTrue($journal->is_public, "Journal ID {$journal->id} is not public");
        }
        $this->assertContains(PublicLevel::Public, $journals->pluck('public_level'));
        $this->assertContains(PublicLevel::Member, $journals->pluck('public_level'));
    }

    /**
     * 閲覧可能な投稿の絞り込み
     * 
     * - 閲覧可否の判断にニックネームでも指定が可能でることを確認します。
     * - 閲覧者が友達リストに含まれる場合は、「全員」「会員」に加え「友達」のコンテンツも閲覧可能となることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#閲覧可能な投稿の絞り込み
     */
    public function test_filter_viewable_with_nickname()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(
            Item::factory(5)->sequence(
                ['is_public' => false, 'public_level' => PublicLevel::Public],
                ['is_public' => true, 'public_level' => PublicLevel::Private],
                ['is_public' => true, 'public_level' => PublicLevel::Friend],
                ['is_public' => true, 'public_level' => PublicLevel::Member],
                ['is_public' => true, 'public_level' => PublicLevel::Public],
            )
        )->hasAttached(Profile::factory(['nickname' => 'Friend']), ['created_by' => 1, 'updated_by' => 1], 'friends')->create();

        // 実行
        $items = Item::by('Feeldee')->viewable('Friend')->get();

        // 評価
        $this->assertEquals(3, $items->count());
        foreach ($items as $item) {
            $this->assertTrue($item->is_public, "Item ID {$item->id} is not public");
        }
        $this->assertContains(PublicLevel::Public, $items->pluck('public_level'));
        $this->assertContains(PublicLevel::Member, $items->pluck('public_level'));
        $this->assertContains(PublicLevel::Friend, $items->pluck('public_level'));
    }

    /**
     * 閲覧可能な投稿の絞り込み
     * 
     * - プロフィールを指定して閲覧可否が判断されることを確認します。
     * - 閲覧者が自分自身の場合は「全員」「会員」のコンテンツに加えて「友達」「自分」のコンテンツも閲覧可能となることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#閲覧可能な投稿の絞り込み
     */
    public function test_filter_viewable_with_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(
            [
                'nickname' => 'Feeldee'
            ]
        )->has(Location::factory(5)->sequence(
            ['is_public' => false, 'public_level' => PublicLevel::Public],
            ['is_public' => true, 'public_level' => PublicLevel::Private],
            ['is_public' => true, 'public_level' => PublicLevel::Friend],
            ['is_public' => true, 'public_level' => PublicLevel::Member],
            ['is_public' => true, 'public_level' => PublicLevel::Public],
        ))->create();

        // 実行
        $locations = Location::by('Feeldee')->viewable(Profile::of('Feeldee')->first())->get();

        // 評価
        $this->assertEquals(4, $locations->count());
        foreach ($locations as $location) {
            $this->assertTrue($location->is_public, "Location ID {$location->id} is not public");
        }
        $this->assertContains(PublicLevel::Public, $locations->pluck('public_level'));
        $this->assertContains(PublicLevel::Member, $locations->pluck('public_level'));
        $this->assertContains(PublicLevel::Friend, $locations->pluck('public_level'));
        $this->assertContains(PublicLevel::Private, $locations->pluck('public_level'));
    }

    /**
     * 閲覧可能な投稿の絞り込み
     * 
     * - isViewableメソッドでログイン中のユーザが閲覧可能かどうかを判定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#閲覧可能な投稿の絞り込み
     */
    public function test_is_viewable()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $user = User::create([
            'name' => 'テストユーザ',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        $user->profiles()->create([
            'nickname' => 'Viewer',
            'title' => '閲覧者'
        ]);
        Auth::shouldReceive('user')->andReturn($user);
        Profile::factory(['nickname' => 'Feeldee'])->has(
            Journal::factory()->count(5)->sequence(
                ['posted_at' => '2025-09-16', 'is_public' => false, 'public_level' => PublicLevel::Public],
                ['posted_at' => '2025-09-17', 'is_public' => true, 'public_level' => PublicLevel::Private],
                ['posted_at' => '2025-09-18', 'is_public' => true, 'public_level' => PublicLevel::Friend],
                ['posted_at' => '2025-09-19', 'is_public' => true, 'public_level' => PublicLevel::Member],
                ['posted_at' => '2025-09-20', 'is_public' => true, 'public_level' => PublicLevel::Public],
            )
        )->create();

        // 実行
        $noPublic = Journal::by('Feeldee')->at('2025-09-16')->first()->isViewable(Auth::user());
        $private = Journal::by('Feeldee')->at('2025-09-17')->first()->isViewable(Auth::user());
        $friend = Journal::by('Feeldee')->at('2025-09-18')->first()->isViewable(Auth::user());
        $member = Journal::by('Feeldee')->at('2025-09-19')->first()->isViewable(Auth::user());
        $public = Journal::by('Feeldee')->at('2025-09-20')->first()->isViewable(Auth::user());

        // 評価
        $this->assertFalse($noPublic);
        $this->assertFalse($private);
        $this->assertFalse($friend);
        $this->assertTrue($member);
        $this->assertTrue($public);
    }

    /**
     * 閲覧可能な投稿の絞り込み
     * 
     * - isViewableメソッドでログインしていない匿名ユーザも判定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#閲覧可能な投稿の絞り込み
     */
    public function test_is_viewable_with_anonymous_user()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(
            Photo::factory()->count(5)->sequence(
                ['posted_at' => '2025-09-16 09:30', 'is_public' => false, 'public_level' => PublicLevel::Public],
                ['posted_at' => '2025-09-16 10:30', 'is_public' => true, 'public_level' => PublicLevel::Private],
                ['posted_at' => '2025-09-16 11:30', 'is_public' => true, 'public_level' => PublicLevel::Friend],
                ['posted_at' => '2025-09-16 12:30', 'is_public' => true, 'public_level' => PublicLevel::Member],
                ['posted_at' => '2025-09-16 13:30', 'is_public' => true, 'public_level' => PublicLevel::Public],
            )
        )->create();

        // 実行
        $noPublic = Photo::by('Feeldee')->at('2025-09-16 09:30')->first()->isViewable();
        $private = Photo::by('Feeldee')->at('2025-09-16 10:30')->first()->isViewable();
        $friend = Photo::by('Feeldee')->at('2025-09-16 11:30')->first()->isViewable();
        $member = Photo::by('Feeldee')->at('2025-09-16 12:30')->first()->isViewable();
        $public = Photo::by('Feeldee')->at('2025-09-16 13:30')->first()->isViewable();

        // 評価
        $this->assertFalse($noPublic);
        $this->assertFalse($private);
        $this->assertFalse($friend);
        $this->assertFalse($member);
        $this->assertTrue($public);
    }

    /**
     * 閲覧可能な投稿の絞り込み
     * 
     * - isViewableメソッドでニックネームを直接指定しても判定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#閲覧可能な投稿の絞り込み
     */
    public function test_is_viewable_with_nickname()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(
            Location::factory()->count(5)->sequence(
                ['posted_at' => '2025-09-16 09:30', 'is_public' => false, 'public_level' => PublicLevel::Public],
                ['posted_at' => '2025-09-17 09:30', 'is_public' => true, 'public_level' => PublicLevel::Private],
                ['posted_at' => '2025-09-18 09:30', 'is_public' => true, 'public_level' => PublicLevel::Friend],
                ['posted_at' => '2025-09-19 09:30', 'is_public' => true, 'public_level' => PublicLevel::Member],
                ['posted_at' => '2025-09-20 09:30', 'is_public' => true, 'public_level' => PublicLevel::Public],
            )
        )->hasAttached(Profile::factory(['nickname' => 'ユーザ1']), [], 'friends')->create();

        // 実行
        $noPublic = Location::by('Feeldee')->at('2025-09-16 09:30')->first()->isViewable('ユーザ1');
        $private = Location::by('Feeldee')->at('2025-09-17 09:30')->first()->isViewable('ユーザ1');
        $friend = Location::by('Feeldee')->at('2025-09-18 09:30')->first()->isViewable('ユーザ1');
        $member = Location::by('Feeldee')->at('2025-09-19 09:30')->first()->isViewable('ユーザ1');
        $public = Location::by('Feeldee')->at('2025-09-20 09:30')->first()->isViewable('ユーザ1');

        // 評価
        $this->assertFalse($noPublic);
        $this->assertFalse($private);
        $this->assertTrue($friend);
        $this->assertTrue($member);
        $this->assertTrue($public);
    }

    /**
     * 閲覧可能な投稿の絞り込み
     * 
     * - isViewableメソッドで自分自身を指定しても判定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#閲覧可能な投稿の絞り込み
     */
    public function test_is_viewable_with_mine()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Item::factory()->count(5)->sequence(
            ['posted_at' => '2025-09-16 09:30', 'is_public' => false, 'public_level' => PublicLevel::Public],
            ['posted_at' => '2025-09-17 09:30', 'is_public' => true, 'public_level' => PublicLevel::Private],
            ['posted_at' => '2025-09-18 09:30', 'is_public' => true, 'public_level' => PublicLevel::Friend],
            ['posted_at' => '2025-09-19 09:30', 'is_public' => true, 'public_level' => PublicLevel::Member],
            ['posted_at' => '2025-09-20 09:30', 'is_public' => true, 'public_level' => PublicLevel::Public],
        )->for($profile)->create();

        // 実行
        $noPublic = Item::by('Feeldee')->at('2025-09-16 09:30')->first()->isViewable($profile);
        $private = Item::by('Feeldee')->at('2025-09-17 09:30')->first()->isViewable($profile);
        $friend = Item::by('Feeldee')->at('2025-09-18 09:30')->first()->isViewable($profile);
        $member = Item::by('Feeldee')->at('2025-09-19 09:30')->first()->isViewable($profile);
        $public = Item::by('Feeldee')->at('2025-09-20 09:30')->first()->isViewable($profile);

        // 評価
        $this->assertFalse($noPublic);
        $this->assertTrue($private);
        $this->assertTrue($friend);
        $this->assertTrue($member);
        $this->assertTrue($public);
    }

    /**
     * 投稿件数のカウント
     * 
     * - 記事を投稿年単位でカウントできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿件数のカウント
     */
    public function test_count_by_year()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Journal::factory()->count(6)->sequence(
            ['posted_at' => '2024-01-01', 'is_public' => true, 'public_level' => PublicLevel::Public],
            ['posted_at' => '2025-09-16', 'is_public' => false, 'public_level' => PublicLevel::Public],
            ['posted_at' => '2025-09-17', 'is_public' => true, 'public_level' => PublicLevel::Private],
            ['posted_at' => '2025-09-18', 'is_public' => true, 'public_level' => PublicLevel::Friend],
            ['posted_at' => '2025-10-19', 'is_public' => true, 'public_level' => PublicLevel::Member],
            ['posted_at' => '2025-10-20', 'is_public' => true, 'public_level' => PublicLevel::Public],
        )->for($profile)->create();

        // 実行
        $result = Journal::by('Feeldee')->countBy('Y')->get();

        // 評価
        $this->assertCount(2, $result);
        $this->assertEquals('2025', $result[0]->label);
        $this->assertEquals(5, $result[0]->count);
        $this->assertEquals('2024', $result[1]->label);
        $this->assertEquals(1, $result[1]->count);
    }

    /**
     * 投稿件数のカウント
     * 
     * - 記事を投稿年月単位でカウントできることを確認します。
     * - 公開済みの投稿のみをカウントできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿件数のカウント
     */
    public function test_count_by_month()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Location::factory()->count(6)->sequence(
            ['posted_at' => '2024-01-01', 'is_public' => true, 'public_level' => PublicLevel::Public],
            ['posted_at' => '2025-09-16', 'is_public' => false, 'public_level' => PublicLevel::Public],
            ['posted_at' => '2025-09-17', 'is_public' => true, 'public_level' => PublicLevel::Private],
            ['posted_at' => '2025-09-18', 'is_public' => true, 'public_level' => PublicLevel::Friend],
            ['posted_at' => '2025-10-19', 'is_public' => true, 'public_level' => PublicLevel::Member],
            ['posted_at' => '2025-10-20', 'is_public' => true, 'public_level' => PublicLevel::Public],
        )->for($profile)->create();

        // 実行
        $result = Location::by('Feeldee')->public()->countBy('Y-m')->get();

        // 評価
        $this->assertCount(3, $result);
        $this->assertEquals('2025-10', $result[0]->label);
        $this->assertEquals(2, $result[0]->count);
        $this->assertEquals('2025-09', $result[1]->label);
        $this->assertEquals(2, $result[1]->count);
        $this->assertEquals('2024-01', $result[2]->label);
        $this->assertEquals(1, $result[2]->count);
    }

    /**
     * 投稿件数のカウント
     * 
     * - 記事を投稿日単位でカウントできることを確認します。
     * - 友達が閲覧可能な投稿のみをカウントできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿件数のカウント
     */
    public function test_count_by_day()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->hasAttached(Profile::factory(['nickname' => 'Friend']), [], 'friends')->create();
        Photo::factory()->count(6)->sequence(
            ['posted_at' => '2025-01-01 00:00:00', 'is_public' => true, 'public_level' => PublicLevel::Public],
            ['posted_at' => '2025-01-01 23:59:59', 'is_public' => false, 'public_level' => PublicLevel::Public],
            ['posted_at' => '2025-01-02 00:00:00', 'is_public' => true, 'public_level' => PublicLevel::Private],
            ['posted_at' => '2025-01-02 23:59:59', 'is_public' => true, 'public_level' => PublicLevel::Friend],
            ['posted_at' => '2025-01-03 00:00:00', 'is_public' => true, 'public_level' => PublicLevel::Member],
            ['posted_at' => '2025-01-03 23:59:59', 'is_public' => true, 'public_level' => PublicLevel::Public],
        )->for($profile)->create();

        // 実行
        $result = Photo::by('Feeldee')->viewable('Friend')->countBy('Y-m-d')->get();

        // 評価
        $this->assertCount(3, $result);
        $this->assertEquals('2025-01-03', $result[0]->label);
        $this->assertEquals(2, $result[0]->count);
        $this->assertEquals('2025-01-02', $result[1]->label);
        $this->assertEquals(1, $result[1]->count);
        $this->assertEquals('2025-01-01', $result[2]->label);
        $this->assertEquals(1, $result[2]->count);
    }

    /**
     * 投稿件数のカウント
     * 
     * - カウント結果を結果の古い順にソートできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿件数のカウント
     */
    public function test_count_by_order_oldest()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        Item::factory()->count(6)->sequence(
            ['posted_at' => '2024-01-01', 'is_public' => true, 'public_level' => PublicLevel::Public],
            ['posted_at' => '2025-09-16', 'is_public' => false, 'public_level' => PublicLevel::Public],
            ['posted_at' => '2025-09-17', 'is_public' => true, 'public_level' => PublicLevel::Private],
            ['posted_at' => '2025-09-18', 'is_public' => true, 'public_level' => PublicLevel::Friend],
            ['posted_at' => '2025-10-19', 'is_public' => true, 'public_level' => PublicLevel::Member],
            ['posted_at' => '2025-10-20', 'is_public' => true, 'public_level' => PublicLevel::Public],
        )->for($profile)->create();

        // 実行
        $result = Item::by('Feeldee')->public()->countBy('Y')->orderOldest()->get();

        // 評価
        $this->assertCount(2, $result);
        $this->assertEquals('2024', $result[0]->label);
        $this->assertEquals(1, $result[0]->count);
        $this->assertEquals('2025', $result[1]->label);
        $this->assertEquals(4, $result[1]->count);
    }

    /**
     * 投稿タイトルによる絞り込み
     * 
     * - 投稿タイトルを指定して投稿を絞り込むことができることを確認します。
     * - 投稿タイトルを完全一致検索できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿タイトルによる絞り込み
     */
    public function test_filter_title()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(
            ['nickname' => 'Feeldee']
        )->has(Journal::factory(3)->sequence(
            ['title' => 'First Journal'],
            ['title' => 'Second Journal'],
            ['title' => 'Third Journal'],
        ))->create();

        // 実行
        $result = Journal::by('Feeldee')->title('First Journal')->get();

        // 評価
        $this->assertCount(1, $result);
        $this->assertEquals('First Journal', $result[0]->title);
    }

    /**
     * 投稿タイトルによる絞り込み
     * 
     * - 投稿タイトルを指定して投稿を絞り込むことができることを確認します。
     * - 投稿タイトルを前方一致検索できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿タイトルによる絞り込み
     */
    public function test_filter_title_prefix()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(
            ['nickname' => 'Feeldee']
        )->has(Location::factory(3)->sequence(
            ['title' => '山形の風景'],
            ['title' => '山形の名所'],
            ['title' => '仙台の観光地'],
        ))->create();

        // 実行
        $locations = Location::by('Feeldee')->title('山形の', Like::Prefix)->get();

        // 評価
        $this->assertCount(2, $locations);
        $this->assertEquals('山形の風景', $locations[0]->title);
        $this->assertEquals('山形の名所', $locations[1]->title);
    }

    /**
     * 投稿タイトルによる絞り込み
     * 
     * - 投稿タイトルを指定して投稿を絞り込むことができることを確認します。
     * - 投稿タイトルを後方一致検索できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿タイトルによる絞り込み
     */
    public function test_filter_title_suffix()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(
            ['nickname' => 'Feeldee']
        )->has(Journal::factory(3)->sequence(
            ['title' => '今日の釣り日記', 'posted_at' => now()],
            ['title' => '昨日の釣り日記', 'posted_at' => now()->subDay()],
            ['title' => '日記を書く', 'posted_at' => now()->subDays(2)],
        ))->create();

        // 実行
        $mypets = Journal::by('Feeldee')->title('日記', Like::Suffix)->get();

        // 評価
        $this->assertCount(2, $mypets);
        $this->assertEquals('今日の釣り日記', $mypets[0]->title);
        $this->assertEquals('昨日の釣り日記', $mypets[1]->title);
    }

    /**
     * 投稿カテゴリの設定と変更
     * 
     * - 新規作成でカテゴリを直接設定できることを確認します。
     * - 投稿カテゴリは、カテゴリそのものの他に、カテゴリIDでもカテゴリ名でも指定可能であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿カテゴリの設定と変更
     */
    public function test_set_category_on_create()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        $news = Profile::of('Feeldee')->first()->categories()->create([
            'type' => Journal::type(),
            'name' => 'News'
        ]);

        // 実行
        $journal1 = $profile->journals()->create([
            'title' => 'Journal category by object',
            'category' => $news
        ]);
        $journal2 = $profile->journals()->create([
            'title' => 'Journal category by id',
            'category' => $news->id
        ]);
        $journal3 = $profile->journals()->create([
            'title' => 'Journal category by name',
            'category' => 'News'
        ]);

        // 評価
        $this->assertEquals($news->id, $journal1->category?->id, 'カテゴリそのものを指定して新規作成');
        $this->assertEquals($news->id, $journal2->category?->id, 'カテゴリIDを指定して新規作成');
        $this->assertEquals($news->id, $journal3->category?->id, 'カテゴリ名を指定して新規作成');
    }

    /**
     * 投稿カテゴリの設定と変更
     * 
     * - 既存の投稿に対してカテゴリを直接設定できることを確認します。
     * - 投稿カテゴリは、カテゴリそのものの他に、カテゴリIDでもカテゴリ名でも指定可能であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿カテゴリの設定と変更
     */
    public function test_set_category_on_update()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        $news = Category::factory([
            'type' => Journal::type(),
            'name' => 'News'
        ])->for($profile)->create();
        $pickup = Category::factory([
            'type' => Journal::type(),
            'name' => 'Pickup'
        ])->for($profile)->create();
        Journal::factory()->count(3)->sequence(
            ['title' => 'Journal category by object', 'category_id' => $news->id],
            ['title' => 'Journal category by id', 'category_id' => $news->id],
            ['title' => 'Journal category by name', 'category_id' => $news->id],
        )->for($profile)->create();

        // 実行
        $journal1 = Journal::title('Journal category by object')->first();
        $journal1->category = $pickup;
        $journal1->save();

        $journal2 = Journal::title('Journal category by id')->first();
        $journal2->category = $pickup->id;
        $journal2->save();

        $journal3 = Journal::title('Journal category by name')->first();
        $journal3->category = 'Pickup';
        $journal3->save();

        // 評価
        $this->assertEquals($pickup->id, $journal1->category?->id, 'カテゴリそのものを指定して変更');
        $this->assertEquals($pickup->id, $journal2->category?->id, 'カテゴリIDを指定して変更');
        $this->assertEquals($pickup->id, optional($journal3->category)->id, 'カテゴリ名を指定して変更');
    }

    /**
     * 投稿カテゴリの設定と変更
     * 
     * - カテゴリ所有プロフィールが投稿者プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿カテゴリの設定と変更
     */
    public function test_set_category_profile_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $otherProfile = Profile::factory()->create();
        $category = Category::factory([
            'profile_id' => $profile->id,
            'type' => Journal::type(),
        ])->create();

        // 実行
        $this->assertThrows(function () use ($otherProfile, $category) {
            $otherProfile->journals()->create([
                'title' => 'テスト記録',
                'posted_at' => now(),
                'category' => $category,
            ]);
        }, ApplicationException::class, 'PostCategoryProfileMissmatch');
    }

    /**
     * 投稿カテゴリの設定と変更
     * 
     * - 投稿種別と同じカテゴリタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿カテゴリの設定と変更
     */
    public function test_set_category_type_missmatch()
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
        }, ApplicationException::class, 'PostCategoryTypeMissmatch');
    }

    /**
     * 投稿カテゴリの設定と変更
     * 
     * - カテゴリ名を指定した場合は、カテゴリ所有プロフィールと投稿者プロフィールが一致し、かつ投稿種別と同じカテゴリタイプのカテゴリの中からカテゴリ名が一致するカテゴリのIDが設定されることを確認します。
     * - 一致するカテゴリが存在しない場合は無視されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿カテゴリの設定と変更
     */
    public function test_set_category_name()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->create();
        $profile->categories()->create([
            'type' => Journal::type(),
            'name' => 'Pickup'
        ]);
        $pickupForPhoto = $profile->categories()->create([
            'type' => Photo::type(),
            'name' => 'Pickup'
        ]);

        // 実行
        $photo = $profile->photos()->create([
            'title' => 'Pickup Photo',
            'src' => '/photos/myphoto.jpg',
            'category' => 'Pickup'
        ]);

        // 評価
        $this->assertEquals($pickupForPhoto->id, $photo->category->id, 'カテゴリ名を指定した場合は、カテゴリ所有プロフィールと投稿者プロフィールが一致し、かつ投稿種別と同じカテゴリタイプのカテゴリの中からカテゴリ名が一致するカテゴリのIDが設定されること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'category_id' => $pickupForPhoto->id,
        ]);

        // 実行
        $photo->category = 'Others';
        $photo->save();

        // 評価
        $this->assertEquals($pickupForPhoto->id, optional($photo->category)->id, 'カテゴリ名を指定した場合に一致するカテゴリが存在しない場合は無視されること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'category_id' => $pickupForPhoto->id,
        ]);
    }

    /**
     * 投稿カテゴリの設定と変更
     * 
     * - 投稿カテゴリをクリアしたい場合は、nullを設定することでクリアできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿カテゴリの設定と変更
     */
    public function test_set_category_clear()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->create();
        $category = Category::factory([
            'type' => Location::type(),
        ])->for($profile)->create();
        $location = Location::factory([
            'category' => $category
        ])->for($profile)->create();
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'category_id' => $category->id,
        ]);

        // 実行
        $location->category = null;
        $location->save();

        // 評価
        $this->assertNull($location->category, 'nullを設定することでクリアできること');
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'category_id' => null,
        ]);
    }

    /**
     * 投稿カテゴリの設定と変更
     * 
     * - 対応するカテゴリが削除された場合は、自動的にNullが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿カテゴリの設定と変更
     */
    public function test_set_category_delete()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $category = $profile->categories()->create([
            'type' => Photo::type(),
            'name' => 'Pickup'
        ]);
        $photo = $profile->photos()->create([
            'title' => 'Pickup Photo',
            'src' => '/photos/myphoto.jpg',
            'category' => $category
        ]);

        // 実行
        $category->delete();
        $photo->refresh();

        // 評価
        $this->assertNull($photo->category, '対応するカテゴリが削除された場合は、自動的にnullが設定されること');
        $this->assertDatabaseEmpty('categories');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'category_id' => null,
        ]);
    }

    /**
     * 投稿カテゴリによる絞り込み
     * 
     * - 投稿カテゴリにより投稿を絞り込むことができることを確認します。
     * - カテゴリオブジェクトを指定して絞り込みできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿カテゴリによる絞り込み
     */
    public function test_categorizedOf()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->create();
        $category = Category::factory(['name' => 'NewItem', 'type' => Item::type()])->for($profile)->create();
        Item::factory(4)->sequence(
            ['title' => 'Item 1', 'category' => $category],
            ['title' => 'Item 2', 'category' => $category],
            ['title' => 'Item 3', 'category' => $category],
            ['title' => 'Item 4'],
        )->for($profile)->create();

        // 実行
        $items = Item::categorizedOf(Category::by('Feeldee')->name('NewItem')->first())->get();

        // 評価
        $this->assertEquals(3, $items->count());
    }

    /**
     * 投稿カテゴリによる絞り込み
     * 
     * - 投稿カテゴリにより投稿を絞り込むことができることを確認します。
     * - カテゴリIDを指定して絞り込みできることを確認
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿カテゴリによる絞り込み
     */
    public function test_categorizedOf_by_id()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->create();
        $category = Category::factory(['name' => 'NewItem', 'type' => Item::type()])->for($profile)->create();
        Item::factory(4)->sequence(
            ['title' => 'Item 1', 'category' => $category],
            ['title' => 'Item 2', 'category' => $category],
            ['title' => 'Item 3', 'category' => $category],
            ['title' => 'Item 4'],
        )->for($profile)->create();

        // 実行
        $category = Category::by('Feeldee')->name('NewItem')->first();
        $items = Item::categorizedOf($category->id)->get();

        // 評価
        $this->assertEquals(3, $items->count());
    }

    /**
     * 投稿カテゴリによる絞り込み
     * 
     * - 投稿カテゴリにより投稿を絞り込むことができることを確認します。
     * - カテゴリ名を指定して絞り込みできることを確認します。
     * - プロフィールを横断して同じカテゴリ名に属する複数の投稿を取得できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿カテゴリによる絞り込み
     */
    public function test_categorizedOf_by_name()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile1 = Profile::factory(['nickname' => 'Profile1'])->create();
        $category1 = Category::factory(['name' => 'News', 'type' => Journal::type()])->for($profile1)->create();
        Journal::factory(3)->sequence(
            ['title' => 'Journal 1', 'category' => $category1],
            ['title' => 'Journal 2', 'category' => $category1],
            ['title' => 'Journal 3'],
        )->for($profile1)->create();

        $profile2 = Profile::factory(['nickname' => 'Profile2'])->create();
        $category2 = Category::factory(['name' => 'News', 'type' => Journal::type()])->for($profile2)->create();
        Journal::factory(3)->sequence(
            ['title' => 'Journal 4', 'category' => $category2],
            ['title' => 'Journal 5', 'category' => $category2],
            ['title' => 'Journal 6', 'category' => $category2],
        )->for($profile2)->create();

        // 実行
        $journals = Journal::categorizedOf('News')->get();

        // 評価
        $this->assertEquals(5, $journals->count());
    }

    /**
     * 投稿カテゴリによる絞り込み
     * 
     * - 投稿カテゴリが指定されていない投稿のみに絞り込みできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿カテゴリによる絞り込み
     */
    public function test_categorizedOf_without_category()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->create();
        $category = Category::factory(['name' => 'Point', 'type' => Location::type()])->for($profile)->create();
        Location::factory(4)->sequence(
            ['title' => 'Item 1', 'category' => $category],
            ['title' => 'Item 2', 'category' => $category],
            ['title' => 'Item 3', 'category' => $category],
            ['title' => 'Item 4'],
        )->for($profile)->create();

        // 実行
        $locations = Location::categorizedOf()->get();
        $uncategorized = Location::categorizedOf(null)->get();

        // 評価
        $this->assertEquals(1, $locations->count());
        $this->assertEquals('Item 4', $locations->first()->title);
        $this->assertEquals(1, $uncategorized->count());
        $this->assertEquals('Item 4', $uncategorized->first()->title);
    }

    /**
     * 投稿タグリストの設定と変更
     * 
     * - 投稿タグリストの設定が可能であることを確認します。
     * - タグオブジェクトのコレクションで指定可能であることを確認します。
     * - タグIDの配列で指定可能であることを確認します。
     * - タグ名の配列で指定可能であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿タグリストの設定と変更
     */
    public function test_set_tags_on_create()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->create();
        $book = $profile->tags()->create([
            'type' => Journal::type(),
            'name' => 'Book'
        ]);
        $favorite = $profile->tags()->create([
            'type' => Journal::type(),
            'name' => 'Favorite'
        ]);
        $music = $profile->tags()->create([
            'type' => Journal::type(),
            'name' => 'Music'
        ]);

        // 実行
        $journal1 = $profile->journals()->create([
            'title' => 'Journal tags by object',
            'tags' => Tag::by('Feeldee')->of(Journal::class)->get()
        ]);
        $journal2 = $profile->journals()->create([
            'title' => 'Journal tags by id',
            'tags' => [$book->id, $favorite->id]
        ]);
        $journal3 = $profile->journals()->create([
            'title' => 'Journal tagged by name',
            'tags' => ['Book', 'Favorite']
        ]);

        // 評価
        $this->assertEquals(3, $journal1->tags->count(), 'タグオブジェクトのコレクションで指定可能であること');
        $this->assertTrue($journal1->tags->pluck('name')->contains('Book'));
        $this->assertTrue($journal1->tags->pluck('name')->contains('Favorite'));
        $this->assertTrue($journal1->tags->pluck('name')->contains('Music'));
        $this->assertEquals(2, $journal2->tags->count(), 'タグIDの配列で指定可能であること');
        $this->assertTrue($journal2->tags->pluck('name')->contains('Book'));
        $this->assertTrue($journal2->tags->pluck('name')->contains('Favorite'));
        $this->assertEquals(2, $journal3->tags->count(), 'タグ名の配列で指定可能であること');
        $this->assertTrue($journal3->tags->pluck('name')->contains('Book'));
        $this->assertTrue($journal3->tags->pluck('name')->contains('Favorite'));
        $this->assertDatabaseCount('taggables', 7);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $book->id,
            'taggable_id' => $journal1->id,
            'taggable_type' => Journal::type(),
        ]);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $favorite->id,
            'taggable_id' => $journal1->id,
            'taggable_type' => Journal::type(),
        ]);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $music->id,
            'taggable_id' => $journal1->id,
            'taggable_type' => Journal::type(),
        ]);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $book->id,
            'taggable_id' => $journal2->id,
            'taggable_type' => Journal::type(),
        ]);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $favorite->id,
            'taggable_id' => $journal2->id,
            'taggable_type' => Journal::type(),
        ]);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $book->id,
            'taggable_id' => $journal3->id,
            'taggable_type' => Journal::type(),
        ]);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $favorite->id,
            'taggable_id' => $journal3->id,
            'taggable_type' => Journal::type(),
        ]);
    }

    /**
     * 投稿タグリストの設定と変更
     * 
     * - 投稿タグリストの変更が可能であることを確認します。
     * - タグオブジェクトのコレクションで指定可能であることを確認します。
     * - タグIDの配列で指定可能であることを確認します。
     * - タグ名の配列で指定可能であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿タグリストの設定と変更
     */
    public function test_set_tags_on_update()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        $book = Tag::factory([
            'type' => Journal::type(),
            'name' => 'Book'
        ])->for($profile)->create();
        $favorite = Tag::factory([
            'type' => Journal::type(),
            'name' => 'Favorite'
        ])->for($profile)->create();
        $music = Tag::factory([
            'type' => Journal::type(),
            'name' => 'Music'
        ])->for($profile)->create();
        Journal::factory()->count(3)->sequence(
            ['title' => 'Journal tag by object', 'tags' => [$book, $favorite, $music]],
            ['title' => 'Journal tag by id', 'tags' => [$book, $favorite]],
            ['title' => 'Journal tag by name', 'tags' => [$book, $favorite]],
        )->for($profile)->create();

        // 実行
        $journal1 = Journal::title('Journal tag by object')->first();
        $journal1->tags = [$music, $favorite];
        $journal1->save();

        $journal2 = Journal::title('Journal tag by id')->first();
        $journal2->tags = [$music->id, $favorite->id];
        $journal2->save();

        $journal3 = Journal::title('Journal tag by name')->first();
        $journal3->tags = ['Music', 'Favorite'];
        $journal3->save();

        // 評価
        $this->assertEquals(2, optional($journal1->tags)->count(), 'タグオブジェクトのコレクションで指定可能であること');
        $this->assertTrue(optional($journal1->tags)->pluck('name')->contains('Favorite'));
        $this->assertTrue(optional($journal1->tags)->pluck('name')->contains('Music'));
        $this->assertEquals(2, optional($journal2->tags)->count(), 'タグIDの配列で指定可能であること');
        $this->assertTrue(optional($journal2->tags)->pluck('name')->contains('Favorite'));
        $this->assertTrue(optional($journal2->tags)->pluck('name')->contains('Music'));
        $this->assertEquals(2, optional($journal3->tags)->count(), 'タグ名の配列で指定可能であること');
        $this->assertTrue(optional($journal3->tags)->pluck('name')->contains('Favorite'));
        $this->assertTrue(optional($journal3->tags)->pluck('name')->contains('Music'));
        $this->assertDatabaseCount('taggables', 6);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $favorite->id,
            'taggable_id' => $journal1->id,
            'taggable_type' => Journal::type(),
        ]);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $music->id,
            'taggable_id' => $journal1->id,
            'taggable_type' => Journal::type(),
        ]);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $favorite->id,
            'taggable_id' => $journal2->id,
            'taggable_type' => Journal::type(),
        ]);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $music->id,
            'taggable_id' => $journal2->id,
            'taggable_type' => Journal::type(),
        ]);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $favorite->id,
            'taggable_id' => $journal3->id,
            'taggable_type' => Journal::type(),
        ]);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $music->id,
            'taggable_id' => $journal3->id,
            'taggable_type' => Journal::type(),
        ]);
    }

    /**
     * 投稿タグリストの設定と変更
     * 
     * - 投稿タグリストに個別にタグを追加できることを確認します。
     * - 投稿タグリストから特定のタグのみを削除できることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿タグリストの設定と変更
     */
    public function test_set_tags_on_modify()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        $book = Tag::factory([
            'type' => Photo::type(),
            'name' => 'Book'
        ])->for($profile)->create();
        $favorite = Tag::factory([
            'type' => Photo::type(),
            'name' => 'Favorite'
        ])->for($profile)->create();
        $photo = Photo::factory([
            'src' => '/photos/sample.png',
            'tags' => [$book]
        ])->for($profile)->create();

        // 実行
        $photo->tags()->attach($favorite);
        $photo->tags()->detach($book);

        // 評価
        $this->assertEquals(1, optional($photo->tags)->count());
        $this->assertTrue(optional($photo->tags)->pluck('name')->contains('Favorite'));
        $this->assertDatabaseCount('taggables', 1);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $favorite->id,
            'taggable_id' => $photo->id,
            'taggable_type' => Photo::type(),
        ]);
    }

    /**
     * 投稿タグリストの設定と変更
     * 
     * - タグ所有プロフィールが投稿者プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿タグリストの設定と変更
     */
    public function test_set_tags_profile_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $otherProfile = Profile::factory()->create();
        $profile = Profile::factory()->create();
        $favorite = $otherProfile->tags()->create([
            'type' => Photo::type(),
            'name' => 'Favorite'
        ]);
        $music = $profile->tags()->create([
            'type' => Photo::type(),
            'name' => 'Music'
        ]);

        // 実行
        $this->assertThrows(function () use ($profile, $favorite, $music) {
            $profile->photos()->create([
                'title' => 'PostTagProfileMissmatch',
                'src' => '/photos/favorite.jpg',
                'tags' => [$favorite, $music]
            ]);
        }, ApplicationException::class, 'PostTagProfileMissmatch');
    }

    /**
     * 投稿タグリストの設定と変更
     * 
     * - タグタイプが投稿種別と一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿タグリストの設定と変更
     */
    public function test_set_tags_type_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $favorite = $profile->tags()->create([
            'type' => Item::type(),
            'name' => 'Favorite'
        ]);
        $music = $profile->tags()->create([
            'type' => Photo::type(),
            'name' => 'Music'
        ]);

        // 実行
        $this->assertThrows(function () use ($profile, $favorite, $music) {
            $profile->photos()->create([
                'title' => 'PostTagProfileMissmatch',
                'src' => '/photos/favorite.jpg',
                'tags' => [$favorite, $music]
            ]);
        }, ApplicationException::class, 'PostTagTypeMissmatch');
    }

    /**
     * 投稿タグリストの設定と変更
     *
     * - タグ名の配列を指定した場合は、タグ所有プロフィールと投稿者プロフィールが一致し、かつ投稿種別と同じタグタイプのカテゴリの中からタグ名が一致するタグのIDが設定されることを確認します。
     * - 一致するタグが存在しない場合は無視されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿タグリストの設定と変更
     */
    public function test_set_tags_name()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $profile->tags()->create([
            'type' => Journal::type(),
            'name' => 'Favorite'
        ]);
        $favoriteForPhoto = $profile->tags()->create([
            'type' => Photo::type(),
            'name' => 'Favorite'
        ]);

        // 実行
        $photo = $profile->photos()->create([
            'title' => 'Favorite Photo',
            'src' => '/photos/myphoto.jpg',
            'tags' => 'Favorite'
        ]);

        // 評価
        $this->assertEquals(1, $photo->tags->count());
        $this->assertEquals($favoriteForPhoto->id, $photo->tags->first()->id);
        $this->assertDatabaseCount('taggables', 1);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $favoriteForPhoto->id,
            'taggable_id' => $photo->id,
            'taggable_type' => Photo::type(),
        ]);

        // 実行
        $photo->tags = ['Favorite', 'Others'];
        $photo->save();

        // 評価
        $this->assertEquals(1, optional($photo->tags)->count());
        $this->assertEquals($favoriteForPhoto->id, optional($photo->tags)->first()->id);
        $this->assertDatabaseCount('taggables', 1);
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $favoriteForPhoto->id,
            'taggable_id' => $photo->id,
            'taggable_type' => Photo::type(),
        ]);
    }

    /**
     * 投稿タグリストの設定と変更
     * 
     * - 投稿タグリストを全てクリアできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿タグリストの設定と変更
     */
    public function test_set_tags_clear()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        $book = Tag::factory([
            'type' => Photo::type(),
            'name' => 'Book'
        ])->for($profile)->create();
        $favorite = Tag::factory([
            'type' => Photo::type(),
            'name' => 'Favorite'
        ])->for($profile)->create();
        $photo = Photo::factory([
            'src' => '/photos/sample.png',
            'tags' => [$book, $favorite]
        ])->for($profile)->create();

        // 実行
        $photo->tags = null;
        $photo->save();

        // 評価
        $this->assertEquals(0, optional($photo->tags)->count());
        $this->assertDatabaseCount('taggables', 0);
    }

    /**
     * 投稿タグリストの設定と変更
     * 
     * - 対応するタグが削除された場合は、投稿タグリストからも自動的に除外されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿タグリストの設定と変更
     */
    public function test_set_tags_delete()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        $favorite = Tag::factory([
            'type' => Journal::type(),
            'name' => 'Favorite'
        ])->for($profile)->create();
        $journal = Journal::factory([
            'title' => 'Favorite books',
            'tags' => $favorite
        ])->for($profile)->create();

        // 実行
        $favorite->delete();
        $journal->refresh();

        // 評価
        $this->assertEquals(0, $journal->tags->count());
        $this->assertDatabaseCount('taggables', 0);
    }

    /**
     * 投稿レコードリストの設定と変更
     * 
     * - 投稿レコードリストの設定と変更をレコーダキーとレコード値のとの連想配列で指定できることを確認します。
     * - レコードキーには、レコーダIDで指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿レコードリストの設定と変更
     */
    public function test_set_records()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->create();
        $weightRecorder = $profile->recorders()->create([
            'type' => Journal::type(),
            'name' => 'Weight',
            'data_type' => 'int',
            'unit' => 'kg'
        ]);
        $lengthRecorder = $profile->recorders()->create([
            'type' => Journal::type(),
            'name' => 'Length',
            'data_type' => 'int',
            'unit' => 'cm'
        ]);

        // 実行（設定）
        $journal = $profile->journals()->create([
            'title' => 'Journal records by object',
            'records' => [$weightRecorder->id => 65, $lengthRecorder->id => 173]
        ]);

        // 評価
        $this->assertEquals(2, $journal->records->count());
        $this->assertEquals(65, $journal->records->first()->value);
        $this->assertEquals(173, $journal->records->last()->value);
        $this->assertDatabaseCount('records', 2);
        $this->assertDatabaseHas('records', [
            'id' => $weightRecorder->id,
            'value' => 65
        ]);
        $this->assertDatabaseHas('records', [
            'id' => $lengthRecorder->id,
            'value' => 173
        ]);

        // 実行（変更）
        $journal->records = [$weightRecorder->id => 75, $lengthRecorder->id => 185];
        $journal->save();

        // 評価
        $this->assertEquals(2, optional($journal->records)->count());
        $this->assertEquals(75, optional($journal->records)->first()->value);
        $this->assertEquals(185, optional($journal->records)->last()->value);
        $this->assertDatabaseCount('records', 2);
        $this->assertDatabaseHas('records', [
            'id' => $weightRecorder->id,
            'value' => 75
        ]);
        $this->assertDatabaseHas('records', [
            'id' => $lengthRecorder->id,
            'value' => 185
        ]);
    }

    /**
     * 投稿レコードリストの設定と変更
     * 
     * - 投稿レコードリストの設定と変更をレコーダキーとレコード値のとの連想配列で指定できることを確認します。
     * - レコードキーには、レコーダ名で指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿レコードリストの設定と変更
     */
    public function test_set_records_by_name()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->create();
        $weightRecorder = $profile->recorders()->create([
            'type' => Journal::type(),
            'name' => 'Weight',
            'data_type' => 'int',
            'unit' => 'kg'
        ]);
        $lengthRecorder = $profile->recorders()->create([
            'type' => Journal::type(),
            'name' => 'Length',
            'data_type' => 'int',
            'unit' => 'cm'
        ]);

        // 実行（設定）
        $journal = $profile->journals()->create([
            'title' => 'Journal records by name',
            'records' => ['Weight' => 65, 'Length' => 173]
        ]);

        // 評価
        $this->assertEquals(2, $journal->records->count());
        $this->assertEquals(65, $journal->records->first()->value);
        $this->assertEquals(173, $journal->records->last()->value);
        $this->assertDatabaseCount('records', 2);
        $this->assertDatabaseHas('records', [
            'id' => $weightRecorder->id,
            'value' => 65
        ]);
        $this->assertDatabaseHas('records', [
            'id' => $lengthRecorder->id,
            'value' => 173
        ]);

        // 実行（変更）
        $journal->records = ['Weight' => 75, 'Length' => 185];
        $journal->save();

        // 評価
        $this->assertEquals(2, optional($journal->records)->count());
        $this->assertEquals(75, optional($journal->records)->first()->value);
        $this->assertEquals(185, optional($journal->records)->last()->value);
        $this->assertDatabaseCount('records', 2);
        $this->assertDatabaseHas('records', [
            'id' => $weightRecorder->id,
            'value' => 75
        ]);
        $this->assertDatabaseHas('records', [
            'id' => $lengthRecorder->id,
            'value' => 185
        ]);
    }

    /**
     * 投稿レコードリストの設定と変更
     * 
     * - レコーダIDを直接指定する場合は、レコーダ所有プロフィールが投稿者プロフィールと一致する必要があることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿レコードリストの設定と変更
     */
    public function test_set_records_profile_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $otherProfile = Profile::factory()->create();
        $profile = Profile::factory()->create();
        $weightRecorder = $profile->recorders()->create([
            'type' => Journal::type(),
            'name' => 'Weight',
            'data_type' => 'int',
            'unit' => 'kg'
        ]);
        $lengthRecorder = $otherProfile->recorders()->create([
            'type' => Journal::type(),
            'name' => 'Length',
            'data_type' => 'int',
            'unit' => 'cm'
        ]);

        // 実行
        $this->assertThrows(function () use ($profile, $weightRecorder, $lengthRecorder) {
            $profile->journals()->create([
                'title' => 'Journal records',
                'records' => [$weightRecorder->id => 65, $lengthRecorder->id => 173]
            ]);
        }, ApplicationException::class, 'RecorderProfileMissmatch');
    }

    /**
     * 投稿レコードリストの設定と変更
     * 
     * - レコーダタイプが投稿種別と一致する必要があることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿レコードリストの設定と変更
     */
    public function test_set_records_type_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $weightRecorder = $profile->recorders()->create([
            'type' => Journal::type(),
            'name' => 'Weight',
            'data_type' => 'int',
            'unit' => 'kg'
        ]);
        $deviceRecorder = $profile->recorders()->create([
            'type' => Photo::type(),
            'name' => 'Device',
            'data_type' => 'string',
        ]);

        // 実行
        $this->assertThrows(function () use ($profile, $weightRecorder, $deviceRecorder) {
            $profile->journals()->create([
                'title' => 'Journal records',
                'records' => [$weightRecorder->id => 65, $deviceRecorder->id => 'iPhone 16 Pro']
            ]);
        }, ApplicationException::class, 'RecorderTypeMissmatch');
    }

    /**
     * 投稿レコードリストの設定と変更
     * 
     * - 存在しないレコーダIDを指定した場合は、指定したレコーダが見つからないエラーとなることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿レコードリストの設定と変更
     */
    public function test_set_records_recorder_not_found()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $weightRecorder = $profile->recorders()->create([
            'type' => Journal::type(),
            'name' => 'Weight',
            'data_type' => 'int',
            'unit' => 'kg'
        ]);

        // 実行
        $this->assertThrows(function () use ($profile, $weightRecorder) {
            $profile->journals()->create([
                'title' => 'Journal records',
                'records' => [99999998 => 100, $weightRecorder->id => 65]
            ]);
        }, ApplicationException::class, 'RecorderNotFound');
    }

    /**
     * 投稿レコードリストの設定と変更
     * 
     * - レコーダ名の配列を指定した場合は、レコーダ所有プロフィールと投稿者プロフィールが一致し、かつ投稿種別と同じタグタイプのレコーダの中からレコーダ名が一致するレコーダが使用されることを確認します。
     * - 一致するレコーダが存在しない場合は無視されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿レコードリストの設定と変更
     */
    public function test_set_records_name()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->create();
        $weightRecorderForJournal = Profile::of('Feeldee')->first()->recorders()->create([
            'type' => Journal::type(),
            'name' => 'Weight',
            'data_type' => 'int',
            'unit' => 'kg'
        ]);
        Profile::of('Feeldee')->first()->recorders()->create([
            'type' => Item::type(),
            'name' => 'Weight',
            'data_type' => 'int',
            'unit' => 'g'
        ]);

        // レコーダ名の配列を指定した場合は、レコーダ所有プロフィールと投稿者プロフィールが一致し、かつ投稿種別と同じタグタイプの
        // レコーダの中からレコーダ名が一致するレコーダが使用されることを確認
        // 実行
        $journal = $profile->journals()->create([
            'title' => 'Journal records',
            'records' => ['Weight' => 65]
        ]);

        // 評価
        $this->assertEquals($weightRecorderForJournal->id, $journal->records()->first()->recorder->id);
        $this->assertEquals(65, $journal->records()->first()->value);
        $this->assertDatabaseCount('records', 1);
        $this->assertDatabaseHas('records', [
            'id' => $weightRecorderForJournal->id,
            'value' => 65
        ]);

        // 一致するレコーダが存在しない場合は無視されることを確認
        // 実行
        $journal->records = ['Other' => 65];
        $journal->save();

        // 評価
        $this->assertEquals($weightRecorderForJournal->id, $journal->records()->first()->recorder->id);
        $this->assertEquals(65, $journal->records()->first()->value);
        $this->assertDatabaseCount('records', 1);
        $this->assertDatabaseHas('records', [
            'id' => $weightRecorderForJournal->id,
            'value' => 65
        ]);
    }

    /**
     * 投稿レコードリストの設定と変更
     * 
     * - 対象となるレコードキーのレコード値にnullを指定した場合は、そのレコードが削除されることを確認します。
     * - レコードキーそのものを連想配列から除外した場合も、そのレコードが削除されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿レコードリストの設定と変更
     */
    public function test_set_records_delete()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->create();
        $weightRecorder = $profile->recorders()->create([
            'type' => Journal::type(),
            'name' => 'Weight',
            'data_type' => 'int',
            'unit' => 'kg'
        ]);
        $lengthRecorder = $profile->recorders()->create([
            'type' => Journal::class,
            'name' => 'Length',
            'data_type' => 'int',
            'unit' => 'cm'
        ]);
        $journal2 = $profile->journals()->create([
            'title' => 'Journal records by id',
            'tags' => [$weightRecorder->id => 65, $lengthRecorder->id => 173]
        ]);
        $journal3 = $profile->journals()->create([
            'title' => 'Journal records by name',
            'tags' => ['Weight' => 65, 'Length' => 173]
        ]);

        // 実行
        $journal2->records = [$weightRecorder->id => 75, $lengthRecorder->id => null];
        $journal2->save();
        $journal3->records = [$weightRecorder->id => 75];
        $journal3->save();

        // 評価
        $this->assertEquals(1, optional($journal2->records)->count());
        $this->assertEquals(75, optional($journal2->records)->first()->value);
        $this->assertEquals(1, optional($journal3->records)->count());
        $this->assertEquals(75, optional($journal3->records)->first()->value);
        $this->assertDatabaseCount('records', 2);
    }

    /**
     * 投稿レコードリストの設定と変更
     * 
     * - 投稿に紐付くレコード一括でクリアしたい場合は、投稿レコードリストにnullを設定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿レコードリストの設定と変更
     */
    public function test_set_records_clear()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->create();
        $weightRecorder = $profile->recorders()->create([
            'type' => Journal::type(),
            'name' => 'Weight',
            'data_type' => 'int',
            'unit' => 'kg'
        ]);
        $lengthRecorder = $profile->recorders()->create([
            'type' => Journal::type(),
            'name' => 'Length',
            'data_type' => 'int',
            'unit' => 'cm'
        ]);
        $journal = $profile->journals()->create([
            'title' => 'Journal records by id',
            'records' => [$weightRecorder->id => 65, $lengthRecorder->id => 173]
        ]);

        // 実行
        $journal->records = null;
        $journal->save();

        // 評価
        $this->assertEquals(0, optional($journal->records)->count());
        $this->assertDatabaseCount('records', 0);
    }

    /**
     * 投稿レコードリストの設定と変更
     * 
     * - レコーダそのものが削除された場合は、投稿レコードリストから削除されたレコーダのレコードも自動的に削除されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿レコードリストの設定と変更
     */
    public function test_set_records_delete_recorder()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->create();
        $weight = $profile->recorders()->create([
            'type' => Journal::type(),
            'name' => 'Weight',
            'data_type' => 'int',
            'unit' => 'kg'
        ]);
        $journal = $profile->journals()->create([
            'title' => 'Journal records',
            'records' => [$weight->id => 65]
        ]);

        // 実行
        $weight->delete();
        $journal->refresh();

        // 評価
        $this->assertEquals(0, $journal->records->count());
        $this->assertDatabaseCount('records', 0);
    }
}
