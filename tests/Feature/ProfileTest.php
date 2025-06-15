<?php

namespace Tests\Feature;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Category;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Post;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\Recorder;
use Feeldee\Framework\Models\Tag;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

/**
 * プロフィールの用語を担保するための機能テストです。
 * 
 * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#プロフィール
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ユーザID
     * 
     * - プロフィールの所有者を特定するための数値型の外部情報であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#ユーザID
     */
    public function test_user_id()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->create(['user_id' => 100, 'nickname' => 'プロフィール100']);
        $expected = Profile::factory()->create(['user_id' => 200, 'nickname' => 'プロフィール200']);
        Profile::factory()->create(['user_id' => 300, 'nickname' => 'プロフィール300']);

        // 実行
        $profile = Profile::ofUserId($expected->user_id)->first();

        // 評価
        $this->assertEquals($expected->nickname, $profile->nickname, 'プロフィールの所有者を特定するための数値型の外部情報であること');
    }

    /**
     * ユーザID
     * 
     * - プロフィールの所有者を特定するための数値型の外部情報であることを確認します。
     * - Laravel標準の認証システムのAuth::id()の値を設定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#ユーザID
     */
    public function test_user_id_laravel_auth()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(153893094);
        $user_id = Auth::id();

        // 実行
        $profile = Profile::create([
            'user_id' => Auth::id(),
            'nickname' => 'テストプロフィール',
            'title' => 'ユーザIDテスト'
        ]);

        // 評価
        $this->assertEquals($user_id, $profile->user_id, 'Laravel標準の認証システムのAuth::id()の値を設定できること');
    }

    /**
     * ユーザID
     * 
     * - ユーザIDは、必ず指定する必要があることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#ユーザID
     */
    public function test_user_id_required()
    {
        // 評価
        $this->assertThrows(function () {
            Profile::create([
                'nickname' => 'テストプロフィール',
                'title' => 'ユーザID必須テスト'
            ]);
        }, ApplicationException::class, 'ProfileUserIdRequired');
    }

    /**
     * ニックネーム
     * 
     * - プロフィールを一意に識別するための名前であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#ニックネーム
     */
    public function test_nickname()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->create(['user_id' => 100, 'nickname' => 'プロフィール100']);
        $expected = Profile::factory()->create(['user_id' => 200, 'nickname' => 'プロフィール200']);
        Profile::factory()->create(['user_id' => 300, 'nickname' => 'プロフィール300']);

        // 実行
        $profile = Profile::ofNickname($expected->nickname)->first();

        // 評価
        $this->assertEquals($expected->nickname, $profile->nickname, 'プロフィールを一意に識別するための名前であること');
    }

    /**
     * ニックネーム
     * 
     * - ユーザが、いくつもプロフィールを作成することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#ニックネーム
     */
    public function test_nickname_one_user_any_profile()
    {
        // 準備
        $userId = 1;
        Auth::shouldReceive('id')->andReturn($userId);

        // 実行
        Profile::create([
            'user_id' => $userId,
            'nickname' => 'テストプロフィール1',
            'title' => 'ニックネームテスト1'
        ]);
        Profile::create([
            'user_id' => $userId,
            'nickname' => 'テストプロフィール2',
            'title' => 'ニックネームテスト2'
        ]);

        // 評価
        $this->assertEquals(2, Profile::ofUserId($userId)->count(), 'ユーザが、いくつもプロフィールを作成することができること');
    }

    /**
     * ニックネーム
     * 
     * - ニックネームは、フレームワークを導入するシステム内で一意となることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#ニックネーム
     */
    public function test_nickname_unique()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->create(['user_id' => 100, 'nickname' => 'ニックネームテスト']);

        // 評価
        $this->assertThrows(function () {
            Profile::create([
                'user_id' => 200,
                'nickname' => 'ニックネームテスト',
                'title' => 'ニックネームユニークテスト'
            ]);
        }, ApplicationException::class, 'ProfileNicknameDuplicated');
    }

    /**
     * ニックネーム
     * 
     * - ニックネームは、必ず指定する必要があることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#ニックネーム
     */
    public function test_nickname_required()
    {
        // 評価
        $this->assertThrows(function () {
            Profile::create([
                'user_id' => 1,
                'title' => 'ニックネーム必須テスト'
            ]);
        }, ApplicationException::class, 'ProfileNicknameRequired');
    }

    /**
     * プロフィールイメージ
     * 
     * - プロフィールのイメージ画像であることを確認します。
     * - URL形式で保存できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#プロフィールイメージ
     */
    public function test_image_url()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $image = '/path/to/image.jpg';

        // 実行
        $profile =  Profile::create([
            'user_id' => 1,
            'nickname' => 'テストプロフィール',
            'title' => 'プロフィールタイトル',
            'image' => $image,
        ]);

        // 検証
        $this->assertEquals($image, $profile->image, 'プロフィールのイメージ画像であること');
        // URL形式で保存できること
        $this->assertDatabaseHas('profiles', [
            'image' => $image,
        ]);
    }

    /**
     * プロフィールイメージ
     * 
     * - プロフィールのイメージ画像であることを確認します。
     * - Base64形式で保存できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#プロフィールイメージ
     */
    public function test_image_base64()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $image = 'data:image/jpeg;base64,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';

        // 実行
        $profile =  Profile::create([
            'user_id' => 1,
            'nickname' => 'テストプロフィール',
            'title' => 'プロフィールタイトル',
            'image' => $image,
        ]);

        // 検証
        $this->assertEquals($image, $profile->image, 'プロフィールのイメージ画像であること');
        // Base64形式で保存できること
        $this->assertDatabaseHas('profiles', [
            'image' => $image,
        ]);
    }

    /**
     * タイトル
     * 
     * - 日記やブログなどのタイトルであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#タイトル
     */
    public function test_title()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $title = 'プロフィールタイトル';

        // 実行
        $profile = Profile::create([
            'user_id' => 1,
            'nickname' => 'テストプロフィール',
            'title' => $title
        ]);

        // 評価
        $this->assertEquals($title, $profile->title, '日記やブログなどのタイトルであること');
        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'title' => $title,
        ]);
    }

    /**
     * タイトル
     * 
     * - タイトルは、必ず指定する必要があることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#タイトル
     */
    public function test_title_required()
    {
        // 評価
        $this->assertThrows(function () {
            Profile::create([
                'user_id' => 1,
                'nickname' => 'テストプロフィール'
            ]);
        }, ApplicationException::class, 'ProfileTitleRequired');
    }

    /***
     * サブタイトル
     * 
     * - 日記やブログなどのサブタイトルであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#サブタイトル
     */
    public function test_subtitle()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $subtitle = 'プロフィールサブタイトル';

        // 実行
        $profile = Profile::create([
            'user_id' => 1,
            'nickname' => 'テストプロフィール',
            'title' => 'プロフィールタイトル',
            'subtitle' => $subtitle
        ]);

        // 評価
        $this->assertEquals($subtitle, $profile->subtitle, '日記やブログなどのサブタイトルであること');
        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'subtitle' => $subtitle,
        ]);
    }

    /**
     * プロフィール説明
     * 
     * - プロフィールの紹介文やコンテンツの説明を記載できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#プロフィール説明
     */
    public function test_description()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $description = 'プロフィール説明';

        // 実行
        $profile = Profile::create([
            'user_id' => 1,
            'nickname' => 'テストプロフィール',
            'title' => 'プロフィールタイトル',
            'description' => $description
        ]);

        // 評価
        $this->assertEquals($description, $profile->description, 'プロフィールの紹介文やコンテンツの説明を記載できること');
        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'description' => $description,
        ]);
    }

    /**
     * カテゴリリスト
     * 
     * - プロフィールに紐付けられたカテゴリのコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#カテゴリリスト
     */
    public function test_categories()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Category::factory(2))->create();

        // 評価
        $this->assertEquals(2, $profile->categories->count());
        foreach ($profile->categories as $category) {
            $this->assertEquals($category->profile->id, $profile->id, 'プロフィールに紐付けられたカテゴリのコレクションであること');
        }
    }

    /**
     * タグリリスト
     * 
     * - プロフィールに紐付けられたタグのコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#タグリスト
     */
    public function test_tags()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Tag::factory(3))->create();

        // 評価
        $this->assertEquals(3, $profile->tags->count());
        foreach ($profile->tags as $tag) {
            $this->assertEquals($tag->profile->id, $profile->id, 'プロフィールに紐付けられたタグのコレクションであること');
        }
    }

    /**
     * レコーダリスト
     * 
     * - プロフィールに紐付けられたレコーダのコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#レコーダリスト
     */
    public function test_recorders()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Recorder::factory(2))->create();

        // 評価
        $this->assertEquals(2, $profile->recorders->count());
        foreach ($profile->recorders as $recorder) {
            $this->assertEquals($recorder->profile->id, $profile->id, 'プロフィールに紐付けられたレコーダのコレクションであること');
        }
    }

    /**
     * 投稿リスト
     * 
     * - プロフィールに紐付けられた投稿のコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#投稿リスト
     */
    public function test_posts()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(2))->create();

        // 評価
        $this->assertEquals(2, $profile->posts->count());
        foreach ($profile->posts as $post) {
            $this->assertEquals($post->profile->id, $profile->id, 'プロフィールに紐付けられた投稿のコレクションであること');
        }
    }

    /**
     * 写真リスト
     * 
     * - プロフィールに紐付けられた投稿のコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#写真リスト
     */
    public function test_photos()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Photo::factory(3))->create();

        // 評価
        $this->assertEquals(3, $profile->photos->count());
        foreach ($profile->photos as $photo) {
            $this->assertEquals($photo->profile->id, $profile->id, 'プロフィールに紐付けられた写真のコレクションであること');
        }
    }

    /**
     * 場所リスト
     * 
     * - プロフィールに紐付けられた場所のコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#場所リスト
     */
    public function test_locations()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Location::factory(5))->create();

        // 評価
        $this->assertEquals(5, $profile->locations->count());
        foreach ($profile->locations as $location) {
            $this->assertEquals($location->profile->id, $profile->id, 'プロフィールに紐付けられた場所のコレクションであること');
        }
    }

    /**
     * アイテムリスト
     * 
     * - プロフィールに紐付けられたアイテムのコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#アイテムリスト
     */
    public function test_items()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory(2))->create();

        // 評価
        $this->assertEquals(2, $profile->items->count());
        foreach ($profile->items as $item) {
            $this->assertEquals($item->profile->id, $profile->id, 'プロフィールに紐付けられたアイテムのコレクションであること');
        }
    }

    /**
     * コンフィグリスト
     * 
     * - プロフィールのカスタムコンフィグリストであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#コンフィグリスト
     */
    public function test_configs()
    {
        // 準備
        config(['profile.config.value_objects' => [
            'custom_config_1' => \Tests\ValueObjects\Configs\CustomConfig::class,
            'custom_config_2' => \Tests\ValueObjects\Configs\CustomConfig::class,
        ]]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $profile->configs()->create([
            'type' => 'custom_config_1',
            'value' => new \Tests\ValueObjects\Configs\CustomConfig('xxxx', 'yyyy'),
        ]);
        $profile->configs()->create([
            'type' => 'custom_config_2',
            'value' => new \Tests\ValueObjects\Configs\CustomConfig('zzzz', 'wwww'),
        ]);

        // 評価
        $this->assertEquals(2, $profile->configs->count());
        foreach ($profile->configs as $config) {
            $this->assertEquals($config->profile->id, $profile->id, 'プロフィールのカスタムコンフィグリストであること');
        }
    }
}
