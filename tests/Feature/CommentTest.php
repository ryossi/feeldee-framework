<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Comment;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\Journal;
use Feeldee\Framework\Models\Reply;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\Assert;
use Tests\Models\User;

/**
 * コメントの機能テストです。
 * 
 * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント
 */
class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * コメント所有者
     * 
     * - コメントされた投稿（以降、コメント対象）に紐付くプロフィールが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント所有者
     */
    public function test_profile()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Journal::factory()->count(1))->create();
        $post = $profile->journals->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = $post->comments()->create([
            'body' => 'これはテストコメントです。',
            'commenter_nickname' => 'テストユーザ'
        ]);

        // 評価
        Assert::assertEquals($comment->profile, $comment->commentable->profile, 'コメント対象に紐付くプロフィールであること');
    }

    /**
     * コメント日時
     * 
     * - 任意の日時を指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時
     */
    public function test_commented_at_specify()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Journal::factory()->count(1))->create();
        $post = $profile->journals->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);
        $commented_at = '2025-03-27 09:30:20';

        // 実行
        $comment = $post->comments()->create([
            'commented_at' => $commented_at,
            'body' => 'これはテストコメントです。',
            'commenter_nickname' => 'テストユーザ'
        ]);

        // 評価
        Assert::assertEquals($commented_at, $comment->commented_at->format('Y-m-d H:i:s'), '指定した日時であること');
    }

    /**
     * コメント日時
     * 
     * - コメント日時が指定されなかった場合はシステム日時が自動で設定されれることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時
     */
    public function test_commented_at_default()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Journal::factory()->count(1))->create();
        $post = $profile->journals->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = $post->comments()->create([
            'body' => 'これはテストコメントです。',
            'commenter_nickname' => 'テストユーザ'
        ]);

        // 評価
        Assert::assertNotEmpty($comment->commented_at, 'システム日時が設定されること');
    }

    /**
     * コメント本文
     * 
     * - テキストが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント本文
     */
    public function test_body_text()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Journal::factory()->count(1))->create();
        $post = $profile->journals->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);
        $body = 'これはテストコメントです。';

        // 実行
        $comment = $post->comments()->create([
            'body' => $body,
            'commenter_nickname' => 'テストユーザ'
        ]);

        // 評価
        Assert::assertEquals($body, $comment->body, 'テキストが使用できること');
    }

    /**
     * コメント本文
     * 
     * - HTMLが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント本文
     */
    public function test_body_html()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Journal::factory()->count(1))->create();
        $post = $profile->journals->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);
        $body = '<h1>>これはテストコメントです。</h1>';

        // 実行
        $comment = $post->comments()->create([
            'body' => $body,
            'commenter_nickname' => 'テストユーザ'
        ]);

        // 評価
        Assert::assertEquals($body, $comment->body, 'HTMLが使用できること');
    }

    /**
     * コメント対象
     * 
     * - コメント対象IDには、コメント対象のIDが設定されることを確認します。
     * - コメント対象種別とコメント対象IDを組み合わせてコメント対象を特定できることを確認します。
     * - コメント対象種別には、コメントが可能な投稿のモデルをあらわす識別文字列が自動設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント対象
     */
    public function test_commentable_posts()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Journal::factory()->count(1))->create();
        $post = $profile->journals->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = $post->comments()->create([
            'body' => 'これはテストコメントです。',
            'commenter_nickname' => 'テストユーザ'
        ]);

        // 評価
        Assert::assertEquals($post->id, $comment->commentable->id, 'コメント対象IDには、コメント対象のIDが設定されること');
        Assert::assertInstanceOf(Journal::class, $comment->commentable, 'コメント対象種別とコメント対象IDを組み合わせてコメント対象を特定できること');
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Journal::type(),
        ]);
    }

    /**
     * コメント対象
     * 
     * - コメント対象IDには、コメント対象のIDが設定されることを確認します。
     * - コメント対象種別とコメント対象IDを組み合わせてコメント対象を特定できることを確認します。
     * - コメント対象種別には、コメントが可能な投稿のモデルをあらわす識別文字列が自動設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント対象
     */
    public function test_commentable_photos()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Photo::factory()->count(1))->create();
        $photo = $profile->photos->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = $photo->comments()->create([
            'body' => 'これはテストコメントです。',
            'commenter_nickname' => 'テストユーザ'
        ]);

        // 評価
        Assert::assertEquals($photo->id, $comment->commentable->id, 'コメント対象IDには、コメント対象のIDが設定されること');
        Assert::assertInstanceOf(Photo::class, $comment->commentable, 'コメント対象種別とコメント対象IDを組み合わせてコメント対象を特定できること');
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Photo::type()
        ]);
    }

    /**
     * コメント対象
     * 
     * - コメント対象IDには、コメント対象のIDが設定されることを確認します。
     * - コメント対象種別とコメント対象IDを組み合わせてコメント対象を特定できることを確認します。
     * - コメント対象ツ種別には、コメントが可能な投稿のモデルをあらわす識別文字列が自動設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント対象
     */
    public function test_commentable_locations()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Location::factory()->count(1))->create();
        $location = $profile->locations->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = $location->comments()->create([
            'body' => 'これはテストコメントです。',
            'commenter_nickname' => 'テストユーザ'
        ]);

        // 評価
        Assert::assertEquals($location->id, $comment->commentable->id, 'コメント対象IDには、コメント対象のIDが設定されること');
        Assert::assertInstanceOf(Location::class, $comment->commentable, 'コメント対象種別とコメント対象IDを組み合わせてコメント対象を特定できること');
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Location::type(),
        ]);
    }

    /**
     * コメント対象
     * 
     * - コメント対象IDには、コメント対象のIDが設定されることを確認します。
     * - コメント対象種別とコメント対象IDを組み合わせてコメント対象を特定できることを確認します。
     * - コメント対象種別には、コメントが可能な投稿のモデルをあらわす識別文字列が自動設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント対象
     */
    public function test_commentable_items()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory()->count(1))->create();
        $item = $profile->items->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = $item->comments()->create([
            'body' => 'これはテストコメントです。',
            'commenter_nickname' => 'テストユーザ'
        ]);

        // 評価
        Assert::assertEquals($item->id, $comment->commentable->id, 'コメント対象IDには、コメント対象のIDが設定されること');
        Assert::assertInstanceOf(Item::class, $comment->commentable, 'コメント対象種別とコメント対象IDを組み合わせてコメント対象を特定できること');
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Item::type(),
        ]);
    }

    /**
     * コメント者プロフィール
     * 
     * - コメントしたユーザのプロフィールであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント者プロフィール
     */
    public function test_commenter()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(99);
        $profile = Profile::factory()->has(Item::factory()->count(1))->create();
        $item = $profile->items->first();

        // コメント者準備
        $user = User::create([
            'name' => 'テストユーザ',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        $commenter = Profile::factory()->create([
            'user_id' => $user->id,
            'nickname' => 'テストユーザ',
        ]);
        Auth::shouldReceive('user')->andReturn($user);

        // 実行
        $comment = $item->comments()->create([
            'body' => 'これはテストコメントです。',
            'commenter' => $commenter,
        ]);

        // 評価
        Assert::assertEquals($commenter->id, $comment->commenter->id, 'コメント者のプロフィールのIDがコメント者プロフィールIDに設定されること');
        $this->assertDatabaseHas('comments', [
            'commenter_profile_id' => $commenter->id,
        ]);
    }

    /**
     * コメント者プロフィール
     * 
     * - コメント者が匿名ユーザの場合は、コメント者プロフィールは設定されないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント者プロフィール
     */
    public function test_commenter_anonymous()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory()->count(1))->create();
        $item = $profile->items->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = $item->comments()->create([
            'body' => 'これはテストコメントです。',
            'commenter_nickname' => 'テストユーザ'
        ]);

        // 評価
        Assert::assertNull($comment->commenter, 'コメント者プロフィールIDは設定されないこと');
    }

    /**
     * コメント者ニックネーム
     * 
     * - コメント作成時に指定がなかった場合は、コメント者プロフィールのニックネームであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント者ニックネーム
     */
    public function test_commenter_nickname_default()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(99);
        $profile = Profile::factory()->has(Item::factory()->count(1))->create();
        $item = $profile->items->first();

        // コメント者準備
        $nickname = 'MyCommenter';
        $user = User::create([
            'name' => 'テストユーザ',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        $commenter = Profile::factory()->create([
            'user_id' => $user->id,
            'nickname' => $nickname,
        ]);
        Auth::shouldReceive('user')->andReturn($user);

        // 実行
        $comment = $item->comments()->create([
            'body' => 'これはテストコメントです。',
            'commenter' => $commenter,
        ]);

        // 評価
        Assert::assertEquals($commenter->nickname, $comment->commenter_nickname, 'コメント者プロフィールのニックネームであること');
        $this->assertDatabaseHas('comments', [
            'commenter_nickname' => null,
        ]);
    }

    /**
     * コメント者ニックネーム
     * 
     * - コメント時に指定したコメント者のニックネームであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント者ニックネーム
     */
    public function test_commenter_nickname()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(99);
        $profile = Profile::factory()->has(Item::factory()->count(1))->create();
        $item = $profile->items->first();

        // コメント者準備
        $user = User::create([
            'name' => 'テストユーザ',
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        Profile::factory()->create([
            'user_id' => $user->id,
            'nickname' => 'テストプロフィール',
        ]);
        Auth::shouldReceive('user')->andReturn($user);
        $nickname = 'MyNickname';

        // 実行
        $comment = $item->comments()->create([
            'body' => 'これはテストコメントです。',
            'commenter_nickname' => $nickname,
        ]);

        // 評価
        Assert::assertEquals($nickname, $comment->commenter_nickname, 'コメント時に指定したコメント者のニックネームであること');
    }

    /**
     * コメント者ニックネーム
     *
     * - コメント者プロフィールまたはコメント者ニックネームのどちらか一方は必ず指定する必要があることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント者ニックネーム
     */
    public function test_commenter_nickname_required()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory()->count(1))->create();
        $item = $profile->items->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $this->assertThrows(function () use ($item) {
            $item->comments()->create([
                'body' => 'これはテストコメントです。',
            ]);
        }, ApplicationException::class, 'CommenterNicknameRequired');
    }

    /**
     * コメント者ニックネーム
     * 
     * - 匿名ユーザ、かつニックネームが指定された場合は、指定したニックネームが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント者ニックネーム
     */
    public function test_nickname_anonymous_specify()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory()->count(1))->create();
        $item = $profile->items->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);
        $nickname = 'MyNickname';

        // 実行
        $comment = $item->comments()->create([
            'body' => 'これはテストコメントです。',
            'commenter_nickname' => $nickname,
        ]);

        // 評価
        Assert::assertEquals($nickname, $comment->commenter_nickname, '指定したニックネームであること');
    }

    /**
     * コメント公開フラグ
     * 
     * - デフォルトは、非公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント公開フラグ
     */
    public function test_is_public_default()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory(['is_public' => true])->count(1))->create();
        $item = $profile->items->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = $item->comments()->create([
            'body' => 'これはテストコメントです。',
            'commenter_nickname' => 'テストユーザ',
        ]);

        // 評価
        $this->assertDatabaseHas('comments', [
            'is_public' => false,
        ]);
        $this->assertFalse($comment->is_public, 'コメント公開フラグは、デフォルトで非公開であること');
    }

    /**
     * コメント公開フラグ
     * 
     * - メント対象の投稿公開フラグが非公開の場合は、コメント公開フラグは常に非公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント公開フラグ
     */
    public function test_is_public_when_commentable_is_private()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory(['is_public' => false])->count(1)->has(Comment::factory(1, ['is_public' => true])))->create();
        $comment = $profile->items->first()->comments->first();

        // 実行
        $is_public = $comment->is_public;

        // 評価
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'is_public' => true,
        ]);
        $this->assertFalse($is_public);
    }

    /**
     * 返信リスト
     * 
     * - 返信リストが取得できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#返信リスト
     */
    public function test_replies()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $count = 5;
        $profile = Profile::factory()->has(Item::factory()->count(1)->has(Comment::factory(1)->has(Reply::factory($count))))->create();
        $comment = $profile->items->first()->comments->first();

        // 実行
        $replies = $comment->replies;

        // 評価
        Assert::assertEquals($count, $replies->count(), '返信リストが取得できること');
    }

    /**
     * コメント作成
     * 
     * - コメントが、投稿毎に付与されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント作成
     */
    public function test_create()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $user = User::create([
            'id' => 99,
            'name' => 'コメント者',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->profiles()->create([
            'nickname' => 'コメント者プロフィール',
            'title' => 'コメント者プロフィールタイトル'
        ]);
        Auth::shouldReceive('user')->andReturn($user);
        Profile::factory(['nickname' => 'feeldee'])->has(Journal::factory(['posted_at' => '2025-07-24'])->count(1))->create();

        // 実行
        $post = Journal::by('feeldee')->at('2025-07-24')->first();
        $comment = $post->comments()->create([
            'commenter' => Auth::user()->profile,
            'body' => 'これはテストコメントです。',
        ]);

        // 評価
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Journal::type(),
            'commentable_id' => $post->id,
            'commenter_profile_id' => Auth::user()->profile->id,
            'commenter_nickname' => null,
            'body' => 'これはテストコメントです。',
        ]);
        $this->assertEquals($post->id, $comment->commentable->id, 'コメントが、投稿毎に付与されること');
        $this->assertInstanceOf(Journal::class, $comment->commentable, 'コメント対象種別とコメント対象IDを組み合わせてコメント対象を特定できること');
    }

    /**
     * コメント作成
     * 
     * - コメント者プロフィールおよびコメント者ニックネームの両方を指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント作成
     */
    public function test_create_with_commenter_and_nickname()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $user = User::create([
            'id' => 99,
            'name' => 'コメント者',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->profiles()->create([
            'nickname' => 'コメント者プロフィール',
            'title' => 'コメント者プロフィールタイトル'
        ]);
        Auth::shouldReceive('user')->andReturn($user);
        Profile::factory(['nickname' => 'feeldee'])->has(Journal::factory(['posted_at' => '2025-07-24'])->count(1))->create();
        $commenter_nickname = 'テストニックネーム';

        // 実行
        $post = Journal::by('feeldee')->at('2025-07-24')->first();
        $comment = $post->comments()->create([
            'commenter' => Auth::user()->profile,
            'commenter_nickname' => $commenter_nickname,
            'body' => 'これはテストコメントです。',
        ]);

        // 評価
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Journal::type(),
            'commentable_id' => $post->id,
            'commenter_profile_id' => Auth::user()->profile->id,
            'commenter_nickname' => $commenter_nickname,
            'body' => 'これはテストコメントです。',
        ]);
        $this->assertEquals($commenter_nickname, $comment->commenter_nickname, 'コメント者プロフィールおよびコメント者ニックネームの両方を指定することもできること');
    }

    /**
     * コメント作成
     * 
     * - 匿名ユーザの場合は、コメント者ニックネームが必須であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント作成
     */
    public function test_create_anonymous_nickname_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'feeldee'])->has(Journal::factory(['posted_at' => '2025-07-24'])->count(1))->create();
        $commenter_nickname = 'テストニックネーム';

        // 実行
        $post = Journal::by('feeldee')->at('2025-07-24')->first();
        $comment = $post->comments()->create([
            'commenter_nickname' => $commenter_nickname,
            'body' => 'これはテストコメントです。',
        ]);

        // 評価
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Journal::type(),
            'commentable_id' => $post->id,
            'commenter_profile_id' => null,
            'commenter_nickname' => $commenter_nickname,
            'body' => 'これはテストコメントです。',
        ]);
        $this->assertEquals($commenter_nickname, $comment->commenter_nickname, '匿名ユーザの場合は、コメント者ニックネームが必須であること');
    }

    /**
     * コメント作成
     * 
     * - コメント日時は、テストなどで任意の日付を指定することも可能であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント作成
     */
    public function test_create_with_commented_at()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'feeldee'])->has(Journal::factory(['posted_at' => '2025-07-24'])->count(1))->create();
        $commenter_nickname = 'テストニックネーム';
        $commented_at = '2025-03-27 09:30:20';

        // 実行
        $post = Journal::by('feeldee')->at('2025-07-24')->first();
        $comment = $post->comments()->create([
            'commenter_nickname' => $commenter_nickname,
            'body' => 'これはテストコメントです。',
            'commented_at' => $commented_at,
        ]);

        // 評価
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Journal::type(),
            'commentable_id' => $post->id,
            'commenter_profile_id' => null,
            'commenter_nickname' => $commenter_nickname,
            'body' => 'これはテストコメントです。',
            'commented_at' => $commented_at,
        ]);
        $this->assertEquals($commented_at, $comment->commented_at, 'テストなどで任意の日付を指定することも可能であること');
    }

    /**
     * コメント公開
     * 
     * - コメントを公開できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント公開
     */
    public function test_doPublic()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory(['is_public' => true])->count(1)->has(Comment::factory(1, ['is_public' => false])))->create();
        $comment = $profile->items->first()->comments->first();

        // 実行
        $comment->doPublic();

        // 評価
        Assert::assertTrue($comment->is_public, 'コメントを公開できること');
    }

    /**
     * コメント公開
     * 
     * - コメントを非公開にできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント公開
     */
    public function test_doPrivate()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory(['is_public' => true])->count(1)->has(Comment::factory(1, ['is_public' => true])))->create();
        $comment = $profile->items->first()->comments->first();

        // 実行
        $comment->doPrivate();

        // 評価
        Assert::assertFalse($comment->is_public, 'コメントを非公開にできること');
    }

    /**
     * コメントリストの並び順
     * 
     * - コメントリストのデフォルトの並び順は、コメント日時の降順（最新順）であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメントリストの並び順
     */
    public function test_comments_order_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'feeldee'])->has(Journal::factory(['posted_at' => '2025-07-24'])->count(1))->create();
        $post = $profile->journals->first();
        $comment1 = $post->comments()->create([
            'body' => '最初のコメント',
            'commenter_nickname' => 'ユーザ1',
            'commented_at' => '2025-07-24 09:00:00'
        ]);
        $comment2 = $post->comments()->create([
            'body' => '次のコメント',
            'commenter_nickname' => 'ユーザ2',
            'commented_at' => '2025-07-24 10:00:00'
        ]);
        $comment3 = $post->comments()->create([
            'body' => '最後のコメント',
            'commenter_nickname' => 'ユーザ3',
            'commented_at' => '2025-07-24 11:00:00'
        ]);

        // 実行
        $post = Journal::by('feeldee')->at('2025-07-24')->first();
        $comments = $post->comments;

        // 評価
        Assert::assertEquals([$comment3->id, $comment2->id, $comment1->id], $comments->pluck('id')->toArray(), 'コメントリストのデフォルトの並び順は、コメント日時の降順（最新順）であること');
    }

    /**
     * コメントリストの並び順
     * 
     * - 古いコメントから順番に並び替えたい場合は、コメント日時昇順でソートできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメントリストの並び順
     */
    public function test_comments_order_oldest()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'feeldee'])->has(Journal::factory(['posted_at' => '2025-07-24'])->count(1))->create();
        $post = $profile->journals->first();
        $comment1 = $post->comments()->create([
            'body' => '最初のコメント',
            'commenter_nickname' => 'ユーザ1',
            'commented_at' => '2025-07-24 09:00:00'
        ]);
        $comment2 = $post->comments()->create([
            'body' => '次のコメント',
            'commenter_nickname' => 'ユーザ2',
            'commented_at' => '2025-07-24 10:00:00'
        ]);
        $comment3 = $post->comments()->create([
            'body' => '最後のコメント',
            'commenter_nickname' => 'ユーザ3',
            'commented_at' => '2025-07-24 11:00:00'
        ]);

        // 実行
        $post = Journal::by('feeldee')->at('2025-07-24')->first();
        $comments = $post->comments()->orderOldest()->get();

        // 評価
        Assert::assertEquals([$comment1->id, $comment2->id, $comment3->id], $comments->pluck('id')->toArray(), '古いコメントから順番に並び替えたい場合は、コメント日時昇順でソートできること');
    }

    /**
     * コメントリストの並び順
     * 
     * - 最新のものから順番に並び替えたい場合は、コメント日時降順でソートできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメントリストの並び順
     */
    public function test_comments_order_latest()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'feeldee'])->has(Journal::factory(['posted_at' => '2025-07-24'])->count(1))->create();
        $post = $profile->journals->first();
        $comment1 = $post->comments()->create([
            'body' => '最初のコメント',
            'commenter_nickname' => 'ユーザ1',
            'commented_at' => '2025-07-24 09:00:00'
        ]);
        $comment2 = $post->comments()->create([
            'body' => '次のコメント',
            'commenter_nickname' => 'ユーザ1',
            'commented_at' => '2025-07-24 10:00:00'
        ]);
        $comment3 = $post->comments()->create([
            'body' => '最後のコメント',
            'commenter_nickname' => 'ユーザ1',
            'commented_at' => '2025-07-24 11:00:00'
        ]);

        // 実行
        $comments = Comment::by('ユーザ1')->orderLatest()->get();

        // 評価
        Assert::assertEquals([$comment3->id, $comment2->id, $comment1->id], $comments->pluck('id')->toArray(), '最新のものから順番に並び替えたい場合は、コメント日時降順でソートできること');
    }

    /**
     * コメントリストの並び順
     * 
     * - 最新(latest|desc)の文字列を直接指定してソートすることができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメントリストの並び順
     */
    public function test_comments_order_direction_latest_or_desc()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'feeldee'])->has(Journal::factory(['posted_at' => '2025-07-24'])->count(1))->create();
        $post = $profile->journals->first();
        $comment1 = $post->comments()->create([
            'body' => '最初のコメント',
            'commenter_nickname' => 'ユーザ1',
            'commented_at' => '2025-07-24 09:00:00'
        ]);
        $comment2 = $post->comments()->create([
            'body' => '次のコメント',
            'commenter_nickname' => 'ユーザ1',
            'commented_at' => '2025-07-24 10:00:00'
        ]);
        $comment3 = $post->comments()->create([
            'body' => '最後のコメント',
            'commenter_nickname' => 'ユーザ1',
            'commented_at' => '2025-07-24 11:00:00'
        ]);

        // 実行
        $commentsLatest = Comment::by('ユーザ1')->orderDirection('latest')->get();
        $commentsDesc = Comment::by('ユーザ1')->orderDirection('desc')->get();

        // 評価
        Assert::assertEquals([$comment3->id, $comment2->id, $comment1->id], $commentsLatest->pluck('id')->toArray(), '最新(latest)の文字列を直接指定してソートすることができること');
        Assert::assertEquals([$comment3->id, $comment2->id, $comment1->id], $commentsDesc->pluck('id')->toArray(), '最新(desc)の文字列を直接指定してソートすることができること');
    }

    /**
     * コメントリストの並び順
     * 
     * - 古いもの(oldest|asc)の文字列を直接指定してソートすることができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメントリストの並び順
     */
    public function test_comments_order_direction_oldest_or_asc()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'feeldee'])->has(Journal::factory(['posted_at' => '2025-07-24'])->count(1))->create();
        $post = $profile->journals->first();
        $comment1 = $post->comments()->create([
            'body' => '最初のコメント',
            'commenter_nickname' => 'ユーザ1',
            'commented_at' => '2025-07-24 09:00:00'
        ]);
        $comment2 = $post->comments()->create([
            'body' => '次のコメント',
            'commenter_nickname' => 'ユーザ1',
            'commented_at' => '2025-07-24 10:00:00'
        ]);
        $comment3 = $post->comments()->create([
            'body' => '最後のコメント',
            'commenter_nickname' => 'ユーザ1',
            'commented_at' => '2025-07-24 11:00:00'
        ]);

        // 実行
        $commentsOldest = Comment::by('ユーザ1')->orderDirection('oldest')->get();
        $commentsAsc = Comment::by('ユーザ1')->orderDirection('asc')->get();

        // 評価
        Assert::assertEquals([$comment1->id, $comment2->id, $comment3->id], $commentsOldest->pluck('id')->toArray(), '古いもの(oldest)の文字列を直接指定してソートすることができること');
        Assert::assertEquals([$comment1->id, $comment2->id, $comment3->id], $commentsAsc->pluck('id')->toArray(), '古いもの(asc)の文字列を直接指定してソートすることができること');
    }

    /**
     * コメント者による絞り込み
     * 
     * - コメント者のニックネームでコメントを絞り込むことができることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント者による絞り込み
     */
    public function test_filter_by()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(['nickname' => 'feeldee'])->has(Journal::factory(['posted_at' => '2025-07-24'])->count(1))->create();
        $journal = $profile->journals->first();
        $journal->comments()->create([
            'body' => 'ユーザ1の最初のコメント',
            'commenter_nickname' => 'ユーザ1',
        ]);
        $journal->comments()->create([
            'body' => 'ユーザ1の次のコメント',
            'commenter_nickname' => 'ユーザ1',
        ]);
        $journal->comments()->create([
            'body' => 'ユーザ2の最後のコメント',
            'commenter_nickname' => 'ユーザ2',
        ]);

        // 実行
        $comments = Comment::by('ユーザ1')->get();

        // 評価
        Assert::assertCount(2, $comments, 'コメント者のニックネームでコメントを絞り込むことができること');
    }

    /**
     * コメント日時による絞り込み
     * 
     * - コメント日時でコメントを絞り込むことができることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時による絞り込み
     */
    public function test_filter_at()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Item::factory()->count(1)->has(
                Comment::factory(3)->sequence(
                    ['body' => 'コメント1', 'commenter_nickname' => 'ユーザ1', 'commented_at' => Carbon::parse('2025-04-22 10:00:00')],
                    ['body' => 'コメント2', 'commenter_nickname' => 'ユーザ2', 'commented_at' => Carbon::parse('2025-04-23 10:00:00')],
                    ['body' => 'コメント3', 'commenter_nickname' => 'ユーザ3', 'commented_at' => Carbon::parse('2025-09-12 09:30:00')],
                )
            )
        )->create();

        // 実行
        $comment = Comment::at('2025-09-12 09:30:00')->first();

        // 評価
        $this->assertEquals('コメント3', $comment->body);
    }

    /**
     * コメント日時による絞り込み
     * 
     * - 時刻の一部を省略した場合には、指定した時刻での前方一致検索となることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時による絞り込み
     */
    public function test_filter_at_partial_time()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(3)->sequence(
                    ['body' => 'コメント1', 'commenter_nickname' => 'ユーザ1', 'commented_at' => Carbon::parse('2025-09-12 09:32:00')],
                    ['body' => 'コメント2', 'commenter_nickname' => 'ユーザ2', 'commented_at' => Carbon::parse('2025-09-12 09:30:00')],
                    ['body' => 'コメント3', 'commenter_nickname' => 'ユーザ3', 'commented_at' => Carbon::parse('2025-09-12 09:30:10')],
                )
            )
        )->create();

        // 実行
        $comments = Comment::at('2025-09-12 09:30')->get();

        // 評価
        $this->assertEquals(2, $comments->count());
    }

    /**
     * コメント日時による絞り込み
     * 
     * - 時刻そのものを省略した場合には、指定した日付での前方一致検索となることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時による絞り込み
     */
    public function test_filter_at_date_only()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(3)->sequence(
                    ['body' => 'コメント1', 'commenter_nickname' => 'ユーザ1', 'commented_at' => Carbon::parse('2025-09-12 09:30:02')],
                    ['body' => 'コメント2', 'commenter_nickname' => 'ユーザ2', 'commented_at' => Carbon::parse('2025-09-12 09:30:01')],
                    ['body' => 'コメント3', 'commenter_nickname' => 'ユーザ3', 'commented_at' => Carbon::parse('2025-09-12 09:30:00')],
                )
            )
        )->create();

        // 実行
        $comments = Comment::at('2025-09-12')->get();

        // 評価
        $this->assertEquals(3, $comments->count());
    }

    /**
     * コメント日時による絞り込み
     * 
     * - コメント日時の範囲を指定して取得できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時による絞り込み
     */
    public function test_filter_between()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(3)->sequence(
                    ['body' => 'コメント1', 'commenter_nickname' => 'ユーザ1', 'commented_at' => Carbon::parse('2025-09-01 09:00:00')],
                    ['body' => 'コメント2', 'commenter_nickname' => 'ユーザ2', 'commented_at' => Carbon::parse('2025-09-12 09:30:01')],
                    ['body' => 'コメント3', 'commenter_nickname' => 'ユーザ3', 'commented_at' => Carbon::parse('2025-09-30 10:00:00')],
                )
            )
        )->create();

        // 実行
        $comments = Comment::between('2025-09-01 09:00:00', '2025-09-30 18:00:00')->get();

        // 評価
        $this->assertEquals(3, $comments->count());
    }

    /**
     * コメント日時による絞り込み
     * 
     * - 範囲指定で時刻の全部を省略した場合には、範囲の開始時刻が00:00:00、終了時刻が23:59:59となるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時による絞り込み
     */
    public function test_filter_between_time_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(3)->sequence(
                    ['body' => 'コメント1', 'commenter_nickname' => 'ユーザ1', 'commented_at' => Carbon::parse('2025-09-01 09:00:00')],
                    ['body' => 'コメント2', 'commenter_nickname' => 'ユーザ2', 'commented_at' => Carbon::parse('2025-09-12 09:30:01')],
                    ['body' => 'コメント3', 'commenter_nickname' => 'ユーザ3', 'commented_at' => Carbon::parse('2025-09-30 10:00:00')],
                )
            )
        )->create();

        // 実行
        $comments = Comment::between('2025-09-01', '2025-09-30')->get();

        // 評価
        $this->assertEquals(3, $comments->count());
    }

    /**
     * コメント日時による絞り込み
     * 
     * - 範囲指定で時刻の一部を省略した場合には、範囲の開始時刻が00:00:00、終了時刻が23:59:59となるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時による絞り込み
     */
    public function test_filter_between_time_partial_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(3)->sequence(
                    ['body' => 'コメント1', 'commenter_nickname' => 'ユーザ1', 'commented_at' => Carbon::parse('2025-09-01 08:59:59')],
                    ['body' => 'コメント2', 'commenter_nickname' => 'ユーザ2', 'commented_at' => Carbon::parse('2025-09-01 09:00:01')],
                    ['body' => 'コメント3', 'commenter_nickname' => 'ユーザ3', 'commented_at' => Carbon::parse('2025-09-01 18:00:01')],
                )
            )
        )->create();

        // 実行
        $comments = Comment::between('2025-09-01 09:00', '2025-09-01 18:00')->get();

        // 評価
        $this->assertEquals(2, $comments->count());
    }

    /**
     * コメント日時による絞り込み
     * 
     * - コメント日時の未満で範囲指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時による絞り込み
     */
    public function test_filter_before()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(3)->sequence(
                    ['body' => 'コメント1', 'commenter_nickname' => 'ユーザ1', 'commented_at' => Carbon::parse('2025-09-01 08:59:59')],
                    ['body' => 'コメント2', 'commenter_nickname' => 'ユーザ2', 'commented_at' => Carbon::parse('2025-09-01 09:00:00')],
                    ['body' => 'コメント3', 'commenter_nickname' => 'ユーザ3', 'commented_at' => Carbon::parse('2025-09-01 09:00:01')],
                )
            )
        )->create();

        // 実行
        $comments = Comment::before('2025-09-01 09:00:00')->get();

        // 評価
        $this->assertEquals(1, $comments->count());
    }

    /**
     * コメント日時による絞り込み
     * 
     * - コメント日時の未満で範囲指定することもできることを確認します。
     * - 時刻の一部または全部を省略した場合には、省略部分が常に00:00:00と同じなるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時による絞り込み
     */
    public function test_filter_before_partial_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(3)->sequence(
                    ['body' => 'コメント1', 'commenter_nickname' => 'ユーザ1', 'commented_at' => Carbon::parse('2025-08-22 10:00:00')],
                    ['body' => 'コメント2', 'commenter_nickname' => 'ユーザ2', 'commented_at' => Carbon::parse('2025-09-01 09:29:59')],
                    ['body' => 'コメント3', 'commenter_nickname' => 'ユーザ3', 'commented_at' => Carbon::parse('2025-09-01 09:30:00')],
                )
            )
        )->create();

        // 実行
        $comments = Comment::before('2025-09-01 09:30')->get();

        // 評価
        $this->assertEquals(2, $comments->count());
    }

    /**
     * コメント日時による絞り込み
     * 
     * - コメント日時のより先で範囲指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時による絞り込み
     */
    public function test_filter_after()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(3)->sequence(
                    ['body' => 'コメント1', 'commenter_nickname' => 'ユーザ1', 'commented_at' => Carbon::parse('2025-08-22 10:00:00')],
                    ['body' => 'コメント2', 'commenter_nickname' => 'ユーザ2', 'commented_at' => Carbon::parse('2025-09-01 09:30:00')],
                    ['body' => 'コメント3', 'commenter_nickname' => 'ユーザ3', 'commented_at' => Carbon::parse('2025-09-01 09:30:01')],
                )
            )
        )->create();

        // 実行
        $comments = Comment::after('2025-09-01 09:30:00')->get();

        // 評価
        $this->assertEquals(1, $comments->count());
    }

    /**
     * コメント日時による絞り込み
     * 
     * - コメント日時のより先で範囲指定することもできることを確認します。
     * - 時刻の一部または全部を省略した場合には、省略部分が常に00:00:00と同じなるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時による絞り込み
     */
    public function test_filter_after_partial_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(3)->sequence(
                    ['body' => 'コメント1', 'commenter_nickname' => 'ユーザ1', 'commented_at' => Carbon::parse('2025-08-22 10:00:00')],
                    ['body' => 'コメント2', 'commenter_nickname' => 'ユーザ2', 'commented_at' => Carbon::parse('2025-09-01 09:30:00')],
                    ['body' => 'コメント3', 'commenter_nickname' => 'ユーザ3', 'commented_at' => Carbon::parse('2025-09-01 09:30:01')],
                )
            )
        )->create();

        // 実行
        $comments = Comment::after('2025-09-01 09:30')->get();

        // 評価
        $this->assertEquals(1, $comments->count());
    }

    /**
     * コメント日時による絞り込み
     * 
     * - コメント日時の以前で範囲指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時による絞り込み
     */
    public function test_filter_beforeEquals()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(3)->sequence(
                    ['body' => 'コメント1', 'commenter_nickname' => 'ユーザ1', 'commented_at' => Carbon::parse('2025-08-22 10:00:00')],
                    ['body' => 'コメント2', 'commenter_nickname' => 'ユーザ2', 'commented_at' => Carbon::parse('2025-09-01 09:30:00')],
                    ['body' => 'コメント3', 'commenter_nickname' => 'ユーザ3', 'commented_at' => Carbon::parse('2025-09-01 09:30:01')],
                )
            )
        )->create();

        // 実行
        $comments = Comment::beforeEquals('2025-09-01 09:30:00')->get();

        // 評価
        $this->assertEquals(2, $comments->count());
    }

    /**
     * コメント日時による絞り込み
     * 
     * - コメント日時の以前で範囲指定することもできることを確認します。
     * - 時刻の一部または全部を省略した場合には、省略部分が常に00:00:00と同じなるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時による絞り込み
     */
    public function test_filter_beforeEquals_partial_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(3)->sequence(
                    ['body' => 'コメント1', 'commenter_nickname' => 'ユーザ1', 'commented_at' => Carbon::parse('2025-08-22 10:00:00')],
                    ['body' => 'コメント2', 'commenter_nickname' => 'ユーザ2', 'commented_at' => Carbon::parse('2025-09-01 09:30:00')],
                    ['body' => 'コメント3', 'commenter_nickname' => 'ユーザ3', 'commented_at' => Carbon::parse('2025-09-01 09:30:01')],
                )
            )
        )->create();

        // 実行
        $comments = Comment::beforeEquals('2025-09-01 09:30')->get();

        // 評価
        $this->assertEquals(2, $comments->count());
    }

    /**
     * コメント日時による絞り込み
     * 
     * - コメント日時の以降で範囲指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時による絞り込み
     */
    public function test_filter_afterEquals()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(3)->sequence(
                    ['body' => 'コメント1', 'commenter_nickname' => 'ユーザ1', 'commented_at' => Carbon::parse('2025-08-22 10:00:00')],
                    ['body' => 'コメント2', 'commenter_nickname' => 'ユーザ2', 'commented_at' => Carbon::parse('2025-09-01 09:30:00')],
                    ['body' => 'コメント3', 'commenter_nickname' => 'ユーザ3', 'commented_at' => Carbon::parse('2025-09-01 09:30:01')],
                )
            )
        )->create();

        // 実行
        $comments = Comment::afterEquals('2025-09-01 09:30:00')->get();

        // 評価
        $this->assertEquals(2, $comments->count());
    }

    /**
     * コメント日時による絞り込み
     * 
     * - コメント日時の以降で範囲指定することもできることを確認します。
     * - 時刻の一部または全部を省略した場合には、省略部分が常に00:00:00と同じなるに不足部分が補われることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時による絞り込み
     */
    public function test_filter_afterEquals_partial_omitted()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory()->count(1)->has(
                Comment::factory(3)->sequence(
                    ['body' => 'コメント1', 'commenter_nickname' => 'ユーザ1', 'commented_at' => Carbon::parse('2025-08-22 10:00:00')],
                    ['body' => 'コメント2', 'commenter_nickname' => 'ユーザ2', 'commented_at' => Carbon::parse('2025-09-01 09:30:00')],
                    ['body' => 'コメント3', 'commenter_nickname' => 'ユーザ3', 'commented_at' => Carbon::parse('2025-09-01 09:30:01')],
                )
            )
        )->create();

        // 実行
        $comments = Comment::afterEquals('2025-09-01 09:30')->get();

        // 評価
        $this->assertEquals(2, $comments->count());
    }

    /**
     * 公開・非公開による絞り込み
     * 
     * - 公開されたコメントのみ取得する場合は、publicローカルスコープを利用できることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#公開・非公開による絞り込み
     */
    public function test_filter_public()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Item::factory(['is_public' => true])->count(1)->has(
                Comment::factory(3)->sequence(
                    ['commenter_nickname' => 'Feeldee', 'is_public' => true],
                    ['commenter_nickname' => 'Feeldee', 'is_public' => false],
                    ['commenter_nickname' => 'Feeldee', 'is_public' => true],
                )
            )
        )->create();

        // 実行
        $comments = Comment::by('Feeldee')->public()->get();

        // 評価
        Assert::assertCount(2, $comments);
    }

    /**
     * 公開・非公開による絞り込み
     * 
     * - 非公開のコメントのみを取得する場合は、privateローカルスコープを利用できることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#公開・非公開による絞り込み
     */
    public function test_filter_private()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory(['posted_at' => Carbon::parse('2025-09-01'), 'is_public' => true])->count(1)->has(
                Comment::factory(3)->sequence(
                    ['is_public' => true],
                    ['is_public' => false],
                    ['is_public' => true],
                )
            )
        )->create();

        // 実行
        $comments = Journal::at('2025-09-01')->first()->comments()->private()->get();

        // 評価
        Assert::assertCount(1, $comments);
    }

    /**
     * 公開・非公開による絞り込み
     * 
     * - コメントの公開・非公開は、常にコメント対象の投稿公開フラグとのAND条件となることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#公開・非公開による絞り込み
     */
    public function test_filter_public_and_post_is_public()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(
            Journal::factory(['is_public' => false])->count(1)->has(
                Comment::factory(3)->sequence(
                    ['is_public' => true],
                    ['is_public' => false],
                    ['is_public' => true],
                )
            )
        )->create();

        // 実行
        $comments = Comment::public()->get();

        // 評価
        Assert::assertCount(0, $comments);
    }
}
