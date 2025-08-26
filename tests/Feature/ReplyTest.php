<?php

namespace Tests\Feature;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Comment;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\Journal;
use Feeldee\Framework\Models\Reply;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\Assert;
use Tests\Models\User;
use Tests\TestCase;

/**
 * 返信の機能テストです。
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
        $profile = Profile::factory()->has(Journal::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $reply = $comment->replies()->create([
            'replyer_nickname' => 'テストユーザ'
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
        $profile = Profile::factory()->has(Journal::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();
        $replied_at = '2025-03-30 10:34:10';

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $reply = $comment->replies()->create([
            'replied_at' => $replied_at,
            'replyer_nickname' => 'テストユーザ',
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
        $profile = Profile::factory()->has(Journal::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $reply = $comment->replies()->create([
            'replyer_nickname' => 'テストユーザ',
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
        $profile = Profile::factory()->has(Journal::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();
        $body = 'テスト返信本文';

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $reply = $comment->replies()->create([
            'body' => $body,
            'replyer_nickname' => 'テストユーザ'
        ]);

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
        $profile = Profile::factory()->has(Journal::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();
        $body = '<b>テスト返信本文</b>';

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $reply = $comment->replies()->create([
            'body' => $body,
            'replyer_nickname' => 'テストユーザ'
        ]);

        // 評価
        Assert::assertEquals($body, $reply->body, 'HTMLが使用できること');
    }

    /**
     * 返信者プロフィール
     * 
     * - 返信者がログインユーザの場合は、返信者のプロフィールのIDが返信者プロフィールIDに設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信者プロフィール
     */
    public function test_replyer_logged_in_user()
    {
        // 返信対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Journal::factory(1)->has(Comment::factory(1)))->create();
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
        $reply = $comment->replies()->create([
            'replyer' => $replyer,
        ]);

        // 評価
        Assert::assertEquals($replyer->id, $reply->replyer->id, '返信者のプロフィールのIDが返信者プロフィールIDに設定されること');
        $this->assertDatabaseHas('replies', [
            'replyer_profile_id' => $replyer->id,
        ]);
    }

    /**
     * 返信者プロフィール
     * 
     * - 返信者が匿名ユーザの場合は、返信者のプロフィールのIDは設定されないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信者プロフィール
     */
    public function test_replyer_anonymous()
    {
        // 返信対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Journal::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $reply = $comment->replies()->create([
            'replyer_nickname' => 'テストユーザ'
        ]);

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
        $profile = Profile::factory()->has(Journal::factory(1)->has(Comment::factory(1)))->create();
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
        $reply = $comment->replies()->create([
            'replyer' => $replyer,
        ]);

        // 評価
        Assert::assertEquals($replyer->nickname, $reply->replyer_nickname, 'ログインユーザのニックネームであること');
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
        $profile = Profile::factory()->has(Journal::factory(1)->has(Comment::factory(1)))->create();
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
        $reply = $comment->replies()->create([
            'replyer_nickname' => $nickname
        ]);

        // 評価
        Assert::assertEquals($nickname, $reply->replyer_nickname, '指定したニックネームであること');
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
        $profile = Profile::factory()->has(Journal::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $this->assertThrows(function () use ($comment) {
            $comment->replies()->create([]);
        }, ApplicationException::class, 'ReplyerNicknameRequired');
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
        $profile = Profile::factory()->has(Journal::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);
        $nickname = '指定したニックネーム';

        // 実行
        $reply = $comment->replies()->create([
            'replyer_nickname' => $nickname
        ]);

        // 評価
        Assert::assertEquals($nickname, $reply->replyer_nickname, '指定したニックネームであること');
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
        $profile = Profile::factory()->has(Journal::factory(1)->has(Comment::factory(1)))->create();
        $comment = $profile->posts->first()->comments->first();

        // 返信者準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $reply = $comment->replies()->create([
            'replyer_nickname' => 'テストユーザ',
        ]);

        // 評価
        Assert::assertFalse($reply->is_public, '公開フラグがデフォルトであること');
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
        $profile = Profile::factory()->has(Journal::factory(1)
            ->has(Comment::factory(1, ['is_public' => true])
                ->has(Reply::factory(1, ['is_public' => true]))))->create();
        $reply = $profile->posts->first()->comments->first()->replies->first();

        // 評価
        Assert::assertTrue($reply->is_public, '公開であること');
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
        $profile = Profile::factory()->has(Journal::factory(1)
            ->has(Comment::factory(1, ['is_public' => true])
                ->has(Reply::factory(1, ['is_public' => false]))))->create();
        $reply = $profile->posts->first()->comments->first()->replies->first();

        // 評価
        Assert::assertFalse($reply->is_public, '非公開であること');
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
        $profile = Profile::factory()->has(Journal::factory(1)
            ->has(Comment::factory(1, ['is_public' => false])
                ->has(Reply::factory(1, ['is_public' => true]))))->create();
        $reply = $profile->posts->first()->comments->first()->replies->first();

        // 評価
        Assert::assertFalse($reply->is_public, '非公開であること');
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
        $profile = Profile::factory()->has(Journal::factory(1)
            ->has(Comment::factory(1, ['is_public' => false])
                ->has(Reply::factory(1, ['is_public' => false]))))->create();
        $reply = $profile->posts->first()->comments->first()->replies->first();

        // 評価
        Assert::assertFalse($reply->is_public, '非公開であること');
    }

    /**
     * 返信作成
     * 
     * - コメントに対する返信を作成することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信作成
     */
    public function test_create_reply()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'feeldee'])
            ->has(Journal::factory(['posted_at' => '2025-07-24'])->has(Comment::factory(['commenter_nickname' => 'ユーザ1'])->count(1))->count(1))->create();

        // 返信者準備
        $user = User::create([
            'id' => 99,
            'name' => '返信者',
            'email' => 'replyer@example.com',
            'password' => bcrypt('password123'),
        ]);
        $replyer = $user->profiles()->create([
            'id' => 99,
            'nickname' => '返信者プロフィール',
            'title' => '返信者プロフィールタイトル'
        ]);
        Auth::shouldReceive('user')->andReturn($user);

        // 実行
        $comment = Journal::by('feeldee')->at('2025-07-24')->first()->comments()->by('ユーザ1')->first();
        $reply = $comment->replies()->create([
            'replyer' => Auth::user()->profile,
            'body' => 'これはテスト返信です。',
        ]);

        // 評価
        $this->assertDatabaseHas('replies', [
            'id' => $reply->id,
            'replyer_profile_id' => $replyer->id,
            'replyer_nickname' => null,
            'body' => 'これはテスト返信です。',
        ]);
        $this->assertEquals($replyer->id, $reply->replyer->id, '返信者プロフィールのIDが設定されていること');
    }

    /**
     * 返信作成
     * 
     * - 返信者プロフィールおよび返信者ニックネームの両方を指定することもできることを確認します。
     * 
     * @link　https://github.com/ryossi/feeldee-framework/wiki/コメント#返信作成
     */
    public function test_create_reply_with_both_profile_and_nickname()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'feeldee'])
            ->has(Journal::factory(['posted_at' => '2025-07-24'])->has(Comment::factory(['commenter_nickname' => 'ユーザ1'])->count(1))->count(1))->create();

        // 返信者準備
        $user = User::create([
            'id' => 99,
            'name' => '返信者',
            'email' => 'replyer@example.com',
            'password' => bcrypt('password123'),
        ]);
        $replyer = $user->profiles()->create([
            'id' => 99,
            'nickname' => '返信者プロフィール',
            'title' => '返信者プロフィールタイトル'
        ]);
        Auth::shouldReceive('user')->andReturn($user);
        $replyer_nickname = 'test456';

        // 実行
        $comment = Journal::by('feeldee')->at('2025-07-24')->first()->comments()->by('ユーザ1')->first();
        $reply = $comment->replies()->create([
            'replyer' => Auth::user()->profile,
            'body' => 'これはテスト返信です。',
            'replyer_nickname' => $replyer_nickname,
        ]);

        // 評価
        $this->assertDatabaseHas('replies', [
            'id' => $reply->id,
            'replyer_profile_id' => $replyer->id,
            'replyer_nickname' => $replyer_nickname,
            'body' => 'これはテスト返信です。',
        ]);
        $this->assertEquals($replyer_nickname, $reply->replyer_nickname, '返信者プロフィールおよび返信者ニックネームの両方を指定することもできること');
    }

    /**
     * 返信作成
     * 
     * - 匿名ユーザの場合は、返信者ニックネームが必須であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信作成
     */
    public function test_create_reply_anonymous_nickname_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'feeldee'])
            ->has(Journal::factory(['posted_at' => '2025-07-24'])->has(Comment::factory(['commenter_nickname' => 'ユーザ1'])->count(1))->count(1))->create();
        $replyer_nickname = 'test456';

        // 実行
        $comment = Journal::by('feeldee')->at('2025-07-24')->first()->comments()->by('ユーザ1')->first();
        $reply = $comment->replies()->create([
            'body' => 'これはテスト返信です。',
            'replyer_nickname' => $replyer_nickname,
        ]);

        // 評価
        $this->assertDatabaseHas('replies', [
            'id' => $reply->id,
            'replyer_profile_id' => null,
            'replyer_nickname' => $replyer_nickname,
            'body' => 'これはテスト返信です。',
        ]);
        $this->assertEquals($replyer_nickname, $reply->replyer_nickname, '匿名ユーザの場合は、返信者ニックネームが必須であること');
    }

    /**
     * 返信作成
     * 
     * - 返信日時は、テストなどで任意の日付を指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信作成
     */
    public function test_create_reply_with_replied_at()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'feeldee'])
            ->has(Journal::factory(['posted_at' => '2025-07-24'])->has(Comment::factory(['commenter_nickname' => 'ユーザ1'])->count(1))->count(1))->create();
        $replyer_nickname = 'test456';
        $replied_at = '2025-03-30 10:34:10';

        // 実行
        $comment = Journal::by('feeldee')->at('2025-07-24')->first()->comments()->by('ユーザ1')->first();
        $reply = $comment->replies()->create([
            'body' => 'これはテスト返信です。',
            'replyer_nickname' => $replyer_nickname,
            'replied_at' => $replied_at,
        ]);

        // 評価
        $this->assertDatabaseHas('replies', [
            'id' => $reply->id,
            'replyer_profile_id' => null,
            'replyer_nickname' => $replyer_nickname,
            'body' => 'これはテスト返信です。',
            'replied_at' => $replied_at,
        ]);
        $this->assertEquals($replied_at, $reply->replied_at, '返信日時は、テストなどで任意の日付を指定することもできること');
    }

    /**
     * 返信公開
     * 
     * - 返信を公開できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開
     */
    public function test_doPublic()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(Journal::factory(1)
            ->has(Comment::factory(1, ['is_public' => true])
                ->has(Reply::factory(1, [
                    'replyer_nickname' => 'test456',
                    'replied_at' => '2000-01-01 09:00:00',
                    'is_public' => false
                ]))))->create();

        // 実行
        $reply = Reply::by('test456')->at('2000-01-01 09:00:00')->first();
        $reply->doPublic();

        // 評価
        Assert::assertTrue($reply->is_public, '公開できること');
    }

    /**
     * 返信公開
     * 
     * - 返信を非公開にできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信公開
     */
    public function test_doPrivate()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(Journal::factory(1)
            ->has(Comment::factory(1, ['is_public' => true])
                ->has(Reply::factory(1, [
                    'replyer_nickname' => 'test456',
                    'replied_at' => '2000-01-01 09:00:00',
                    'is_public' => true
                ]))))->create();
        // 実行
        $reply = Reply::by('test456')->at('2000-01-01 09:00:00')->first();
        $reply->doPrivate();

        // 評価
        Assert::assertFalse($reply->is_public, '非公開にできること');
    }

    /**
     * 返信リストの並び順
     * 
     * - 返信リストのデフォルトの並び順は、返信日時の降順（最新順）であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信リストの並び順
     */
    public function test_replies_order()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'feeldee'])->has(
            Journal::factory(1, ['posted_at' => '2025-07-24'])->has(
                Comment::factory(1)->has(
                    Reply::factory(3, [
                        'replyer_nickname' => 'test456',
                        'replied_at' => '2025-07-24 10:00:00'
                    ])->sequence(
                        ['id' => 1, 'replied_at' => '2025-07-24 10:00:00'],
                        ['id' => 2, 'replied_at' => '2025-07-24 11:00:00'],
                        ['id' => 3, 'replied_at' => '2025-07-24 12:00:00']
                    )
                )
            )
        )->create();

        // 実行
        $comment = Journal::by('feeldee')->at('2025-07-24')->first()->comments()->first();
        $replies = $comment->replies;

        // 評価
        Assert::assertEquals([3, 2, 1], $replies->pluck('id')->toArray(), '返信リストのデフォルトの並び順は、返信日時の降順（最新順）であること');
    }

    /**
     * 返信リストの並び順
     * 
     * - 古い返信から順番に並び替えたい場合は、返信日時昇順でソートできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信リストの並び順
     */
    public function test_replies_order_oldest()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'feeldee'])->has(
            Journal::factory(1, ['posted_at' => '2025-07-24'])->has(
                Comment::factory(1)->has(
                    Reply::factory(3, [
                        'replyer_nickname' => 'test456',
                        'replied_at' => '2025-07-24 10:00:00'
                    ])->sequence(
                        ['id' => 1, 'replied_at' => '2025-07-24 10:00:00'],
                        ['id' => 2, 'replied_at' => '2025-07-24 11:00:00'],
                        ['id' => 3, 'replied_at' => '2025-07-24 12:00:00']
                    )
                )
            )
        )->create();

        // 実行
        $comment = Journal::by('feeldee')->at('2025-07-24')->first()->comments()->first();
        $replies = $comment->replies()->orderOldest()->get();

        // 評価
        Assert::assertEquals([1, 2, 3], $replies->pluck('id')->toArray(), '古い返信から順番に並び替えたい場合は、返信日時昇順でソートできること');
    }

    /**
     * 返信リストの並び順
     * 
     * - 独自のSQLにおいてソート指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信リストの並び順
     */
    public function test_replies_order_custom_sql()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'feeldee'])->has(
            Journal::factory(1, ['posted_at' => '2025-07-24'])->has(
                Comment::factory(1)->has(
                    Reply::factory(3, [
                        'replyer_nickname' => 'test456',
                        'replied_at' => '2025-07-24 10:00:00',
                    ])->sequence(
                        ['id' => 1, 'replied_at' => '2025-07-24 10:00:00'],
                        ['id' => 2, 'replied_at' => '2025-07-24 11:00:00'],
                        ['id' => 3, 'replied_at' => '2025-07-24 12:00:00']
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::by('test456')->orderLatest()->get();

        // 評価
        Assert::assertEquals([3, 2, 1], $replies->pluck('id')->toArray(), '独自のSQLにおいてソート指定することもできること');
    }
}
