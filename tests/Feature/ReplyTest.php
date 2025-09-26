<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Comment;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\Journal;
use Feeldee\Framework\Models\PublicLevel;
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
        $comment = $profile->journals->first()->comments->first();

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
        $comment = $profile->journals->first()->comments->first();
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
        $comment = $profile->journals->first()->comments->first();

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
        $comment = $profile->journals->first()->comments->first();
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
        $comment = $profile->journals->first()->comments->first();
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
        $comment = $profile->journals->first()->comments->first();

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
        $comment = $profile->journals->first()->comments->first();

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
        $comment = $profile->journals->first()->comments->first();

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
        $comment = $profile->journals->first()->comments->first();

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
        $comment = $profile->journals->first()->comments->first();

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
        $comment = $profile->journals->first()->comments->first();

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
        $comment = $profile->journals->first()->comments->first();

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
        $profile = Profile::factory()->has(Journal::factory(1, ['is_public' => true])
            ->has(Comment::factory(1, ['is_public' => true])
                ->has(Reply::factory(1, ['is_public' => true]))))->create();
        $reply = $profile->journals->first()->comments->first()->replies->first();

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
        $profile = Profile::factory()->has(Journal::factory(1, ['is_public' => true])
            ->has(Comment::factory(1, ['is_public' => true])
                ->has(Reply::factory(1, ['is_public' => false]))))->create();
        $reply = $profile->journals->first()->comments->first()->replies->first();

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
        $profile = Profile::factory()->has(Journal::factory(1, ['is_public' => true])
            ->has(Comment::factory(1, ['is_public' => false])
                ->has(Reply::factory(1, ['is_public' => true]))))->create();
        $reply = $profile->journals->first()->comments->first()->replies->first();

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
        $profile = Profile::factory()->has(Journal::factory(1, ['is_public' => true])
            ->has(Comment::factory(1, ['is_public' => false])
                ->has(Reply::factory(1, ['is_public' => false]))))->create();
        $reply = $profile->journals->first()->comments->first()->replies->first();

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
        Profile::factory()->has(Journal::factory(1, ['is_public' => true])
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
        Profile::factory()->has(Journal::factory(1, ['is_public' => true])
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
     * - 最新の返信から順番に取得する場合は、返信日時降順でソートできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信リストの並び順
     */
    public function test_replies_order_latest()
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

    /**
     * 返信リストの並び順
     * 
     * - 最新(latest|desc)の文字列を直接指定してソートすることができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント# 返信リストの並び順
     */
    public function test_comments_order_direction_latest_or_desc()
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
        $repliesLatest = Reply::by('test456')->orderDirection('latest')->get();
        $repliesDesc = Reply::by('test456')->orderDirection('desc')->get();

        // 評価
        Assert::assertEquals([3, 2, 1], $repliesLatest->pluck('id')->toArray(), '最新(latest)の文字列を直接指定してソートすることができること');
        Assert::assertEquals([3, 2, 1], $repliesDesc->pluck('id')->toArray(), '最新(desc)の文字列を直接指定してソートすることができること');
    }

    /**
     *  返信リストの並び順
     * 
     * - 古いもの(oldest|asc)の文字列を直接指定してソートすることができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント# 返信リストの並び順
     */
    public function test_comments_order_direction_oldest_or_asc()
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
        $repliesOldest = Reply::by('test456')->orderDirection('oldest')->get();
        $repliesAsc = Reply::by('test456')->orderDirection('asc')->get();

        // 評価
        Assert::assertEquals([1, 2, 3], $repliesOldest->pluck('id')->toArray(), '古い(oldest)の文字列を直接指定してソートすることができること');
        Assert::assertEquals([1, 2, 3], $repliesAsc->pluck('id')->toArray(), '古い(asc)の文字列を直接指定してソートすることができること');
    }

    /**
     * 返信者による絞り込み
     * 
     * - 返信者のニックネームで返信を絞り込むことができることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信者による絞り込み
     */
    public function test_filter_by_replyer_nickname()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'feeldee'])->has(
            Journal::factory(1, ['posted_at' => '2025-07-24'])->has(
                Comment::factory(1)->has(
                    Reply::factory(3)->sequence(
                        ['id' => 1, 'replyer_nickname' => 'ユーザ1', 'replied_at' => '2025-07-24 10:00:00'],
                        ['id' => 2, 'replyer_nickname' => 'ユーザ1', 'replied_at' => '2025-07-24 11:00:00'],
                        ['id' => 3, 'replyer_nickname' => 'ユーザ2', 'replied_at' => '2025-07-24 12:00:00']
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::by('ユーザ1')->get();

        // 評価
        Assert::assertCount(2, $replies, '返信者のニックネームで返信を絞り込むことができること');
    }

    /**
     * 返信日時による絞り込み
     * 
     * - 返信日時で返信を絞り込むことができることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時による絞り込み
     */
    public function test_filter_at()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(1)->has(
                    Reply::factory(3)->sequence(
                        ['body' => '返信1', 'replyer_nickname' => 'ユーザ1', 'replied_at' => Carbon::parse('2025-04-22 10:00:00')],
                        ['body' => '返信2', 'replyer_nickname' => 'ユーザ2', 'replied_at' => Carbon::parse('2025-04-23 10:00:00')],
                        ['body' => '返信3', 'replyer_nickname' => 'ユーザ3', 'replied_at' => Carbon::parse('2025-09-12 09:30:00')],
                    )
                )
            )
        )->create();

        // 実行
        $reply = Reply::at('2025-09-12 09:30:00')->first();

        // 評価
        $this->assertEquals('返信3', $reply->body);
    }

    /**
     * 返信日時による絞り込み
     * 
     * - 時刻の一部を省略した場合には、指定した時刻での前方一致検索となることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時による絞り込み
     */
    public function test_filter_at_partial_time()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(1)->has(
                    Reply::factory(3)->sequence(
                        ['body' => '返信1', 'replyer_nickname' => 'ユーザ1', 'replied_at' => Carbon::parse('2025-04-22 10:00:00')],
                        ['body' => '返信2', 'replyer_nickname' => 'ユーザ2', 'replied_at' => Carbon::parse('2025-04-23 10:00:00')],
                        ['body' => '返信3', 'replyer_nickname' => 'ユーザ3', 'replied_at' => Carbon::parse('2025-09-12 09:30:00')],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::at('2025-09-12 09:30')->get();

        // 評価
        $this->assertEquals(1, $replies->count());
    }

    /**
     * 返信日時による絞り込み
     * 
     * - 時刻そのものを省略した場合には、指定した日付での前方一致検索となることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時による絞り込み
     */
    public function test_filter_at_date_only()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(1)->has(
                    Reply::factory(3)->sequence(
                        ['body' => '返信1', 'replyer_nickname' => 'ユーザ1', 'replied_at' => Carbon::parse('2025-09-13 10:00:00')],
                        ['body' => '返信2', 'replyer_nickname' => 'ユーザ2', 'replied_at' => Carbon::parse('2025-09-12 10:00:00')],
                        ['body' => '返信3', 'replyer_nickname' => 'ユーザ3', 'replied_at' => Carbon::parse('2025-09-12 09:30:00')],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::at('2025-09-12')->get();

        // 評価
        $this->assertEquals(2, $replies->count());
    }

    /**
     * 返信日時による絞り込み
     * 
     * - 返信日時の範囲を指定して取得できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時による絞り込み
     */
    public function test_filter_between()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(1)->has(
                    Reply::factory(3)->sequence(
                        ['body' => '返信1', 'replyer_nickname' => 'ユーザ1', 'replied_at' => Carbon::parse('2025-09-01 09:00:00')],
                        ['body' => '返信2', 'replyer_nickname' => 'ユーザ2', 'replied_at' => Carbon::parse('2025-09-01 10:00:00')],
                        ['body' => '返信3', 'replyer_nickname' => 'ユーザ3', 'replied_at' => Carbon::parse('2025-09-30 18:00:00')],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::between('2025-09-01 09:00:00', '2025-09-30 18:00:00')->get();

        // 評価
        $this->assertEquals(3, $replies->count());
    }

    /**
     * 返信日時による絞り込み
     * 
     * - 範囲指定で時刻の全部を省略した場合には、範囲の開始時刻が00:00:00、終了時刻が23:59:59となるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時による絞り込み
     */
    public function test_filter_between_time_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(1)->has(
                    Reply::factory(3)->sequence(
                        ['body' => '返信1', 'replyer_nickname' => 'ユーザ1', 'replied_at' => Carbon::parse('2025-09-01 00:00:00')],
                        ['body' => '返信2', 'replyer_nickname' => 'ユーザ2', 'replied_at' => Carbon::parse('2025-09-01 10:00:00')],
                        ['body' => '返信3', 'replyer_nickname' => 'ユーザ3', 'replied_at' => Carbon::parse('2025-09-30 23:59:59')],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::between('2025-09-01', '2025-09-30')->get();

        // 評価
        $this->assertEquals(3, $replies->count());
    }

    /**
     * 返信日時による絞り込み
     * 
     * - 範囲指定で時刻の一部を省略した場合には、範囲の開始時刻が00:00:00、終了時刻が23:59:59となるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時による絞り込み
     */
    public function test_filter_between_time_partial_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(1)->has(
                    Reply::factory(3)->sequence(
                        ['body' => '返信1', 'replyer_nickname' => 'ユーザ1', 'replied_at' => Carbon::parse('2025-09-01 09:00:00')],
                        ['body' => '返信2', 'replyer_nickname' => 'ユーザ2', 'replied_at' => Carbon::parse('2025-09-01 18:00:59')],
                        ['body' => '返信3', 'replyer_nickname' => 'ユーザ3', 'replied_at' => Carbon::parse('2025-09-01 18:01:00')],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::between('2025-09-01 09:00', '2025-09-01 18:00')->get();

        // 評価
        $this->assertEquals(2, $replies->count());
    }

    /**
     * 返信日時による絞り込み
     * 
     * - 返信日時の未満で範囲指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時による絞り込み
     */
    public function test_filter_before()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(1)->has(
                    Reply::factory(3)->sequence(
                        ['body' => '返信1', 'replyer_nickname' => 'ユーザ1', 'replied_at' => Carbon::parse('2025-09-01 08:59:59')],
                        ['body' => '返信2', 'replyer_nickname' => 'ユーザ2', 'replied_at' => Carbon::parse('2025-09-01 09:00:00')],
                        ['body' => '返信3', 'replyer_nickname' => 'ユーザ3', 'replied_at' => Carbon::parse('2025-09-01 09:00:01')],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::before('2025-09-01 09:00:00')->get();

        // 評価
        $this->assertEquals(1, $replies->count());
    }

    /**
     * 返信日時による絞り込み
     * 
     * - 返信日時の未満で範囲指定することもできることを確認します。
     * - 時刻の一部または全部を省略した場合には、省略部分が常に00:00:00と同じなるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時による絞り込み
     */
    public function test_filter_before_partial_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(1)->has(
                    Reply::factory(3)->sequence(
                        ['body' => '返信1', 'replyer_nickname' => 'ユーザ1', 'replied_at' => Carbon::parse('2025-09-01 09:29:59')],
                        ['body' => '返信2', 'replyer_nickname' => 'ユーザ2', 'replied_at' => Carbon::parse('2025-09-01 09:30:00')],
                        ['body' => '返信3', 'replyer_nickname' => 'ユーザ3', 'replied_at' => Carbon::parse('2025-09-01 09:30:01')],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::before('2025-09-01 09:30')->get();

        // 評価
        $this->assertEquals(1, $replies->count());
    }

    /**
     * 返信日時による絞り込み
     * 
     * - 返信日時のより先で範囲指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時による絞り込み
     */
    public function test_filter_after()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(1)->has(
                    Reply::factory(3)->sequence(
                        ['body' => '返信1', 'replyer_nickname' => 'ユーザ1', 'replied_at' => Carbon::parse('2025-09-01 09:29:59')],
                        ['body' => '返信2', 'replyer_nickname' => 'ユーザ2', 'replied_at' => Carbon::parse('2025-09-01 09:30:00')],
                        ['body' => '返信3', 'replyer_nickname' => 'ユーザ3', 'replied_at' => Carbon::parse('2025-09-01 09:30:01')],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::after('2025-09-01 09:30:00')->get();

        // 評価
        $this->assertEquals(1, $replies->count());
    }

    /**
     * 返信日時による絞り込み
     * 
     * - 返信日時のより先で範囲指定することもできることを確認します。
     * - 時刻の一部または全部を省略した場合には、省略部分が常に00:00:00と同じなるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時による絞り込み
     */
    public function test_filter_after_partial_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(1)->has(
                    Reply::factory(3)->sequence(
                        ['body' => '返信1', 'replyer_nickname' => 'ユーザ1', 'replied_at' => Carbon::parse('2025-09-01 09:29:59')],
                        ['body' => '返信2', 'replyer_nickname' => 'ユーザ2', 'replied_at' => Carbon::parse('2025-09-01 09:30:00')],
                        ['body' => '返信3', 'replyer_nickname' => 'ユーザ3', 'replied_at' => Carbon::parse('2025-09-01 09:30:01')],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::after('2025-09-01 09:30')->get();

        // 評価
        $this->assertEquals(1, $replies->count());
    }

    /**
     * 返信日時による絞り込み
     * 
     * - 返信日時の以前で範囲指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時による絞り込み
     */
    public function test_filter_beforeEquals()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(1)->has(
                    Reply::factory(3)->sequence(
                        ['body' => '返信1', 'replyer_nickname' => 'ユーザ1', 'replied_at' => Carbon::parse('2025-04-22 10:00:00')],
                        ['body' => '返信2', 'replyer_nickname' => 'ユーザ2', 'replied_at' => Carbon::parse('2025-04-23 10:00:00')],
                        ['body' => '返信3', 'replyer_nickname' => 'ユーザ3', 'replied_at' => Carbon::parse('2025-09-12 09:30:00')],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::beforeEquals('2025-09-01 09:30:00')->get();

        // 評価
        $this->assertEquals(2, $replies->count());
    }

    /**
     * 返信日時による絞り込み
     * 
     * - 返信日時の以前で範囲指定することもできることを確認します。
     * - 時刻の一部または全部を省略した場合には、省略部分が常に00:00:00と同じなるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時による絞り込み
     */
    public function test_filter_beforeEquals_partial_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(1)->has(
                    Reply::factory(3)->sequence(
                        ['body' => '返信1', 'replyer_nickname' => 'ユーザ1', 'replied_at' => Carbon::parse('2025-04-22 10:00:00')],
                        ['body' => '返信2', 'replyer_nickname' => 'ユーザ2', 'replied_at' => Carbon::parse('2025-04-23 10:00:00')],
                        ['body' => '返信3', 'replyer_nickname' => 'ユーザ3', 'replied_at' => Carbon::parse('2025-09-12 09:30:00')],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::beforeEquals('2025-09-01 09:30')->get();

        // 評価
        $this->assertEquals(2, $replies->count());
    }

    /**
     * 返信日時による絞り込み
     * 
     * - 返信日時の以降で範囲指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時による絞り込み
     */
    public function test_filter_afterEquals()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(1)->has(
                    Reply::factory(3)->sequence(
                        ['body' => '返信1', 'replyer_nickname' => 'ユーザ1', 'replied_at' => Carbon::parse('2025-09-01 09:29:59')],
                        ['body' => '返信2', 'replyer_nickname' => 'ユーザ2', 'replied_at' => Carbon::parse('2025-09-01 09:30:00')],
                        ['body' => '返信3', 'replyer_nickname' => 'ユーザ3', 'replied_at' => Carbon::parse('2025-09-01 09:30:01')],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::afterEquals('2025-09-01 09:30:00')->get();

        // 評価
        $this->assertEquals(2, $replies->count());
    }

    /**
     * 返信日時による絞り込み
     * 
     * - 返信日時の以降で範囲指定することもできることを確認します。
     * - 時刻の一部または全部を省略した場合には、省略部分が常に00:00:00と同じなるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信日時による絞り込み
     */
    public function test_filter_afterEquals_partial_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(1)->has(
                    Reply::factory(3)->sequence(
                        ['body' => '返信1', 'replyer_nickname' => 'ユーザ1', 'replied_at' => Carbon::parse('2025-09-01 09:29:59')],
                        ['body' => '返信2', 'replyer_nickname' => 'ユーザ2', 'replied_at' => Carbon::parse('2025-09-01 09:30:00')],
                        ['body' => '返信3', 'replyer_nickname' => 'ユーザ3', 'replied_at' => Carbon::parse('2025-09-01 09:30:01')],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::afterEquals('2025-09-01 09:30')->get();

        // 評価
        $this->assertEquals(2, $replies->count());
    }

    /**
     * 公開・非公開による絞り込み
     * 
     * - 公開された返信のみ取得する場合は、publicローカルスコープが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#公開・非公開による絞り込み
     */
    public function test_filter_public()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory(['is_public' => true])->count(1)->has(
                Comment::factory(1, ['is_public' => true])->has(
                    Reply::factory(3)->sequence(
                        ['body' => '返信1', 'replyer_nickname' => 'Feeldee', 'replied_at' => Carbon::parse('2025-09-01 09:30:00'), 'is_public' => true],
                        ['body' => '返信2', 'replyer_nickname' => 'Feeldee', 'replied_at' => Carbon::parse('2025-09-01 09:31:00'), 'is_public' => false],
                        ['body' => '返信3', 'replyer_nickname' => 'Feeldee', 'replied_at' => Carbon::parse('2025-09-01 09:32:00'), 'is_public' => true],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::by('Feeldee')->at('2025-09-01')->public()->get();

        // 評価
        $this->assertEquals(2, $replies->count());
    }

    /**
     * 公開・非公開による絞り込み
     * 
     * - 非公開の返信のみを取得する場合は、privateローカルスコープを利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#公開・非公開による絞り込み
     */
    public function test_filter_private()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory(['is_public' => true])->count(1)->has(
                Comment::factory(1, ['is_public' => true])->has(
                    Reply::factory(3)->sequence(
                        ['replied_at' => Carbon::parse('2025-09-01 09:30:00'), 'is_public' => true],
                        ['replied_at' => Carbon::parse('2025-09-01 09:31:00'), 'is_public' => false],
                        ['replied_at' => Carbon::parse('2025-09-01 09:32:00'), 'is_public' => true],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::at('2025-09-01')->private()->get();

        // 評価
        $this->assertEquals(1, $replies->count());
    }

    /**
     * 公開・非公開による絞り込み
     * 
     * - 返信の公開・非公開は、常に返信対象のコメント公開フラグとのAND条件となることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#公開・非公開による絞り込み
     */
    public function test_filter_public_or_private_with_comment_is_private()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory(['is_public' => true])->count(1)->has(
                Comment::factory(1, ['is_public' => false])->has(
                    Reply::factory(3)->sequence(
                        ['replied_at' => Carbon::parse('2025-09-01 09:30:00'), 'is_public' => true],
                        ['replied_at' => Carbon::parse('2025-09-01 09:31:00'), 'is_public' => false],
                        ['replied_at' => Carbon::parse('2025-09-01 09:32:00'), 'is_public' => true],
                    )
                )
            )
        )->create();

        // 実行
        $replies = Reply::at('2025-09-01')->public()->get();

        // 評価
        $this->assertEquals(0, $replies->count());
    }

    /**
     * 閲覧可能な返信の絞り込み
     * 
     * - 匿名ユーザでも閲覧可能な返信リストを取得できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/返信#閲覧可能な返信の絞り込み
     */
    public function test_filter_viewable_with_anonymous_user()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->create();
        $commenter = Profile::factory(['nickname' => 'Commenter'])->create();
        $replyer = Profile::factory(['nickname' => 'Replyer'])->create();
        // コメント対象が非公開
        Journal::factory(['is_public' => false, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済みだが、返信対象が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => false, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済み、かつ返信対象も公開済みだが、返信が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => false, 'replyer_profile_id' => $replyer->id],
                )
            )
        )->for($profile)->create();
        // コメント対象が「全員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();
        // コメント対象が「会員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Member])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();
        // コメント対象が「友達」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Friend])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();
        // コメント対象が「自分」に公開済みで、コメントも公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Private])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();

        // 実行
        $replies = Reply::viewable()->get();

        // 評価
        $this->assertEquals(1, $replies->count());
        Assert::assertTrue($replies->first()->is_public, '返信が公開されていること');
        Assert::assertTrue($replies->first()->comment->is_public, '返信対象が公開されていること');
        Assert::assertTrue($replies->first()->comment->commentable->is_public, 'コメント対象が公開されていること');
        Assert::assertEquals(PublicLevel::Public, $replies->first()->comment->commentable->public_level, 'コメント対象の公開レベル「全員」であること');
    }

    /**
     * 閲覧可能な返信の絞り込み
     * 
     * - ログイン済みユーザが閲覧可能な返信リストを取得できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/返信#閲覧可能な返信の絞り込み
     */
    public function test_filter_viewable_with_logged_in_user()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $user = User::create([
            'name' => 'テストユーザ',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        $user->profiles()->create([
            'nickname' => 'Viewer',
            'title' => '閲覧者'
        ]);
        Auth::shouldReceive('user')->andReturn($user);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->create();
        $commenter = Profile::factory(['nickname' => 'Commenter'])->create();
        $replyer = Profile::factory(['nickname' => 'Replyer'])->create();
        // コメント対象が非公開
        Journal::factory(['is_public' => false, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済みだが、返信対象が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => false, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済み、かつ返信対象も公開済みだが、返信が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => false, 'replyer_profile_id' => $replyer->id],
                )
            )
        )->for($profile)->create();
        // コメント対象が「全員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();
        // コメント対象が「会員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Member])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();
        // コメント対象が「友達」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Friend])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();
        // コメント対象が「自分」に公開済みで、コメントも公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Private])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();

        // 実行
        $replies = Reply::viewable(Auth::user())->get();

        // 評価
        $this->assertEquals(2, $replies->count());
        foreach ($replies as $reply) {
            Assert::assertTrue($reply->is_public, '返信が公開されていること');
            Assert::assertTrue($reply->comment->is_public, '返信対象が公開されていること');
            Assert::assertTrue($reply->comment->commentable->is_public, 'コメント対象が公開されていること');
            Assert::assertTrue(in_array($reply->comment->commentable->public_level, [PublicLevel::Public, PublicLevel::Member]), 'コメント対象の公開レベルが「全員」または「会員」であること');
        }
    }

    /**
     * 閲覧可能な返信の絞り込み
     * 
     * - 友達リストに登録済みプロフィールで閲覧可能な返信リストを取得できることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/返信#閲覧可能な返信の絞り込み
     */
    public function test_filter_viewable_with_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->hasAttached(Profile::factory(['nickname' => 'Friend']), [], 'friends')->create();
        $commenter = Profile::factory(['nickname' => 'Commenter'])->create();
        $replyer = Profile::factory(['nickname' => 'Replyer'])->create();
        // コメント対象が非公開
        Journal::factory(['is_public' => false, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済みだが、返信対象が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => false, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済み、かつ返信対象も公開済みだが、返信が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => false, 'replyer_profile_id' => $replyer->id],
                )
            )
        )->for($profile)->create();
        // コメント対象が「全員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();
        // コメント対象が「会員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Member])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();
        // コメント対象が「友達」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Friend])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();
        // コメント対象が「自分」に公開済みで、コメントも公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Private])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();

        // 実行
        $replies = Reply::viewable(Profile::of('Friend')->first())->get();

        // 評価
        $this->assertEquals(3, $replies->count());
        Assert::assertTrue($replies->first()->is_public, '返信が公開されていること');
        Assert::assertTrue($replies->first()->comment->is_public, '返信対象が公開されていること');
        Assert::assertTrue($replies->first()->comment->commentable->is_public, 'コメント対象が公開されていること');
        Assert::assertTrue(in_array($replies->first()->comment->commentable->public_level, [PublicLevel::Public, PublicLevel::Member, PublicLevel::Friend]), 'コメント対象の公開レベルが「全員」「会員」「友達」であること');
    }

    /**
     * 閲覧可能な返信の絞り込み
     * 
     * - 自分自身が閲覧可能な返信リストを取得できることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/返信#閲覧可能な返信の絞り込み
     */
    public function test_filter_viewable_with_nickname()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->hasAttached(Profile::factory(['nickname' => 'Friend']), [], 'friends')->create();
        $commenter = Profile::factory(['nickname' => 'Commenter'])->create();
        $replyer = Profile::factory(['nickname' => 'Replyer'])->create();
        // コメント対象が非公開
        Journal::factory(['is_public' => false, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済みだが、返信対象が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => false, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済み、かつ返信対象も公開済みだが、返信が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => false, 'replyer_profile_id' => $replyer->id],
                )
            )
        )->for($profile)->create();
        // コメント対象が「全員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();
        // コメント対象が「会員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Member])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();
        // コメント対象が「友達」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Friend])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();
        // コメント対象が「自分」に公開済みで、コメントも公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Private])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();

        // 実行
        $replies = Reply::viewable('Feeldee')->get();

        // 評価
        $this->assertEquals(4, $replies->count());
        Assert::assertTrue($replies->first()->is_public, '返信が公開されていること');
        Assert::assertTrue($replies->first()->comment->is_public, '返信対象が公開されていること');
        Assert::assertTrue($replies->first()->comment->commentable->is_public, 'コメント対象が公開されていること');
        Assert::assertTrue(in_array($replies->first()->comment->commentable->public_level, [PublicLevel::Public, PublicLevel::Member, PublicLevel::Friend, PublicLevel::Private]), 'コメント対象の公開レベルが「全員」「会員」「友達」「自分」であること');
    }

    /**
     * 閲覧可能な返信の絞り込み
     * 
     * - 自分自身の返信については、コメント対象の投稿公開レベルにかかわらず、公開済みであれば閲覧可能であることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/返信#閲覧可能な返信の絞り込み
     */
    public function test_filter_viewable_with_my_reply()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->hasAttached(Profile::factory(['nickname' => 'Friend']), [], 'friends')->create();
        $commenter = Profile::factory(['nickname' => 'Commenter'])->create();
        $replyer = Profile::factory(['nickname' => 'Replyer'])->create();
        // コメント対象が非公開
        Journal::factory(['is_public' => false, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済みだが、返信対象が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => false, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済み、かつ返信対象も公開済みだが、返信が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => false, 'replyer_profile_id' => $replyer->id],
                )
            )
        )->for($profile)->create();
        // コメント対象が「全員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();
        // コメント対象が「会員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Member])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();
        // コメント対象が「友達」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Friend])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();
        // コメント対象が「自分」に公開済みで、コメントも公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Private])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id]
                )
            )
        )->for($profile)->create();

        // 実行
        $replies = Reply::viewable('Replyer')->get();

        // 評価
        $this->assertEquals(4, $replies->count());
        Assert::assertTrue($replies->first()->is_public, '返信が公開されていること');
        Assert::assertTrue($replies->first()->comment->is_public, '返信対象が公開されていること');
        Assert::assertTrue($replies->first()->comment->commentable->is_public, 'コメント対象が公開されていること');
        Assert::assertTrue(in_array($replies->first()->comment->commentable->public_level, [PublicLevel::Public, PublicLevel::Member, PublicLevel::Friend, PublicLevel::Private]), 'コメント対象の公開レベルが「全員」「会員」「友達」「自分」であること');
    }

    /**
     * 閲覧可能な返信の絞り込み
     * 
     * - isViewableメソッドで匿名ユーザにも閲覧可能かどうかを判定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/返信#閲覧可能な返信の絞り込み
     */
    public function test_is_viewable_with_anonymous_user()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->hasAttached(Profile::factory(['nickname' => 'Friend']), [], 'friends')->create();
        $commenter = Profile::factory(['nickname' => 'Commenter'])->create();
        $replyer = Profile::factory(['nickname' => 'Replyer'])->create();
        // コメント対象が非公開
        Journal::factory(['is_public' => false, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-21'],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済みだが、返信対象が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => false, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-22'],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済み、かつ返信対象も公開済みだが、返信が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => false, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-23'],
                )
            )
        )->for($profile)->create();
        // コメント対象が「全員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-24']
                )
            )
        )->for($profile)->create();
        // コメント対象が「会員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Member])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-25']
                )
            )
        )->for($profile)->create();
        // コメント対象が「友達」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Friend])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-26']
                )
            )
        )->for($profile)->create();
        // コメント対象が「自分」に公開済みで、コメントも公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Private])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-27']
                )
            )
        )->for($profile)->create();

        // 実行
        $post_false = Reply::by('Replyer')->at('2025-09-21')->first()->isViewable();
        $comment_false = Reply::by('Replyer')->at('2025-09-22')->first()->isViewable();
        $reply_false = Reply::by('Replyer')->at('2025-09-23')->first()->isViewable();
        $post_public = Reply::by('Replyer')->at('2025-09-24')->first()->isViewable();
        $post_member = Reply::by('Replyer')->at('2025-09-25')->first()->isViewable();
        $post_friend = Reply::by('Replyer')->at('2025-09-26')->first()->isViewable();
        $post_private = Reply::by('Replyer')->at('2025-09-27')->first()->isViewable();

        // 評価
        $this->assertFalse($post_false, 'コメント対象が非公開のコメントは匿名ユーザには閲覧できないこと');
        $this->assertFalse($comment_false, '非公開コメントは匿名ユーザには閲覧できないこと');
        $this->assertFalse($reply_false, '非公開返信は匿名ユーザには閲覧できないこと');
        $this->assertTrue($post_public, '「全員」は匿名ユーザにも閲覧可能であること');
        $this->assertFalse($post_member, '「会員」は匿名ユーザには閲覧できないこと');
        $this->assertFalse($post_friend, '「友達」は匿名ユーザには閲覧できないこと');
        $this->assertFalse($post_private, '「自分」は匿名ユーザには閲覧できないこと');
    }

    /**
     * 閲覧可能な返信の絞り込み
     * 
     * - isViewableメソッドでログイン中のユーザが閲覧可能かどうかを判定できることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/返信#閲覧可能な返信の絞り込み
     */
    public function test_is_viewable_with_logged_in_user()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $user = User::create([
            'name' => 'テストユーザ',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        $user->profiles()->create([
            'nickname' => 'Viewer',
            'title' => '閲覧者'
        ]);
        Auth::shouldReceive('user')->andReturn($user);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->hasAttached(Profile::factory(['nickname' => 'Friend']), [], 'friends')->create();
        $commenter = Profile::factory(['nickname' => 'Commenter'])->create();
        $replyer = Profile::factory(['nickname' => 'Replyer'])->create();
        // コメント対象が非公開
        Journal::factory(['is_public' => false, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-21'],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済みだが、返信対象が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => false, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-22'],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済み、かつ返信対象も公開済みだが、返信が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => false, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-23'],
                )
            )
        )->for($profile)->create();
        // コメント対象が「全員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-24']
                )
            )
        )->for($profile)->create();
        // コメント対象が「会員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Member])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-25']
                )
            )
        )->for($profile)->create();
        // コメント対象が「友達」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Friend])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-26']
                )
            )
        )->for($profile)->create();
        // コメント対象が「自分」に公開済みで、コメントも公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Private])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-27']
                )
            )
        )->for($profile)->create();

        // 実行
        $post_false = Reply::by('Replyer')->at('2025-09-21')->first()->isViewable(Auth::user());
        $comment_false = Reply::by('Replyer')->at('2025-09-22')->first()->isViewable(Auth::user());
        $reply_false = Reply::by('Replyer')->at('2025-09-23')->first()->isViewable(Auth::user());
        $post_public = Reply::by('Replyer')->at('2025-09-24')->first()->isViewable(Auth::user());
        $post_member = Reply::by('Replyer')->at('2025-09-25')->first()->isViewable(Auth::user());
        $post_friend = Reply::by('Replyer')->at('2025-09-26')->first()->isViewable(Auth::user());
        $post_private = Reply::by('Replyer')->at('2025-09-27')->first()->isViewable(Auth::user());

        // 評価
        $this->assertFalse($post_false, 'コメント対象が非公開のコメントはログインユーザには閲覧できないこと');
        $this->assertFalse($comment_false, '非公開コメントはログインユーザには閲覧できないこと');
        $this->assertFalse($reply_false, '非公開返信はログインユーザには閲覧できないこと');
        $this->assertTrue($post_public, '「全員」はログインユーザにも閲覧可能であること');
        $this->assertTrue($post_member, '「会員」はログインユーザにも閲覧可能であること');
        $this->assertFalse($post_friend, '「友達」はログインユーザには閲覧できないこと');
        $this->assertFalse($post_private, '「自分」はログインユーザには閲覧できないこと');
    }

    /**
     * 閲覧可能な返信の絞り込み
     * 
     * - isViewableメソッドでコメント対象の友達リストに登録されたプロフィールで閲覧可能かどうかを判定できることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#閲覧可能なコメントの絞り込み
     */
    public function test_is_viewable_with_friend_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->hasAttached(Profile::factory(['nickname' => 'Friend']), [], 'friends')->create();
        $commenter = Profile::factory(['nickname' => 'Commenter'])->create();
        $replyer = Profile::factory(['nickname' => 'Replyer'])->create();
        // コメント対象が非公開
        Journal::factory(['is_public' => false, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-21'],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済みだが、返信対象が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => false, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-22'],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済み、かつ返信対象も公開済みだが、返信が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => false, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-23'],
                )
            )
        )->for($profile)->create();
        // コメント対象が「全員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-24']
                )
            )
        )->for($profile)->create();
        // コメント対象が「会員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Member])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-25']
                )
            )
        )->for($profile)->create();
        // コメント対象が「友達」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Friend])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-26']
                )
            )
        )->for($profile)->create();
        // コメント対象が「自分」に公開済みで、コメントも公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Private])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-27']
                )
            )
        )->for($profile)->create();

        // 実行
        $post_false = Reply::by('Replyer')->at('2025-09-21')->first()->isViewable('Friend');
        $comment_false = Reply::by('Replyer')->at('2025-09-22')->first()->isViewable('Friend');
        $reply_false = Reply::by('Replyer')->at('2025-09-23')->first()->isViewable('Friend');
        $post_public = Reply::by('Replyer')->at('2025-09-24')->first()->isViewable('Friend');
        $post_member = Reply::by('Replyer')->at('2025-09-25')->first()->isViewable('Friend');
        $post_friend = Reply::by('Replyer')->at('2025-09-26')->first()->isViewable('Friend');
        $post_private = Reply::by('Replyer')->at('2025-09-27')->first()->isViewable('Friend');

        // 評価
        $this->assertFalse($post_false, 'コメント対象が非公開のコメントは友達登録されたプロフィールには閲覧できないこと');
        $this->assertFalse($comment_false, '非公開コメントは友達登録されたプロフィールには閲覧できないこと');
        $this->assertFalse($reply_false, '非公開返信は友達登録されたプロフィールには閲覧できないこと');
        $this->assertTrue($post_public, '「全員」は友達登録されたプロフィールにも閲覧可能であること');
        $this->assertTrue($post_member, '「会員」は友達登録されたプロフィールにも閲覧可能であること');
        $this->assertTrue($post_friend, '「友達」は友達登録されたプロフィールにも閲覧可能であること');
        $this->assertFalse($post_private, '「自分」は友達登録されたプロフィールには閲覧できないこと');
    }

    /**
     * 閲覧可能な返信の絞り込み
     * 
     * - isViewableメソッドでコメント対象の投稿者自身がニックネームで閲覧可能かどうかを判定できることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/返信#閲覧可能な返信の絞り込み
     */
    public function test_is_viewable_with_nickname()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'Feeldee'])->hasAttached(Profile::factory(['nickname' => 'Friend']), [], 'friends')->create();
        $commenter = Profile::factory(['nickname' => 'Commenter'])->create();
        $replyer = Profile::factory(['nickname' => 'Replyer'])->create();
        // コメント対象が非公開
        Journal::factory(['is_public' => false, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-21'],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済みだが、返信対象が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => false, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-22'],
                )
            )
        )->for($profile)->create();
        // コメント対象は「全員」に公開済み、かつ返信対象も公開済みだが、返信が非公開
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => false, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-23'],
                )
            )
        )->for($profile)->create();
        // コメント対象が「全員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Public])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-24']
                )
            )
        )->for($profile)->create();
        // コメント対象が「会員」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Member])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-25']
                )
            )
        )->for($profile)->create();
        // コメント対象が「友達」に公開済みで、返信対象も返信も公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Friend])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-26']
                )
            )
        )->for($profile)->create();
        // コメント対象が「自分」に公開済みで、コメントも公開済み
        Journal::factory(['is_public' => true, 'public_level' => PublicLevel::Private])->count(1)->has(
            Comment::factory(['is_public' => true, 'commenter_profile_id' => $commenter->id])->has(
                Reply::factory(
                    ['is_public' => true, 'replyer_profile_id' => $replyer->id, 'replied_at' => '2025-09-27']
                )
            )
        )->for($profile)->create();

        // 実行
        $post_false = Reply::by('Replyer')->at('2025-09-21')->first()->isViewable('Replyer');
        $comment_false = Reply::by('Replyer')->at('2025-09-22')->first()->isViewable('Replyer');
        $reply_false = Reply::by('Replyer')->at('2025-09-23')->first()->isViewable('Replyer');
        $post_public = Reply::by('Replyer')->at('2025-09-24')->first()->isViewable('Replyer');
        $post_member = Reply::by('Replyer')->at('2025-09-25')->first()->isViewable('Replyer');
        $post_friend = Reply::by('Replyer')->at('2025-09-26')->first()->isViewable('Replyer');
        $post_private = Reply::by('Replyer')->at('2025-09-27')->first()->isViewable('Replyer');

        // 評価
        $this->assertFalse($post_false, 'コメント対象が非公開のコメントは自分自身でも閲覧できないこと');
        $this->assertFalse($comment_false, '非公開コメントは自分自身でも閲覧できないこと');
        $this->assertFalse($reply_false, '非公開返信は自分自身でも閲覧できないこと');
        $this->assertTrue($post_public, '「全員」は自分自身にも閲覧可能であること');
        $this->assertTrue($post_member, '「会員」は自分自身にも閲覧可能であること');
        $this->assertTrue($post_friend, '「友達」は自分自身にも閲覧可能であること');
        $this->assertTrue($post_private, '「自分」は自分自身にも閲覧可能であること');
    }
}
