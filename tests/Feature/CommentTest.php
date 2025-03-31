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
use Illuminate\Testing\Assert;

/**
 * コメントの用語を担保するための機能テストです。
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
     * コメント日時
     * 
     * - 任意の日時を指定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時
     */
    public function test_commented_at_specify()
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
     * コメント日時
     * 
     * - コメント日時が指定されなかった場合はシステム日時が自動で設定されれることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時
     */
    public function test_commented_at_default()
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
     * コメント本文
     * 
     * - テキストが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント本文
     */
    public function test_body_text()
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
     * コメント本文
     * 
     * - HTMLが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント本文
     */
    public function test_body_html()
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
        $this->assertDatabaseHas('comments', [
            'commentable_type' => Item::type(),
        ]);
    }

    /**
     * コメント者
     * 
     * - コメント者がログインユーザの場合は、コメント者プロフィールIDには、コメント者のプロフィールIDが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント対象
     */
    public function test_commenter_logged_in_user()
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
        ], $item);

        // 評価
        Assert::assertEquals($commenter->id, $comment->commenter->id, 'コメント者のプロフィールのIDがコメント者プロフィールIDに設定されること');
        $this->assertDatabaseHas('comments', [
            'commenter_profile_id' => $commenter->id,
        ]);
    }

    /**
     * コメント者
     * 
     * - コメント者が匿名ユーザの場合は、コメント者プロフィールIDは設定されないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント対象
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
        $comment = Comment::create([
            'body' => 'これはテストコメントです。',
            'nickname' => 'テストユーザ'
        ], $item);

        // 評価
        Assert::assertNull($comment->commenter, 'コメント者プロフィールIDは設定されないこと');
    }

    /**
     * コメント者ニックネーム
     * 
     * - ログインユーザ、かつコメント者ニックネームが指定されなかった場合は、コメント者のプロフィールのニックネームであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント者ニックネーム
     */
    public function test_nickname_logged_in_user_default()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory()->count(1))->create();
        $item = $profile->items->first();

        // コメント者準備
        $nickname = 'MyCommenter';
        Auth::shouldReceive('id')->andReturn(2);
        $commenter = Profile::factory()->create([
            'nickname' => $nickname,
        ]);
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($commenter);
        Auth::shouldReceive('user')->andReturn($user);

        // 実行
        $comment = Comment::create([
            'body' => 'これはテストコメントです。',
        ], $item);

        // 評価
        Assert::assertEquals($commenter->nickname, $comment->nickname, 'コメント者のプロフィールのニックネームであること');
        $this->assertDatabaseHas('comments', [
            'commenter_nickname' => null,
        ]);
    }

    /**
     * コメント者ニックネーム
     * 
     * - ログインユーザ、かつコメント者ニックネームが指定された場合は、指定したニックネームが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント者ニックネーム
     */
    public function test_nickname_logged_in_user_specify()
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
        $nickname = 'MyNickname';

        // 実行
        $comment = Comment::create([
            'body' => 'これはテストコメントです。',
            'nickname' => $nickname,
        ], $item);

        // 評価
        Assert::assertEquals($nickname, $comment->nickname, '指定したニックネームであること');
    }

    /**
     * コメント者ニックネーム
     * 
     * - 匿名ユーザは、ニックネームが必須であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント者ニックネーム
     */
    public function test_nickname_anonymous_required()
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
            Comment::create([
                'body' => 'これはテストコメントです。',
            ], $item);
        }, \Illuminate\Validation\ValidationException::class);
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
        $comment = Comment::create([
            'body' => 'これはテストコメントです。',
            'nickname' => $nickname,
        ], $item);

        // 評価
        Assert::assertEquals($nickname, $comment->nickname, '指定したニックネームであること');
    }

    /**
     * コメント公開フラグ
     * 
     * - コメント公開フラグが指定されなかった場合は、非公開であることを確認します。
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
        $comment = Comment::create([
            'body' => 'これはテストコメントです。',
            'nickname' => 'テストユーザ',
            'is_public' => true
        ], $item);
        $comment->refresh();

        // 評価
        Assert::assertFalse($comment->isPublic, 'デフォルトは、非公開であること');
    }

    /**
     * コメント公開フラグ
     * 
     * - doPublic()メソッドを実行すると、コメントが公開されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント公開フラグ
     */
    public function test_is_public_doPublic()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory()->count(1)->has(Comment::factory(1, ['is_public' => false])))->create();
        $comment = $profile->items->first()->comments->first();

        // 実行
        $comment->doPublic();

        // 評価
        Assert::assertTrue($comment->isPublic, '公開であること');
    }

    /**
     * コメント公開フラグ
     * 
     * - doPrivate()メソッドを実行すると、コメントが非公開になることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント公開フラグ
     */
    public function test_is_public_doPrivate()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Item::factory()->count(1)->has(Comment::factory(1, ['is_public' => true])))->create();
        $comment = $profile->items->first()->comments->first();

        // 実行
        $comment->doPrivate();

        // 評価
        Assert::assertFalse($comment->isPublic, '非公開であること');
    }
}
