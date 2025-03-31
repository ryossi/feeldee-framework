<?php

namespace Tests\Feature;

use Feeldee\Framework\Contracts\HssProfile;
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
        // 返信準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $reply = Reply::create([
            'nickname' => 'テストユーザ'
        ], $comment);

        // 評価
        Assert::assertEquals($comment, $reply->comment, '返信したコメントであること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時
     */
    public function test_返信日時_任意の日時を指定()
    {
        // 返信準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();
        $replied_at = '2025-03-30 10:34:10';

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $reply = Reply::create([
            'replied_at' => $replied_at,
            'nickname' => 'テストユーザ',
        ], $comment);

        // 評価
        Assert::assertEquals($replied_at, $reply->replied_at, '指定した日時であること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時
     */
    public function test_返信日時_指定されなかった場合()
    {
        // 返信準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $reply = Reply::create([
            'nickname' => 'テストユーザ',
        ], $comment);

        // 評価
        Assert::assertNotEmpty($reply->replied_at, '指定した日時であること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信本文
     */
    public function test_返信本文_テキスト()
    {
        // 返信準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();
        $body = 'テスト返信本文';

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $reply = Reply::create([
            'body' => $body,
            'nickname' => 'テストユーザ'
        ], $comment);

        // 評価
        Assert::assertEquals($body, $reply->body, 'テキストが使用できること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信本文
     */
    public function test_返信本文_HTML()
    {
        // 返信対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();
        $body = '<b>テスト返信本文</b>';

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $reply = Reply::create([
            'body' => $body,
            'nickname' => 'テストユーザ'
        ], $comment);

        // 評価
        Assert::assertEquals($body, $reply->body, 'HTMLが使用できること');
    }

    /**
     * 返信者
     * 
     * - 返信者がログインユーザの場合は、返信者のプロフィールのIDが返信者プロフィールIDに設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信者
     */
    public function test_replyer_logged_in_user()
    {
        // 返信対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        Auth::shouldReceive('id')->andReturn(2);
        $replyer = Profile::factory()->create();
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($replyer);
        Auth::shouldReceive('user')->andReturn($user);

        // 実行
        $reply = Reply::create([], $comment);

        // 評価
        Assert::assertEquals($replyer, $reply->replyer, '返信者のプロフィールのIDが返信者プロフィールIDに設定されること');
        $this->assertDatabaseHas('replies', [
            'replyer_profile_id' => $replyer->id,
        ]);
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信者
     */
    public function test_返信者_匿名ユーザ()
    {
        // 返信対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $reply = Reply::create([
            'nickname' => 'テストユーザ'
        ], $comment);

        // 評価
        Assert::assertNull($reply->replyer, '返信者プロフィールIDは設定されないこと');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信者ニックネーム
     */
    public function test_返信者ニックネーム_ログインユーザかつニックネーム指定なし()
    {
        // 返信対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        Auth::shouldReceive('id')->andReturn(2);
        $replyer = Profile::factory()->create(
            [
                'nickname' => 'テストユーザ',
            ]
        );
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($replyer);
        Auth::shouldReceive('user')->andReturn($user);

        // 実行
        $reply = Reply::create([], $comment);

        // 評価
        Assert::assertEquals($replyer->nickname, $reply->nickname, 'ログインユーザのニックネームであること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信者ニックネーム
     */
    public function test_返信者ニックネーム_ログインユーザかつニックネーム指定あり()
    {
        // 返信対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        Auth::shouldReceive('id')->andReturn(2);
        $replyer = Profile::factory()->create(
            [
                'nickname' => 'オリジナルニックネーム',
            ]
        );
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($replyer);
        Auth::shouldReceive('user')->andReturn($user);
        $nickname = '指定したニックネーム';

        // 実行
        $reply = Reply::create([
            'nickname' => $nickname
        ], $comment);

        // 評価
        Assert::assertEquals($nickname, $reply->nickname, '指定したニックネームであること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信者ニックネーム
     */
    public function test_返信者ニックネーム_匿名ユーザかつニックネーム指定なし()
    {
        // 返信対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $this->assertThrows(function () use ($comment) {
            Reply::create([], $comment);
        }, \Illuminate\Validation\ValidationException::class);
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信者ニックネーム
     */
    public function test_返信者ニックネーム_匿名ユーザかつニックネーム指定あり()
    {
        // 返信対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);
        $nickname = '指定したニックネーム';

        // 実行
        $reply = Reply::create([
            'nickname' => $nickname
        ], $comment);

        // 評価
        Assert::assertEquals($nickname, $reply->nickname, '指定したニックネームであること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開フラグ
     */
    public function test_返信公開フラグ_デフォルト()
    {
        // 返信対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $reply = Reply::create([
            'nickname' => 'テストユーザ',
            'is_public' => true,
        ], $comment);
        $reply->refresh();

        // 評価
        Assert::assertFalse($reply->isPublic, '公開フラグがデフォルトであること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開フラグ
     */
    public function test_返信公開フラグ_公開()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)
            ->has(Comment::factory(1, ['is_public' => true])
                ->has(Reply::factory(1, ['is_public' => false]))))->create();
        $reply = $profile->posts->first()->comments->first()->replies->first();

        // 実行
        $reply->doPublic();

        // 評価
        Assert::assertTrue($reply->isPublic, '公開であること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開フラグ
     */
    public function test_返信公開フラグ_非公開()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)
            ->has(Comment::factory(1, ['is_public' => true])
                ->has(Reply::factory(1, ['is_public' => true]))))->create();
        $reply = $profile->posts->first()->comments->first()->replies->first();

        // 実行
        $reply->doPrivate();

        // 評価
        Assert::assertFalse($reply->isPublic, '非公開であること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開フラグ
     */
    public function test_返信公開フラグ_取得時の返信公開フラグ_公開・公開()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)
            ->has(Comment::factory(1, ['is_public' => true])
                ->has(Reply::factory(1, ['is_public' => true]))))->create();
        $reply = $profile->posts->first()->comments->first()->replies->first();

        // 評価
        Assert::assertTrue($reply->isPublic, '公開であること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開フラグ
     */
    public function test_返信公開フラグ_取得時の返信公開フラグ_公開・非公開()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)
            ->has(Comment::factory(1, ['is_public' => true])
                ->has(Reply::factory(1, ['is_public' => false]))))->create();
        $reply = $profile->posts->first()->comments->first()->replies->first();

        // 評価
        Assert::assertFalse($reply->isPublic, '非公開であること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開フラグ
     */
    public function test_返信公開フラグ_取得時の返信公開フラグ_非公開・公開()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)
            ->has(Comment::factory(1, ['is_public' => false])
                ->has(Reply::factory(1, ['is_public' => true]))))->create();
        $reply = $profile->posts->first()->comments->first()->replies->first();

        // 評価
        Assert::assertFalse($reply->isPublic, '非公開であること');
    }

    /**
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開フラグ
     */
    public function test_返信公開フラグ_取得時の返信公開フラグ_非公開・非公開()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory(1)
            ->has(Comment::factory(1, ['is_public' => false])
                ->has(Reply::factory(1, ['is_public' => false]))))->create();
        $reply = $profile->posts->first()->comments->first()->replies->first();

        // 評価
        Assert::assertFalse($reply->isPublic, '非公開であること');
    }
}
