<?php

namespace Tests\Feature;

use Feeldee\Framework\Models\Comment;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\Post;
use Feeldee\Framework\Models\Reply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\Assert;
use Tests\Models\User;
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
     * 返信対象
     * 
     * - 返信対象のは、返信したコメントであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信対象
     */
    public function test_comment()
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
     * 返信日時
     * 
     * - 返信日時は、任意の日時を指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時
     */
    public function test_replied_at_specify()
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
     * 返信日時
     * 
     * - 返信日時は、指定しなかった場合は、現在日時が設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時
     */
    public function test_replied_at_default()
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
     * 返信本文
     * 
     * - テキストが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信本文
     */
    public function test_body_text()
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
     * 返信本文
     * 
     * - HTMLが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信本文
     */
    public function test_body_html()
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
        $user = User::create([
            'name' => '返信者',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        $replyer = Profile::factory()->create([
            'user_id' => $user->id,
            'nickname' => '返信者プロフィール',
        ]);
        Auth::shouldReceive('user')->andReturn($user);

        // 実行
        $reply = Reply::create([], $comment);

        // 評価
        Assert::assertEquals($replyer->id, $reply->replyer->id, '返信者のプロフィールのIDが返信者プロフィールIDに設定されること');
        $this->assertDatabaseHas('replies', [
            'replyer_profile_id' => $replyer->id,
        ]);
    }

    /**
     * 返信者
     * 
     * - 返信者が匿名ユーザの場合は、返信者のプロフィールのIDは設定されないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信者
     */
    public function test_replyer_anonymous()
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
        $this->assertDatabaseHas('replies', [
            'replyer_profile_id' => null,
        ]);
    }

    /**
     * 返信者ニックネーム
     * 
     * ログインユーザ、かつ返信者ニックネームが指定されなかった場合は、返信者のプロフィールのニックネームであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信者ニックネーム
     */
    public function test_nickname_logged_in_user_default()
    {
        // 返信対象準備
        Auth::shouldReceive('id')->andReturn(99);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        $user = User::create([
            'name' => '返信者',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        $replyer = Profile::factory()->create([
            'user_id' => $user->id,
            'nickname' => '返信者プロフィール',
        ]);
        Auth::shouldReceive('user')->andReturn($user);

        // 実行
        $reply = Reply::create([], $comment);

        // 評価
        Assert::assertEquals($replyer->nickname, $reply->nickname, 'ログインユーザのニックネームであること');
        $this->assertDatabaseHas('replies', [
            'replyer_nickname' => null,
        ]);
    }

    /**
     * 返信者ニックネーム
     * 
     * ログインユーザ、かつ返信者ニックネームが指定された場合は、指定したニックネームであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信者ニックネーム
     */
    public function test_nickname_logged_in_user_specify()
    {
        // 返信対象準備
        Auth::shouldReceive('id')->andReturn(99);
        $profile = Profile::factory()->has(Post::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        $user = User::create([
            'name' => '返信者',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        Profile::factory()->create([
            'user_id' => $user->id,
            'nickname' => 'オリジナルニックネーム',
        ]);
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
     * 返信者ニックネーム
     * 
     * - 匿名ユーザは、ニックネームが必須であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信者ニックネーム
     */
    public function test_nickname_anonymous_required()
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
     * 返信者ニックネーム
     * 
     * - 匿名ユーザ、かつニックネーム指定ありの場合は、指定したニックネームであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信者ニックネーム
     */
    public function test_nickname_anonymous_specify()
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
     * 返信公開フラグ
     * 
     * - デフォルトは、非公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開フラグ
     */
    public function test_is_public_default()
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
        ], $comment);

        // 評価
        Assert::assertFalse($reply->isPublic, '公開フラグがデフォルトであること');
    }

    /**
     * 返信公開フラグ
     * 
     * - 公開できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開フラグ
     */
    public function test_is_public_doPublic()
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
        Assert::assertTrue($reply->isPublic, '公開できること');
    }

    /**
     * 返信公開フラグ
     * 
     * - 非公開にできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開フラグ
     */
    public function test_is_public_doPrivate()
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
        Assert::assertFalse($reply->isPublic, '非公開にできること');
    }

    /**
     * 返信公開フラグ
     * 
     * - 取得時の返信公開フラグは、常に返信対象のコメント公開フラグとのAND条件となることを確認します。
     * - 返信対象のコメント公開フラグが公開、返信公開フラグが公開の場合は、取得時の返信公開フラグは公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開フラグ
     */
    public function test_is_publilc_true_and_true()
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
     * 返信公開フラグ
     * 
     * - 取得時の返信公開フラグは、常に返信対象のコメント公開フラグとのAND条件となることを確認します。
     * - 返信対象のコメント公開フラグが公開、返信公開フラグが非公開の場合は、取得時の返信公開フラグは非公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開フラグ
     */
    public function test_is_publilc_true_and_false()
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
     * 返信公開フラグ
     * 
     * - 取得時の返信公開フラグは、常に返信対象のコメント公開フラグとのAND条件となることを確認します。
     * - 返信対象のコメント公開フラグが非公開、返信公開フラグが公開の場合は、取得時の返信公開フラグは非公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開フラグ
     */
    public function test_is_publilc_false_and_true()
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
     * 返信公開フラグ
     * 
     * - 取得時の返信公開フラグは、常に返信対象のコメント公開フラグとのAND条件となることを確認します。
     * - 返信対象のコメント公開フラグが非公開、返信公開フラグが非公開の場合は、取得時の返信公開フラグは非公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開フラグ
     */
    public function test_is_publilc_false_and_false()
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
