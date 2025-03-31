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
}
