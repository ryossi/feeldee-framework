<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Feeldee\Framework\Models\Comment;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\Post;
use Feeldee\Framework\Models\Useable;
use Illuminate\Foundation\Auth\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use PHPUnit\Framework\Assert;

/**
 * コメントの用語を担保するための機能テストです。
 * 
 * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント
 */
class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時
     */
    public function test_コメント日時_任意の日時を指定()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory()->count(1))->create();
        $post = $profile->posts->first();
        Auth::shouldReceive('user')->andReturn(null);
        $commented_at = '2025-03-27 09:30:20';

        // 実行
        $comment = Comment::create([
            'commented_at' => $commented_at,
            'body' => 'これはテストコメントです。',
            'nickname' => 'テストユーザ'
        ], $post);

        // 評価
        Assert::assertEquals($commented_at, $comment->commented_at->format('Y-m-d H:i:s'), '指定した日時であること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時
     */
    public function test_コメント日時_指定されなかった場合()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory()->count(1))->create();
        $post = $profile->posts->first();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = Comment::create([
            'body' => 'これはテストコメントです。',
            'nickname' => 'テストユーザ'
        ], $post);

        // 評価
        Assert::assertNotEmpty($comment->commented_at, 'システム日時が設定されること');
    }
}
