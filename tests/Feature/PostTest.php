<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Journal;
use Feeldee\Framework\Models\Like;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\PublicLevel;
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
            ['title' => '今日の釣り日記'],
            ['title' => '昨日の釣り日記'],
            ['title' => '日記を書く'],
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
    // public function test_set_category_on_update()
    // {
    //     // 準備
    //     Auth::shouldReceive('id')->andReturn(1);
    //     $profile = Profile::factory(
    //         ['nickname' => 'Feeldee']
    //     )->has(
    //         Category::factory(1, ['name' => 'News'])->has(Journal::factory()->count(3)->sequence([
    //             ['title' => 'Journal category by object'],
    //             ['title' => 'Journal category by id'],
    //             ['title' => 'Journal category by name'],
    //         ]))
    //     )->create();
    //     $pickup = $profile->categories()->create([
    //         'type' => Journal::class,
    //         'name' => 'Pickup'
    //     ]);

    //     // 実行
    //     Journal::by('Feeldee')->at('2025-09-16')->first()->update(['category' => $pickup]);
    //     $journal->save();

    //     // 評価
    //     $this->assertNull($journal->category);
    // }
}
