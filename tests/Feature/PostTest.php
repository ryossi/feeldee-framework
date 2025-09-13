<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Journal;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

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
    public function test_filter_by_nickname()
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
    public function test_filter_by_posted_at()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
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
            'posted_at' => Carbon::parse('2025-04-21 10:00:00'),
        ]);

        // 実行
        $journal = Journal::at('2025-04-23 10:00:00')->first();

        // 評価
        $this->assertEquals("テスト投稿2", $journal->title);
    }
}
