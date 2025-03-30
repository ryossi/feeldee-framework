<?php

namespace Tests\Feature;

use Feeldee\Framework\Models\Comment;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\Post;
use Feeldee\Framework\Models\Reply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\Assert;
use Tests\TestCase;

/**
 * 返信の用語を担保するための機能テストです。
 * 
 * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信
 */
class ReplyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信対象
     */
    public function test_返信対象()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 実行
        $reply = Reply::create([], $comment);

        // 評価
        Assert::assertEquals($comment, $reply->comment, '返信したコメントであること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時
     */
    public function test_返信日時_任意の日時を指定()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();
        $replied_at = '2025-03-30 10:34:10';

        // 実行
        $reply = Reply::create([
            'replied_at' => $replied_at,
        ], $comment);

        // 評価
        Assert::assertEquals($replied_at, $reply->replied_at, '指定した日時であること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時
     */
    public function test_返信日時_指定されなかった場合()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 実行
        $reply = Reply::create([], $comment);

        // 評価
        Assert::assertNotEmpty($reply->replied_at, '指定した日時であること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信本文
     */
    public function test_返信本文_テキスト()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();
        $body = 'テスト返信本文';

        // 実行
        $reply = Reply::create([
            'body' => $body,
        ], $comment);

        // 評価
        Assert::assertEquals($body, $reply->body, 'テキストが使用できること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信本文
     */
    public function test_返信本文_HTML()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();
        $body = '<b>テスト返信本文</b>';

        // 実行
        $reply = Reply::create([
            'body' => $body,
        ], $comment);

        // 評価
        Assert::assertEquals($body, $reply->body, 'HTMLが使用できること');
    }
}
