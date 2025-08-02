<?php

namespace Tests\Feature;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Comment;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\Post;
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
     * - コメントされたコンテンツ（以降、コメント対象）に紐付くプロフィールが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント所有者
     */
    public function test_profile()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory()->count(1))->create();
        $post = $profile->posts->first();

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
        $profile = Profile::factory()->has(Post::factory()->count(1))->create();
        $post = $profile->posts->first();

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
        $profile = Profile::factory()->has(Post::factory()->count(1))->create();
        $post = $profile->posts->first();

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
        $profile = Profile::factory()->has(Post::factory()->count(1))->create();
        $post = $profile->posts->first();

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
        $profile = Profile::factory()->has(Post::factory()->count(1))->create();
        $post = $profile->posts->first();

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
     * - コメント対象コンテンツIDには、コメント対象のコンテンツのIDが設定されることを確認します。
     * - コメント対象コンテンツ種別とコメント対象コンテンツIDを組み合わせてコメント対象を特定できることを確認します。
     * - コメント対象コンテンツ種別には、コメントが可能な投稿のモデルをあらわす識別文字列が自動設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント対象
     */
    public function test_commentable_posts()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Post::factory()->count(1))->create();
        $post = $profile->posts->first();

        // コメント者準備
        Auth::shouldReceive('id')->andReturnNull();
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $comment = $post->comments()->create([
            'body' => 'これはテストコメントです。',
            'commenter_nickname' => 'テストユーザ'
        ]);

        // 評価
        Assert::assertEquals($post->id, $comment->commentable->id, 'コメント対象コンテンツIDには、コメント対象のコンテンツのIDが設定されること');
        Assert::assertInstanceOf(Post::class, $comment->commentable, 'コメント対象コンテンツ種別とコメント対象コンテンツIDを組み合わせてコメント対象を特定できること');
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Post::type(),
        ]);
    }

    /**
     * コメント対象
     * 
     * - コメント対象コンテンツIDには、コメント対象のコンテンツのIDが設定されることを確認します。
     * - コメント対象コンテンツ種別とコメント対象コンテンツIDを組み合わせてコメント対象を特定できることを確認します。
     * - コメント対象コンテンツ種別には、コメントが可能な投稿のモデルをあらわす識別文字列が自動設定されることを確認します。
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
        Assert::assertEquals($photo->id, $comment->commentable->id, 'コメント対象コンテンツIDには、コメント対象のコンテンツのIDが設定されること');
        Assert::assertInstanceOf(Photo::class, $comment->commentable, 'コメント対象コンテンツ種別とコメント対象コンテンツIDを組み合わせてコメント対象を特定できること');
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Photo::type()
        ]);
    }

    /**
     * コメント対象
     * 
     * - コメント対象コンテンツIDには、コメント対象のコンテンツのIDが設定されることを確認します。
     * - コメント対象コンテンツ種別とコメント対象コンテンツIDを組み合わせてコメント対象を特定できることを確認します。
     * - コメント対象コンテンツ種別には、コメントが可能な投稿のモデルをあらわす識別文字列が自動設定されることを確認します。
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
        Assert::assertEquals($location->id, $comment->commentable->id, 'コメント対象コンテンツIDには、コメント対象のコンテンツのIDが設定されること');
        Assert::assertInstanceOf(Location::class, $comment->commentable, 'コメント対象コンテンツ種別とコメント対象コンテンツIDを組み合わせてコメント対象を特定できること');
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Location::type(),
        ]);
    }

    /**
     * コメント対象
     * 
     * - コメント対象コンテンツIDには、コメント対象のコンテンツのIDが設定されることを確認します。
     * - コメント対象コンテンツ種別とコメント対象コンテンツIDを組み合わせてコメント対象を特定できることを確認します。
     * - コメント対象コンテンツ種別には、コメントが可能な投稿のモデルをあらわす識別文字列が自動設定されることを確認します。
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
        Assert::assertEquals($item->id, $comment->commentable->id, 'コメント対象コンテンツIDには、コメント対象のコンテンツのIDが設定されること');
        Assert::assertInstanceOf(Item::class, $comment->commentable, 'コメント対象コンテンツ種別とコメント対象コンテンツIDを組み合わせてコメント対象を特定できること');
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
        $profile = Profile::factory()->has(Item::factory()->count(1))->create();
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
     * - コメントが、コンテンツ毎に付与されることを確認します。
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
        Profile::factory(['nickname' => 'feeldee'])->has(Post::factory(['post_date' => '2025-07-24'])->count(1))->create();

        // 実行
        $post = Post::by('feeldee')->at('2025-07-24')->first();
        $comment = $post->comments()->create([
            'commenter' => Auth::user()->profile,
            'body' => 'これはテストコメントです。',
        ]);

        // 評価
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Post::type(),
            'commentable_id' => $post->id,
            'commenter_profile_id' => Auth::user()->profile->id,
            'commenter_nickname' => null,
            'body' => 'これはテストコメントです。',
        ]);
        $this->assertEquals($post->id, $comment->commentable->id, 'コメントが、コンテンツ毎に付与されること');
        $this->assertInstanceOf(Post::class, $comment->commentable, 'コメント対象コンテンツ種別とコメント対象コンテンツIDを組み合わせてコメント対象を特定できること');
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
        Profile::factory(['nickname' => 'feeldee'])->has(Post::factory(['post_date' => '2025-07-24'])->count(1))->create();
        $commenter_nickname = 'テストニックネーム';

        // 実行
        $post = Post::by('feeldee')->at('2025-07-24')->first();
        $comment = $post->comments()->create([
            'commenter' => Auth::user()->profile,
            'commenter_nickname' => $commenter_nickname,
            'body' => 'これはテストコメントです。',
        ]);

        // 評価
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Post::type(),
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
        Profile::factory(['nickname' => 'feeldee'])->has(Post::factory(['post_date' => '2025-07-24'])->count(1))->create();
        $commenter_nickname = 'テストニックネーム';

        // 実行
        $post = Post::by('feeldee')->at('2025-07-24')->first();
        $comment = $post->comments()->create([
            'commenter_nickname' => $commenter_nickname,
            'body' => 'これはテストコメントです。',
        ]);

        // 評価
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Post::type(),
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
        Profile::factory(['nickname' => 'feeldee'])->has(Post::factory(['post_date' => '2025-07-24'])->count(1))->create();
        $commenter_nickname = 'テストニックネーム';
        $commented_at = '2025-03-27 09:30:20';

        // 実行
        $post = Post::by('feeldee')->at('2025-07-24')->first();
        $comment = $post->comments()->create([
            'commenter_nickname' => $commenter_nickname,
            'body' => 'これはテストコメントです。',
            'commented_at' => $commented_at,
        ]);

        // 評価
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Post::type(),
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
        $profile = Profile::factory()->has(Item::factory()->count(1)->has(Comment::factory(1, ['is_public' => false])))->create();
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
        $profile = Profile::factory()->has(Item::factory()->count(1)->has(Comment::factory(1, ['is_public' => true])))->create();
        $comment = $profile->items->first()->comments->first();

        // 実行
        $comment->doPrivate();

        // 評価
        Assert::assertFalse($comment->is_public, 'コメントを非公開にできること');
    }
}
