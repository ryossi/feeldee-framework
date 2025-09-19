<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Journal;
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
     * 閲覧可能なコンテンツの絞り込み
     * 
     * - 閲覧可能なコンテンツのみに絞り込む場合は、viewableローカルスコープが利用できることを確認します。
     * - 匿名ユーザは、公開レベル「全員」のみ閲覧可否であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#閲覧可能なコンテンツの絞り込み
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
     * 閲覧可能なコンテンツの絞り込み
     * 
     * - プロフィールが関連付けされているユーザEloquentモデルが指定された場合は、デフォルトプロフィールに基づき閲覧可否が判断されることを確認します。
     * - ログインユーザは、公開レベル「全員」「会員」が閲覧可否であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#閲覧可能なコンテンツの絞り込み
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
     * 閲覧可能なコンテンツの絞り込み
     * 
     * - 閲覧可否の判断にニックネームでも指定が可能でることを確認します。
     * - 閲覧者が友達リストに含まれる場合は、「全員」「会員」に加え「友達」のコンテンツも閲覧可能となることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#閲覧可能なコンテンツの絞り込み
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
     * 閲覧可能なコンテンツの絞り込み
     * 
     * - プロフィールを指定して閲覧可否が判断されることを確認します。
     * - 閲覧者が自分自身の場合は「全員」「会員」のコンテンツに加えて「友達」「自分」のコンテンツも閲覧可能となることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#閲覧可能なコンテンツの絞り込み
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
}
