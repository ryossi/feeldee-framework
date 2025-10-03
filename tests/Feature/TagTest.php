<?php

namespace Tests\Feature;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Journal;
use Feeldee\Framework\Models\Like;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /**
     * タグ所有プロフィール
     * 
     * - タグを作成したユーザのプロフィールであることを確認します。
     * - プロフィールのIDが、タグ所有プロフィールIDに設定されていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ所有プロフィール
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag = $profile->tags()->create([
            'name' => 'テストタグ',
            'type' => Journal::type(),
        ]);

        // 評価
        $this->assertEquals($profile->id, $tag->profile->id, 'タグを作成したユーザのプロフィールであること');
        // プロフィールのIDが、タグ所有プロフィールIDに設定されていること
        $this->assertDatabaseHas('tags', [
            'profile_id' => $profile->id,
        ]);
    }

    /** 
     * タグ所有プロフィール
     * 
     * - タグ所有プロフィールは必須項目であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ所有プロフィール
     */
    public function test_profile_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);

        // 実行
        $this->assertThrows(function () {
            Tag::create([
                'name' => 'テストタグ',
                'type' => Journal::type(),
            ]);
        }, ApplicationException::class, 'TagProfileRequired');
    }

    /**
     * タグタイプ
     * 
     * - タグタイプは必須項目であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグタイプ
     */
    public function test_type_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->tags()->create([
                'name' => 'テストタグ',
            ]);
        }, ApplicationException::class, 'TagTypeRequired');
    }

    /**
     * タグタイプ
     * 
     * - 投稿のタグは、投稿のタグタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグタイプ
     */
    public function test_type_post()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag = $profile->tags()->create([
            'name' => 'テストタグ',
            'type' => Journal::type(),
        ]);

        // 評価
        $this->assertEquals(Journal::type(), $tag->type, '投稿のタグタイプであること');
        $this->assertDatabaseHas('tags', [
            'type' => Journal::type(),
        ]);
    }

    /**
     * タグタイプ
     * 
     * - 写真のタグは、写真のタグタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグタイプ
     */
    public function test_type_photo()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag = $profile->tags()->create([
            'name' => 'テストタグ',
            'type' => Photo::type(),
        ]);

        // 評価
        $this->assertEquals(Photo::type(), $tag->type, '写真のタグタイプであること');
        $this->assertDatabaseHas('tags', [
            'type' => Photo::type(),
        ]);
    }

    /**
     * タグタイプ
     * 
     * - 場所のタグは、場所のタグタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグタイプ
     */
    public function test_type_location()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag = $profile->tags()->create([
            'name' => 'テストタグ',
            'type' => Location::type(),
        ]);

        // 評価
        $this->assertEquals(Location::type(), $tag->type, '場所のタグタイプであること');
        $this->assertDatabaseHas('tags', [
            'type' => Location::type(),
        ]);
    }

    /**
     * タグタイプ
     * 
     * - アイテムのタグは、アイテムのタグタイプであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグタイプ
     */
    public function test_type_item()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag = $profile->tags()->create([
            'name' => 'テストタグ',
            'type' => Item::type(),
        ]);

        // 評価
        $this->assertEquals(Item::type(), $tag->type, 'アイテムのタグタイプであること');
        $this->assertDatabaseHas('tags', [
            'type' => Item::type(),
        ]);
    }

    /**
     * タグ名
     * 
     * - タグの名前であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ名
     */
    public function test_name()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag = $profile->tags()->create([
            'name' => 'テストタグ',
            'type' => Journal::type(),
        ]);

        // 評価
        $this->assertEquals('テストタグ', $tag->name, 'タグの名前であること');
        $this->assertDatabaseHas('tags', [
            'name' => 'テストタグ',
        ]);
    }

    /**
     * タグ名
     * 
     * - タグ名は必須項目であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ名
     */
    public function test_name_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->tags()->create([
                'type' => Journal::type(),
            ]);
        }, ApplicationException::class, 'TagNameRequired');
    }

    /**
     * タグ名
     * 
     * - タグ所有プロフィールとタグタイプの中でユニークであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ名
     */
    public function test_name_unique()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Tag::factory(1, ['name' => 'テストタグ', 'type' => Journal::type()]))->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->tags()->create([
                'name' => 'テストタグ',
                'type' => Journal::type(),
            ]);
        }, ApplicationException::class, 'TagNameDuplicated');
    }

    /**
     * タグ名
     * 
     * - タグ所有プロフィールとタグタイプの中でユニークであることを確認します。
     * - タグタイプが異なる場合は、登録できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ名
     */
    public function test_name_unique_with_different_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Tag::factory(1, ['name' => 'テストタグ', 'type' => Journal::type()]))->create();

        // 実行
        $tag = $profile->categories()->create([
            'name' => 'テストタグ',
            'type' => Item::type(),
        ]);

        // 評価
        $this->assertEquals('テストタグ', $tag->name, 'タグタイプが異なる場合は、登録できること');
        $this->assertDatabaseHas('categories', [
            'name' => 'テストタグ',
            'type' => Item::type(),
        ]);
    }

    /**
     * タグ名
     * 
     * - タグ所有プロフィールとタグタイプの中でユニークであることを確認します。
     * - タグ所有プロフィールが異なる場合は、登録できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ名
     */
    public function test_name_unique_with_different_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory()->has(Tag::factory(1, ['name' => 'テストタグ', 'type' => Journal::type()]))->create();
        $otherProfile = Profile::factory()->create();

        // 実行
        $tag = $otherProfile->categories()->create([
            'name' => 'テストタグ',
            'type' => Journal::type(),
        ]);

        // 評価
        $this->assertEquals('テストタグ', $tag->name, 'タグ所有プロフィールが異なる場合は、登録できること');
        $this->assertDatabaseHas('categories', [
            'name' => 'テストタグ',
            'type' => Journal::type(),
            'profile_id' => $otherProfile->id,
        ]);
    }

    /**
     * タグイメージ
     * 
     * - タグのイメージ画像であることを確認します。
     * - URL形式で保存できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグイメージ
     */
    public function test_image_url()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $image = '/path/to/image.jpg';

        // 実行
        $tag = $profile->tags()->create([
            'name' => 'テストタグ',
            'type' => Journal::type(),
            'image' => $image,
        ]);

        // 検証
        $this->assertEquals($image, $tag->image, 'タグのイメージ画像であること');
        // URL形式で保存できること
        $this->assertDatabaseHas('tags', [
            'image' => $image,
        ]);
    }

    /**
     * タグイメージ
     * 
     * - タグのイメージ画像であることを確認します。
     * - Base64形式で保存できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグイメージ
     */
    public function test_image_base64()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $image = 'data:image/jpeg;base64,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';

        // 実行
        $tag = $profile->tags()->create([
            'name' => 'テストタグ',
            'type' => Journal::type(),
            'image' => $image,
        ]);

        // 検証
        $this->assertEquals($image, $tag->image, 'タグのイメージ画像であること');
        // Base64形式で保存できること
        $this->assertDatabaseHas('tags', [
            'image' => $image,
        ]);
    }

    /**
     * タグ表示順
     * 
     * - 同じタグ所有プロフィール、タグタイプでタグの表示順を決定するための番号であることを確認します。
     * - 作成時に自動採番されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ表示順
     */
    public function test_order_number()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Journal::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Journal::type(),
        ]);
        $tag3 = $profile->tags()->create([
            'name' => 'タグ3',
            'type' => Journal::type(),
        ]);

        // 評価
        $this->assertEquals(1, $tag1->order_number, '同じタグ所有プロフィール、タグタイプの中での表示順を持つこと');
        $this->assertEquals(2, $tag2->order_number, '同じタグ所有プロフィール、タグタイプの中での表示順を持つこと');
        $this->assertEquals(3, $tag3->order_number, '同じタグ所有プロフィール、タグタイプの中での表示順を持つこと');
        // タグ表示順は、作成時に自動採番されること
        foreach ($profile->tags as $tag) {
            $this->assertDatabaseHas('tags', [
                'id' => $tag->id,
                'order_number' => $tag->order_number,
            ]);
        }
    }

    /**
     * タグ表示順
     * 
     * - 表示順で前のタグに容易にアクセスすることができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ表示順
     */
    public function test_order_number_previous()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Journal::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Journal::type(),
        ]);

        // 実行
        $previousTag = $tag2->previous();

        // 評価
        $this->assertEquals($tag1->id, $previousTag->id, '表示順で前のタグに容易にアクセスすることができること');
    }

    /**
     * タグ表示順
     * 
     * - 表示順で後のタグに容易にアクセスすることができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ表示順
     */
    public function test_order_number_next()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Journal::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Journal::type(),
        ]);

        // 実行
        $nextTag = $tag1->next();

        // 評価
        $this->assertEquals($tag2->id, $nextTag->id, '表示順で後のタグに容易にアクセスすることができること');
    }

    /**
     * タグ表示順
     * 
     * - 直接編集しなくても同じタグ所有プロフィール、タグタイプの中で表示順を上へ移動することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ表示順
     */
    public function test_order_number_up()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Journal::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Journal::type(),
        ]);

        // 実行
        $tag2->orderUp();

        // 評価
        $this->assertEquals(1, $tag2->order_number, '同じタグ所有プロフィール、タグタイプの中で表示順を上へ移動することができること');
        $this->assertDatabaseHas('tags', [
            'id' => $tag2->id,
            'order_number' => 1,
        ]);
        $tag1->refresh();
        $this->assertEquals(2, $tag1->order_number, '同じタグ所有プロフィール、タグタイプの中で表示順を上へ移動することができること');
        $this->assertDatabaseHas('tags', [
            'id' => $tag1->id,
            'order_number' => 2,
        ]);
    }

    /**
     * タグ表示順
     * 
     * - 直接編集しなくても同じタグ所有プロフィール、タグタイプの中で表示順を下へ移動することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ表示順
     */
    public function test_order_number_down()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Journal::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Journal::type(),
        ]);

        // 実行
        $tag1->orderDown();

        // 評価
        $this->assertEquals(2, $tag1->order_number, '同じタグ所有プロフィール、タグタイプの中で表示順を下へ移動することができること');
        $this->assertDatabaseHas('tags', [
            'id' => $tag1->id,
            'order_number' => 2,
        ]);
        $tag2->refresh();
        $this->assertEquals(1, $tag2->order_number, '同じタグ所有プロフィール、タグタイプの中で表示順を下へ移動することができること');
        $this->assertDatabaseHas('tags', [
            'id' => $tag2->id,
            'order_number' => 1,
        ]);
    }

    /**
     * 投稿リスト
     * 
     * - タグ付けされている投稿のコレクションであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#投稿リスト
     */
    public function test_posts()
    {
        //  準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $tag = $profile->tags()->create([
            'name' => 'テストタグ',
            'type' => Journal::type(),
            'posts' => Journal::factory(3)->create(['profile_id' => $profile->id]),
        ]);

        // 評価
        $this->assertEquals(3, $tag->posts->count());
        foreach ($tag->posts as $post) {
            $this->assertEquals($post->tags()->first()->id, $tag->id, 'タグ付けされている投稿のリストであること');
        }
    }

    /**
     * 投稿リスト
     * 
     * - タグ付けされている投稿を削除すると、投稿リストからも自動的に除外されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#投稿リスト
     */
    public function test_posts_delete()
    {
        //  準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $post1 = Journal::factory()->create(['profile_id' => $profile->id]);
        $post2 = Journal::factory()->create(['profile_id' => $profile->id]);
        $post3 = Journal::factory()->create(['profile_id' => $profile->id]);
        $tag = $profile->tags()->create([
            'name' => 'テストタグ',
            'type' => Journal::type(),
            'posts' => [$post1, $post2, $post3],
        ]);

        // 実行
        $post2->delete();

        // 評価
        $this->assertEquals(2, $tag->posts->count(), 'タグ付けされている投稿を削除すると、投稿リストからも自動的に除外されること');
        foreach ($tag->posts as $post) {
            $this->assertEquals($post->tags()->first()->id, $tag->id);
        }
    }

    /**
     * 投稿リスト
     * 
     * - 投稿リストに直接投稿のコレクションを指定する場合、タグ所有プロフィールが投稿者プロフィールと一致している必要があることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#投稿リスト
     */
    public function test_posts_profile_missmatch()
    {
        //  準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $otherProfile = Profile::factory()->create();

        // 評価
        $this->assertThrows(function () use ($profile, $otherProfile) {
            // 実行
            $profile->tags()->create([
                'name' => 'テストタグ',
                'type' => Journal::type(),
                'posts' => Journal::factory(3)->create(['profile_id' => $otherProfile->id]),
            ]);
        }, ApplicationException::class, 'TagProfileMissmatch');
    }

    /**
     * 投稿リスト
     * 
     * - 投稿リストに直接投稿のコレクションを指定する場合、タグタイプが投稿種別と一致している必要があることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#投稿リスト
     */
    public function test_posts_type_missmatch()
    {
        //  準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 評価
        $this->assertThrows(function () use ($profile) {
            // 実行
            $profile->tags()->create([
                'name' => 'テストタグ',
                'type' => Journal::type(),
                'posts' => Item::factory(1)->create(['profile_id' => $profile->id]),
            ]);
        }, ApplicationException::class, 'TagTypeMissmatch');
    }

    /**
     * タグ所有者による絞り込み
     * 
     * - タグ所有者によりタグを絞り込むことができることを確認します。
     * - プロフィールそのもので絞り込むことができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ所有者による絞り込み
     */
    public function test_scopeBy()
    {
        //  準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(Tag::factory()->count(2))->create();
        Profile::factory(['nickname' => 'Other'])->has(Tag::factory()->count(1))->create();

        // 実行
        $tags = Tag::by(Profile::of('Feeldee')->first())->get();

        // 評価
        $this->assertCount(2, $tags);
    }

    /**
     * タグ所有者による絞り込み
     * 
     * - タグ所有者によりタグを絞り込むことができることを確認します。
     * - ニックネームで絞り込むことができることを確認します。
     */
    public function test_scopeBy_nickname()
    {
        //  準備
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(Tag::factory()->count(3))->create();
        Profile::factory(['nickname' => 'Other'])->has(Tag::factory()->count(5))->create();

        // 実行
        $tags = Tag::by('Feeldee')->get();

        // 評価
        $this->assertCount(3, $tags);
    }

    /**
     * タグタイプによる絞り込み
     * 
     * - タグタイプによりタグを絞り込むことができることを確認します。
     * - 投稿の抽象クラスを継承した具象クラスを指定して絞り込むことができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグタイプによる絞り込み
     */
    public function test_scopeOf()
    {
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(Tag::factory()->count(7)->sequence(
            ['type' => Journal::type()],
            ['type' => Photo::type()],
            ['type' => Photo::type()],
            ['type' => Location::type()],
            ['type' => Location::type()],
            ['type' => Location::type()],
            ['type' => Item::type()],
        ))->create();

        // 実行
        $locationTags = Tag::by('Feeldee')->of(Location::class)->get();

        // 評価
        $this->assertCount(3, $locationTags);
    }

    /**
     * タグタイプによる絞り込み
     * 
     * - タグタイプによりタグを絞り込むことができることを確認します。
     * - タグタイプの文字列を指定して絞り込むことができることを確認します。
     */
    public function test_scopeOf_string()
    {
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(Tag::factory()->count(7)->sequence(
            ['type' => Journal::type()],
            ['type' => Photo::type()],
            ['type' => Photo::type()],
            ['type' => Location::type()],
            ['type' => Location::type()],
            ['type' => Location::type()],
            ['type' => Item::type()],
        ))->create();

        // 実行
        $photoTags = Tag::by('Feeldee')->of(Photo::type())->get();

        // 評価
        $this->assertCount(2, $photoTags);
    }

    /**
     * タグ名による絞り込み
     * 
     * - タグ名を指定して絞り込むことができることを確認します。
     * - タグ名を完全一致検索できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ名による絞り込み
     */
    public function test_scopeName()
    {
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(Tag::factory()->count(9)->sequence(
            ['type' => Journal::type(), 'name' => 'Favorite'],
            ['type' => Photo::type(), 'name' => 'Favorite'],
            ['type' => Photo::type(), 'name' => 'Pickup'],
            ['type' => Photo::type(), 'name' => 'News'],
            ['type' => Location::type(), 'name' => 'WestPark'],
            ['type' => Location::type(), 'name' => 'HomeGround'],
            ['type' => Location::type(), 'name' => 'HomeTown'],
            ['type' => Location::type(), 'name' => 'CentralPark'],
            ['type' => Item::type(), 'name' => 'Favorite'],
        ))->create();

        // 実行
        $tags = Tag::by('Feeldee')->name('Favorite')->get();

        // 評価
        $this->assertCount(3, $tags);
    }

    /**
     * タグ名による絞り込み
     * 
     * - タグ名を指定して絞り込むことができることを確認します。
     * - タグ名を前方一致検索できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ名による絞り込み
     */
    public function test_scopeName_prefix()
    {
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(Tag::factory()->count(9)->sequence(
            ['type' => Journal::type(), 'name' => 'Favorite'],
            ['type' => Photo::type(), 'name' => 'Favorite'],
            ['type' => Photo::type(), 'name' => 'Pickup'],
            ['type' => Photo::type(), 'name' => 'News'],
            ['type' => Location::type(), 'name' => 'WestPark'],
            ['type' => Location::type(), 'name' => 'HomeGround'],
            ['type' => Location::type(), 'name' => 'HomeTown'],
            ['type' => Location::type(), 'name' => 'ParkHome'],
            ['type' => Item::type(), 'name' => 'Favorite'],
        ))->create();

        // 実行
        $homes = Tag::by('Feeldee')->name('Home', Like::Prefix)->get();

        // 評価
        $this->assertCount(2, $homes);
    }

    /**
     * タグ名による絞り込み
     * 
     * - タグ名を指定して絞り込むことができることを確認します。
     * - タグ名を後方一致検索できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/タグ#タグ名による絞り込み
     */
    public function test_scopeName_suffix()
    {
        Auth::shouldReceive('id')->andReturn(1);
        Profile::factory(['nickname' => 'Feeldee'])->has(Tag::factory()->count(9)->sequence(
            ['type' => Journal::type(), 'name' => 'Favorite'],
            ['type' => Photo::type(), 'name' => 'Favorite'],
            ['type' => Photo::type(), 'name' => 'Pickup'],
            ['type' => Photo::type(), 'name' => 'News'],
            ['type' => Location::type(), 'name' => 'WestPark'],
            ['type' => Location::type(), 'name' => 'HomeGround'],
            ['type' => Location::type(), 'name' => 'HomeTown'],
            ['type' => Location::type(), 'name' => 'ParkHome'],
            ['type' => Item::type(), 'name' => 'Favorite'],
        ))->create();

        // 実行
        $parks = Tag::by('Feeldee')->of(Location::class)->name('Park', Like::Suffix)->get();

        // 評価
        $this->assertCount(1, $parks);
    }
}
