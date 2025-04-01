<?php

namespace Tests\Feature;

use Feeldee\Framework\Contracts\HssProfile;
use Feeldee\Framework\Models\Post;
use Feeldee\Framework\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 投稿
     * 
     * - ログインユーザのみが作成できること
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿
     */
    public function test_create()
    {
        // 準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $this->assertThrows(function () {
            Post::create([
                'title' => 'テスト投稿',
                'post_date' => now(),
            ]);
        }, \Feeldee\Framework\Exceptions\LoginRequiredException::class);
    }

    /**
     * コンテンツ種別
     * 
     * - 投稿のコンテンツ種別（type）は、"post"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#コンテンツ種別
     */
    public function test_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($profile);
        Auth::shouldReceive('user')->andReturn($user);

        // 実行
        $post = Post::create([
            'title' => 'テスト投稿',
            'post_date' => now(),
        ]);

        // 検証
        $this->assertEquals('post', $post->type(), '投稿のコンテンツ種別（type）は、"post"であること');
    }

    /**
     * コンテンツ所有者
     * 
     * - ログインユーザのプロフィールのIDがコンテンツ所有者プロフィールIDに設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#コンテンツ所有者
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($profile);
        Auth::shouldReceive('user')->andReturn($user);

        // 実行
        $post = Post::create([
            'title' => 'テスト投稿',
            'post_date' => now(),
        ]);

        // 検証
        $this->assertEquals($profile->id, $post->profile_id, 'ログインユーザのプロフィールのIDがコンテンツ所有者プロフィールIDに設定されること');
        $this->assertDatabaseHas('posts', [
            'profile_id' => $profile->id,
        ]);
    }

    /**
     * 投稿日
     * 
     * - 投稿した日付であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日
     */
    public function test_post_date()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($profile);
        Auth::shouldReceive('user')->andReturn($user);
        $post_date = '2025-04-01';

        // 実行
        $post = Post::create([
            'title' => 'テスト投稿',
            'post_date' => $post_date,
        ]);

        // 検証
        $this->assertEquals($post_date, $post->post_date->format('Y-m-d'), '投稿した日付であること');
        $this->assertDatabaseHas('posts', [
            'post_date' => $post_date . ' 00:00:00',
        ]);
    }

    /**
     * 投稿日
     * 
     * - 投稿時に必ず指定する必要があることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#投稿日
     */
    public function test_post_date_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($profile);
        Auth::shouldReceive('user')->andReturn($user);

        // 実行
        $this->assertThrows(function () {
            Post::create([
                'title' => 'テスト投稿',
            ]);
        }, \Illuminate\Validation\ValidationException::class);
    }

    /**
     * タイトル
     * 
     * - 投稿のタイトルであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#タイトル
     */
    public function test_title()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($profile);
        Auth::shouldReceive('user')->andReturn($user);
        $title = '投稿のタイトル';

        // 実行
        $post = Post::create([
            'title' => $title,
            'post_date' => now(),
        ]);

        // 検証
        $this->assertEquals($title, $post->title, '投稿のタイトルであること');
    }

    /**
     * タイトル
     * 
     * - 投稿時に必ず指定する必要があることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#タイトル
     */
    public function test_title_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($profile);
        Auth::shouldReceive('user')->andReturn($user);

        // 実行
        $this->assertThrows(function () {
            Post::create([
                'post_date' => now(),
            ]);
        }, \Illuminate\Validation\ValidationException::class);
    }

    /**
     * 内容
     * 
     * - 投稿記事の本文であることを確認します。
     * - HTMLが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#内容
     */
    public function test_value_html()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($profile);
        Auth::shouldReceive('user')->andReturn($user);
        $value = '<p>投稿記事の本文</p>';

        // 実行
        $post = Post::create([
            'title' => 'テスト投稿',
            'post_date' => now(),
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($value, $post->value, '投稿記事の本文であること');
        // HTMLが使用できること
        $this->assertDatabaseHas('posts', [
            'value' => $value,
        ]);
    }

    /**
     * 内容
     * 
     * - 投稿記事の本文であることを確認します。
     * - テキストが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#内容
     */
    public function test_value_text()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($profile);
        Auth::shouldReceive('user')->andReturn($user);
        $value = '投稿記事の本文';

        // 実行
        $post = Post::create([
            'title' => 'テスト投稿',
            'post_date' => now(),
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($value, $post->value, '投稿記事の本文であること');
        // テキストが使用できること
        $this->assertDatabaseHas('posts', [
            'value' => $value,
        ]);
    }

    /**
     * テキスト
     * 
     * - 投稿記事の内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - 記事の投稿時に、自動補完されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#テキスト
     */
    public function test_text_create()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($profile);
        Auth::shouldReceive('user')->andReturn($user);
        $value = '<p>投稿記事の本文</p>';
        $expected = '投稿記事の本文';

        // 実行
        $post = Post::create([
            'title' => 'テスト投稿',
            'post_date' => now(),
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($expected, $post->text, '投稿記事の内容から、HTMLタグのみを排除したテキスト表現であること');
        // 記事の投稿時に、自動補完されること
        $this->assertDatabaseHas('posts', [
            'text' => $expected,
        ]);
    }

    /**
     * テキスト
     * 
     * - 投稿記事の内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - 記事の更新時に、自動補完されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#テキスト
     */
    public function test_text_update()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory()->count(1))->create();
        $post = $profile->posts->first();
        $value = '<p>投稿記事の本文</p>';
        $expected = '投稿記事の本文';

        // 実行
        $post->update([
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($expected, $post->text, '投稿記事の内容から、HTMLタグのみを排除したテキスト表現であること');
        // 記事の更新時に、自動補完されること
        $this->assertDatabaseHas('posts', [
            'text' => $expected,
        ]);
    }

    /**
     * サムネイル
     * 
     * - 投稿記事のサムネイル画像であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/投稿#サムネイル
     */
    public function test_thumbnail()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($profile);
        Auth::shouldReceive('user')->andReturn($user);
        $thumbnail = '/path/to/thumbnail.jpg';

        // 実行
        $post = Post::create([
            'title' => 'テスト投稿',
            'post_date' => now(),
            'thumbnail' => $thumbnail,
        ]);

        // 検証
        $this->assertEquals($thumbnail, $post->thumbnail, '投稿記事のサムネイル画像であること');
        // サムネイル画像が保存されていること
        $this->assertDatabaseHas('posts', [
            'thumbnail' => $thumbnail,
        ]);
    }
}
