<?php

namespace Tests\Feature;

use Feeldee\Framework\Contracts\HssProfile;
use Feeldee\Framework\Models\Comment;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\Post;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
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
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント所有者
     */
    public function test_コメント所有者()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->twice()->andReturn(1);
        $profile = Profile::factory()->has(Post::factory()->count(1))->create();
        $post = $profile->posts->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = Comment::create([
            'body' => 'これはテストコメントです。',
            'nickname' => 'テストユーザ'
        ], $post);

        // 評価
        Assert::assertEquals($comment->profile, $comment->commentable->profile, 'コメント対象に紐付くプロフィールであること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時
     */
    public function test_コメント日時_任意の日時を指定()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->twice()->andReturn(1);
        $profile = Profile::factory()->has(Post::factory()->count(1))->create();
        $post = $profile->posts->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
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
        // コメント対象準備
        Auth::shouldReceive('id')->twice()->andReturn(1);
        $profile = Profile::factory()->has(Post::factory()->count(1))->create();
        $post = $profile->posts->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = Comment::create([
            'body' => 'これはテストコメントです。',
            'nickname' => 'テストユーザ'
        ], $post);

        // 評価
        Assert::assertNotEmpty($comment->commented_at, 'システム日時が設定されること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント本文
     */
    public function test_コメント本文_テキスト()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->twice()->andReturn(1);
        $profile = Profile::factory()->has(Post::factory()->count(1))->create();
        $post = $profile->posts->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);
        $body = 'これはテストコメントです。';

        // 実行
        $comment = Comment::create([
            'body' => $body,
            'nickname' => 'テストユーザ'
        ], $post);

        // 評価
        Assert::assertEquals($body, $comment->body, 'テキストが使用できること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント本文
     */
    public function test_コメント本文_HTML()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->twice()->andReturn(1);
        $profile = Profile::factory()->has(Post::factory()->count(1))->create();
        $post = $profile->posts->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);
        $body = '<h1>>これはテストコメントです。</h1>';

        // 実行
        $comment = Comment::create([
            'body' => $body,
            'nickname' => 'テストユーザ'
        ], $post);

        // 評価
        Assert::assertEquals($body, $comment->body, 'HTMLが使用できること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント対象
     */
    public function test_コメント対象_投稿()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->twice()->andReturn(1);
        $profile = Profile::factory()->has(Post::factory()->count(1))->create();
        $post = $profile->posts->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = Comment::create([
            'body' => 'これはテストコメントです。',
            'nickname' => 'テストユーザ'
        ], $post);

        // 評価
        Assert::assertEquals($post->id, $comment->commentable->id, 'コメント対象コンテンツIDには、コメント対象のコンテンツのIDが設定されること');
        Assert::assertInstanceOf(Post::class, $comment->commentable, 'コメント対象コンテンツ種別とコメント対象コンテンツIDを組み合わせてコメント対象を特定できること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント対象
     */
    public function test_コメント対象_写真()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->twice()->andReturn(1);
        $profile = Profile::factory()->has(Photo::factory()->count(1))->create();
        $photo = $profile->photos->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = Comment::create([
            'body' => 'これはテストコメントです。',
            'nickname' => 'テストユーザ'
        ], $photo);

        // 評価
        Assert::assertEquals($photo->id, $comment->commentable->id, 'コメント対象コンテンツIDには、コメント対象のコンテンツのIDが設定されること');
        Assert::assertInstanceOf(Photo::class, $comment->commentable, 'コメント対象コンテンツ種別とコメント対象コンテンツIDを組み合わせてコメント対象を特定できること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント対象
     */
    public function test_コメント対象_場所()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->twice()->andReturn(1);
        $profile = Profile::factory()->has(Location::factory()->count(1))->create();
        $location = $profile->locations->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = Comment::create([
            'body' => 'これはテストコメントです。',
            'nickname' => 'テストユーザ'
        ], $location);

        // 評価
        Assert::assertEquals($location->id, $comment->commentable->id, 'コメント対象コンテンツIDには、コメント対象のコンテンツのIDが設定されること');
        Assert::assertInstanceOf(Location::class, $comment->commentable, 'コメント対象コンテンツ種別とコメント対象コンテンツIDを組み合わせてコメント対象を特定できること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント対象
     */
    public function test_コメント対象_アイテム()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->twice()->andReturn(1);
        $profile = Profile::factory()->has(Item::factory()->count(1))->create();
        $item = $profile->items->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = Comment::create([
            'body' => 'これはテストコメントです。',
            'nickname' => 'テストユーザ'
        ], $item);

        // 評価
        Assert::assertEquals($item->id, $comment->commentable->id, 'コメント対象コンテンツIDには、コメント対象のコンテンツのIDが設定されること');
        Assert::assertInstanceOf(Item::class, $comment->commentable, 'コメント対象コンテンツ種別とコメント対象コンテンツIDを組み合わせてコメント対象を特定できること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント対象
     */
    public function test_コメント者_ログインユーザ()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory()->count(1))->create();
        $item = $profile->items->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturn(2);
        $commenter = Profile::factory()->create();
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($commenter);
        Auth::shouldReceive('user')->andReturn($user);

        // 実行
        $comment = Comment::create([
            'body' => 'これはテストコメントです。',
            'nickname' => 'テストユーザ'
        ], $item);

        // 評価
        Assert::assertEquals($commenter->id, $comment->commenter->id, 'コメント者のプロフィールのIDがコメント者プロフィールIDに設定されること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント対象
     */
    public function test_コメント者_匿名ユーザ()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory()->count(1))->create();
        $item = $profile->items->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = Comment::create([
            'body' => 'これはテストコメントです。',
            'nickname' => 'テストユーザ'
        ], $item);

        // 評価
        Assert::assertNull($comment->commenter, 'コメント者プロフィールIDは設定されないこと');
    }
}
