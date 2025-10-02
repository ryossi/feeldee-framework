<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Feeldee\Framework\Casts\HTML;
use Feeldee\Framework\Casts\URL;
use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Category;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Journal;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\PublicLevel;
use Feeldee\Framework\Models\Recorder;
use Feeldee\Framework\Observers\PostPhotoShareObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Tests\Hooks\CustomHtmlHook;
use Tests\Hooks\CustomUrlHook;
use Tests\TestCase;

class PhotoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 投稿種別
     * 
     * - 写真の投稿種別は、"photo"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿種別
     */
    public function test_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
        ]);

        // 検証
        $this->assertEquals('photo', $photo->type(), '写真の投稿種別は、"photo"であること');
    }

    /**
     * 投稿者プロフィール
     * 
     * - 写真を作成したユーザのプロフィールあることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コ投稿者プロフィール
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
        ]);

        // 検証
        $this->assertEquals($profile->id, $photo->profile->id, '写真を作成したユーザのプロフィールあること');
        $this->assertDatabaseHas('photos', [
            'profile_id' => $profile->id,
        ]);
    }

    /**
     * 投稿タイトル
     * 
     * - 登録した写真に付けるタイトルであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿タイトル
     */
    public function test_title()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $title = '写真のタイトル';

        // 実行
        $photo = $profile->photos()->create([
            'title' => $title,
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
        ]);

        // 検証
        $this->assertEquals($title, $photo->title, '登録した写真に付けるタイトルであること');
    }

    /**
     * 投稿内容
     * 
     * - 写真の説明またはメモ書きなどであることを確認します。
     * - HTMLが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿内容
     */
    public function test_value_html()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>写真内容の本文</p>';

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($value, $photo->value, '写真の説明またはメモ書きなどであること');
        // HTMLが使用できること
        $this->assertDatabaseHas('photos', [
            'value' => $value,
        ]);
    }

    /**
     * 投稿内容
     * 
     * - 写真の説明またはメモ書きなどであることを確認します。
     * - テキストが使用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿内容
     */
    public function test_value_text()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '写真の本文';

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($value, $photo->value, '写真の説明またはメモ書きなどであること');
        // テキストが使用できること
        $this->assertDatabaseHas('photos', [
            'value' => $value,
        ]);
    }

    /**
     * 投稿内容テキスト
     * 
     * - 投稿内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - 投稿内容の登録時に、自動変換されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿内容テキスト
     */
    public function test_text_create()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>写真内容の本文</p>';
        $expected = '写真内容の本文';

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($expected, $photo->text, '投稿内容から、HTMLタグのみを排除したテキスト表現であること');
        // 投稿内容の登録時に、自動変換されること
        $this->assertDatabaseHas('photos', [
            'text' => $expected,
        ]);
    }

    /**
     * 投稿内容テキスト
     * 
     * - 投稿内容から、HTMLタグのみを排除したテキスト表現であることを確認します。
     * - 投稿内容の編集時に、自動変換されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿内容テキスト
     */
    public function test_text_update()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->has(Photo::factory()->count(1))->create();
        $photo = $profile->photos->first();
        $value = '<p>写真内容の本文</p>';
        $expected = '写真内容の本文';

        // 実行
        $photo->update([
            'value' => $value,
        ]);

        // 検証
        $this->assertEquals($expected, $photo->text, '投稿内容から、HTMLタグのみを排除したテキスト表現であること');
        // 投稿内容の編集時に、自動変換されること
        $this->assertDatabaseHas('photos', [
            'text' => $expected,
        ]);
    }

    /**
     * 投稿公開フラグ
     * 
     * - デフォルトは、非公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿公開フラグ
     */
    public function test_is_public_default()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
        ]);

        // 評価
        $this->assertFalse($photo->isPublic, 'デフォルトは、非公開であること');
    }

    /**
     * 投稿公開フラグ
     * 
     * - 公開できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿公開フラグ
     */
    public function test_is_public_doPublic()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo = Photo::factory([
            'is_public' => false,
            'profile_id' => $profile->id,
        ])->create();

        // 実行
        $photo->doPublic();

        // 評価
        $this->assertTrue($photo->isPublic, '公開できること');
    }

    /**
     * 投稿公開フラグ
     * 
     * - 非公開にできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿公開フラグ
     */
    public function test_is_public_doPrivate()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo = Photo::factory([
            'is_public' => true,
            'profile_id' => $profile->id,
        ])->create();

        // 実行
        $photo->doPrivate();

        // 評価
        $this->assertFalse($photo->isPublic, '非公開にできること');
    }

    /**
     * 投稿公開レベル
     * 
     * - デフォルトは、"自分"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿公開レベル
     */
    public function test_public_level_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
        ]);

        // 評価
        $this->assertEquals(PublicLevel::Private, $photo->public_level, 'デフォルトは、"自分"であること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'public_level' => PublicLevel::Private,
        ]);
    }

    /**
     * 投稿公開レベル
     * 
     * - 投稿公開レベルを指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿公開レベル
     */
    public function test_public_level()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
            'public_level' => PublicLevel::Member,
        ]);

        // 評価
        $this->assertEquals(PublicLevel::Member, $photo->public_level, '投稿公開レベルを指定できること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'public_level' => PublicLevel::Member,
        ]);
    }

    /**
     * 投稿公開レベル
     * 
     * - 投稿公開レベルを変更できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿公開レベル
     */
    public function test_public_level_update()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo = Photo::factory([
            'profile_id' => $profile->id,
        ])->create();

        // 実行
        $photo->public_level = PublicLevel::Member;
        $photo->save();

        // 評価
        $this->assertEquals(PublicLevel::Member, $photo->public_level, '投稿公開レベルを変更できること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'public_level' => PublicLevel::Member,
        ]);
    }

    /**
     * 投稿タグリスト
     * 
     * - タグ付けできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿タグリスト
     */
    public function test_tags()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Photo::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Photo::type(),
        ]);

        // 実行
        $photo = $profile->photos()->create([
            'title' => 'テスト写真',
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
            'tags' => [$tag1, $tag2],
        ]);

        // 評価
        $this->assertEquals(2, $photo->tags->count(), 'タグ付けできること');
        foreach ($photo->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $photo->id,
                'taggable_type' => Photo::type(),
            ]);
        }
    }

    /**
     * 投稿タグリスト
     * 
     * - タグIDを指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿タグリスト
     */
    public function test_tags_id()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Photo::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Photo::type(),
        ]);

        // 実行
        $photo = $profile->photos()->create([
            'title' => 'テスト写真',
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
            'tags' => [$tag1->id, $tag2->id],
        ]);

        // 評価
        $this->assertEquals(2, $photo->tags->count(), 'タグIDを指定できること');
        foreach ($photo->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $photo->id,
                'taggable_type' => Photo::type(),
            ]);
        }
    }

    /**
     * 投稿タグリスト
     * 
     * - タグ所有プロフィールが投稿者プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿タグリスト
     */
    public function test_tags_profile_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $otherProfile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Photo::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Photo::type(),
        ]);

        // 実行
        $this->assertThrows(function () use ($otherProfile, $tag1, $tag2) {
            $otherProfile->photos()->create([
                'title' => 'テスト写真',
                'src' => '/mbox/photo.jpg',
                'posted_at' => now(),
                'tags' => [$tag1->id, $tag2->id],
            ]);
        }, ApplicationException::class, 'TagProfileMissmatch');
    }

    /**
     * 投稿タグリスト
     * 
     * - タグタイプが投稿種別と一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿タグリスト
     */
    public function test_tags_type_missmatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Photo::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Item::type(),
        ]);

        // 実行
        $this->assertThrows(function () use ($profile, $tag1, $tag2) {
            $profile->photos()->create([
                'title' => 'テスト写真',
                'src' => '/mbox/photo.jpg',
                'posted_at' => now(),
                'tags' => [$tag1->id, $tag2->id],
            ]);
        }, ApplicationException::class, 'TagTypeMissmatch');
    }

    /**
     * 投稿タグリスト
     * 
     * - タグ名を指定した場合は、タグ所有プロフィールと投稿者プロフィールが一致し、かつ投稿種別と同じタグタイプのタグの中からタグ名が一致するタグのIDが設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿タグリスト
     */
    public function test_tags_name()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Photo::type(),
        ]);
        $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Photo::type(),
        ]);

        // 実行
        $photo = $profile->photos()->create([
            'title' => 'テスト写真',
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
            'tags' => ['タグ1', 'タグ2'],
        ]);

        // 評価
        $this->assertEquals(2, $photo->tags->count(), 'タグ名を指定した場合は、タグ所有プロフィールと投稿者プロフィールが一致し、かつ投稿種別と同じタグタイプのタグの中からタグ名が一致するタグのIDが設定されること');
        foreach ($photo->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $photo->id,
                'taggable_type' => Photo::type(),
            ]);
        }
    }

    /**
     * 投稿タグリス
     * 
     * - 一致するタグが存在しない場合は無視されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿タグリスト
     */
    public function test_tags_name_nomatch()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Photo::type(),
        ]);
        $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Photo::type(),
        ]);

        // 実行
        $photo = $profile->photos()->create([
            'title' => 'テスト写真',
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
            'tags' => ['タグ3', 'タグ2'],
        ]);

        // 評価
        $this->assertEquals(1, $photo->tags->count(), '一致するタグが存在しない場合は無視されること');
        foreach ($photo->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $photo->id,
                'taggable_type' => Photo::type(),
            ]);
        }
    }

    /**
     * 投稿タグリスト
     * 
     * - 対応するタグが削除された場合は、投稿タグリストから自動的に除外されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿タグリスト
     */
    public function test_tags_delete()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $tag1 = $profile->tags()->create([
            'name' => 'タグ1',
            'type' => Photo::type(),
        ]);
        $tag2 = $profile->tags()->create([
            'name' => 'タグ2',
            'type' => Photo::type(),
        ]);
        $photo = $profile->photos()->create([
            'title' => 'テスト写真',
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
            'tags' => [$tag1, $tag2],
        ]);

        // 実行
        $tag1->delete();

        // 評価
        $this->assertEquals(1, $photo->tags->count(), '対応するタグが削除された場合は、投稿タグリストから自動的に除外されること');
        foreach ($photo->tags as $tag) {
            $this->assertDatabaseHas('taggables', [
                'tag_id' => $tag->id,
                'taggable_id' => $photo->id,
                'taggable_type' => Photo::type(),
            ]);
        }
    }

    /**
     * 写真リスト
     * 
     * - 記事内容に写真が使用されている投稿のコレクションであることを確認します。
     * - 写真を削除しても、関連する投稿の記事内容には影響はないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿リスト
     */
    public function test_posts()
    {
        // 準備
        Journal::observe(PostPhotoShareObserver::class);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = 'これは写真リストのテストです。<br>
                1枚目の写真:<img src="http://photo.test/img/1.png" /><br>
                2枚目の写真:<img src="http://photo.test/img/2.png" /><br>
                3枚目の写真:<img src="http://photo.test/img/2.png" /><br3
                ';

        // 実行
        $journalA = $profile->journals()->create([
            'posted_at' => Carbon::parse('2025-04-22'),
            'title' => '投稿A',
            'value' => $value,
        ]);
        $journalB = $profile->journals()->create([
            'posted_at' => Carbon::parse('2025-04-23'),
            'title' => '投稿B',
        ]);
        $journalB->value = '
                これは写真リストのテストです。<br>
                1枚目の写真:<img src="http://photo.test/img/2.png" /><br>
                2枚目の写真:<img src="http://photo.test/img/3.png" /><br>
                3枚目の写真:<img src="http://photo.test/img/4.png" /><br>
                ';
        $journalB->save();
        $photo1 = $profile->photos()->ofSrc('http://photo.test/img/1.png')->first();
        $photo1->delete();

        // 評価
        $photo2 = $profile->photos()->ofSrc('http://photo.test/img/2.png')->first();
        $this->assertEquals(2, $photo2->relatedJournals->count(), '投稿内容に写真が使用されている記録のコレクションであること');
        $this->assertEquals($value, $journalA->value, '写真を削除しても、関連する投稿の記事内容には影響はないこと');
    }

    /**
     * 投稿レコードリスト
     * 
     * - レコーダによって記録された写真のレコードリストであることを確認します。
     * - レコーダの指定は、レコーダそのものを指定することができることを確認します。
     * - レコーダの指定は、レコーダIDを指定することができることを確認します。
     * - レコーダの指定は、レコーダ名を指定することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿レコードリスト
     */
    public function test_content_records()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo = Photo::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder1 = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Photo::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);
        $recorder2 = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Photo::type(),
            'data_type' => 'bool',
            'name' => 'テストレコーダ2',
        ]);
        $recorder3 = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Photo::type(),
            'data_type' => 'date',
            'name' => 'テストレコーダ3',
        ]);

        // 実行
        $photo->record($recorder1, 1);
        $photo->record($recorder2->id, true);
        $photo->record('テストレコーダ3', '2025-04-22');

        // 評価
        $this->assertEquals(3, $photo->records->count(), 'レコーダによって記録された写真のレコードリストであること');
        foreach ($photo->records as $i => $record) {
            if ($i == 0) {
                $this->assertEquals($recorder1->id, $record->recorder_id, 'レコーダそのものを指定することができること');
            } elseif ($i == 1) {
                $this->assertEquals($recorder2->id, $record->recorder_id, 'レコーダIDを指定することができること');
            } elseif ($i == 2) {
                $this->assertEquals($recorder3->id, $record->recorder_id, 'レコーダ名を指定することができること');
            }
        }
    }

    /**
     * 投稿レコードリスト
     * 
     * - レコーダを指定する場合は、レコーダ所有プロフィールが投稿者プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿レコードリスト
     */
    public function test_content_records_recorder_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo = Photo::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder = Recorder::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
            'type' => Photo::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);

        // 実行
        $this->expectException(ApplicationException::class);
        $this->expectExceptionCode(73007);
        $photo->record($recorder, 1);
    }

    /**
     * 投稿レコードリスト
     * 
     * - レコーダを指定する場合は、レコーダタイプが投稿種別と一致していることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿レコードリスト
     */
    public function test_content_records_recorder_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo = Photo::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Item::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);

        // 実行
        $this->expectException(ApplicationException::class);
        $this->expectExceptionCode(73008);
        $photo->record($recorder, 1);
    }

    /**
     * 投稿レコードリスト
     * 
     * - レコーダIDを指定する場合は、レコーダ所有プロフィールが投稿者プロフィールと一致することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿レコードリスト
     */
    public function test_content_records_recorder_id_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo = Photo::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder = Recorder::factory()->create([
            'profile_id' => Profile::factory()->create()->id,
            'type' => Photo::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);

        // 実行
        $this->expectException(ApplicationException::class);
        $this->expectExceptionCode(73007);
        $photo->record($recorder->id, 1);
    }

    /**
     * 投稿レコードリスト
     * 
     * - レコーダIDを指定する場合は、レコーダタイプが投稿種別と一致していることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿レコードリスト
     */
    public function test_content_records_recorder_id_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo = Photo::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Item::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);

        // 実行
        $this->expectException(ApplicationException::class);
        $this->expectExceptionCode(73008);
        $photo->record($recorder->id, 1);
    }

    /**
     * 投稿レコードリスト
     * 
     * - 対応するレコーダが削除された場合は、投稿レコードリストからも自動的に除外されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿レコードリスト
     */
    public function test_content_records_recorder_delete()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $photo = Photo::factory()->create([
            'profile_id' => $profile->id,
        ]);
        $recorder = Recorder::factory()->create([
            'profile_id' => $profile->id,
            'type' => Photo::type(),
            'data_type' => 'int',
            'name' => 'テストレコーダ1',
        ]);

        // 実行
        $photo->record($recorder, 1);
        $recorder->delete();

        // 評価
        $this->assertEquals(0, $photo->records->count(), '対応するレコーダが削除された場合は、投稿レコードリストからも自動的に除外されること');
        $this->assertDatabaseEmpty('records');
    }

    /**
     * 投稿内容
     * 
     * - 取得時にHTMLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿内容
     */
    public function test_content_value_html_cast_hook_get()
    {

        // 準備
        Config::set(HTML::CONFIG_KEY_HTML_CAST_HOOKS, [
            CustomHtmlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>テスト投稿</p>';
        $photo = Photo::factory()->create([
            'profile_id' => $profile->id,
            'value' => $value,
        ]);

        // 実行
        $expected = $photo->value;

        // 評価
        $this->assertEquals(CustomHtmlHook::PREFIX . $value, $expected, '取得時にHTMLキャストフックが利用できることを確認します。');
        $this->assertDatabaseHas('photos', [
            'profile_id' => $profile->id,
            'value' => $value,
        ]);
    }

    /**
     * 投稿内容
     * 
     * - 設定時にHTMLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#投稿内容
     */
    public function test_content_value_html_cast_hook_set()
    {

        // 準備
        Config::set(HTML::CONFIG_KEY_HTML_CAST_HOOKS, [
            CustomHtmlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $value = '<p>テスト投稿</p>';

        // 実行
        $profile->photos()->create([
            'title' => 'テスト写真',
            'src' => '/mbox/photo.jpg',
            'posted_at' => now(),
            'value' => CustomHtmlHook::PREFIX . $value,
        ]);

        // 評価
        // 設定時にHTMLキャストフックが利用できること
        $this->assertDatabaseHas('photos', [
            'profile_id' => $profile->id,
            'value' => $value,
        ]);
    }

    /**
     * 写真ソース
     * 
     * - 取得時にURLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#写真ソース
     */
    public function test_src_url_cast_hook_get()
    {
        // 準備
        Config::set(URL::CONFIG_KEY_URL_CAST_HOOKS, [
            CustomUrlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $src = 'http://photo.test/img/photo.jpg';
        $photo = Photo::factory()->create([
            'profile_id' => $profile->id,
            'src' => $src,
        ]);

        // 実行
        $expected = $photo->src;

        // 評価
        $this->assertEquals(CustomUrlHook::PREFIX . $src, $expected, '取得時にURLキャストフックが利用できることを確認します。');
        $this->assertDatabaseHas('photos', [
            'profile_id' => $profile->id,
            'src' => $src,
        ]);
    }

    /**
     * 写真ソース
     * 
     * - 設定時にURLキャストフックが利用できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#写真ソース
     */
    public function test_src_url_cast_hook_set()
    {
        // 準備
        Config::set(URL::CONFIG_KEY_URL_CAST_HOOKS, [
            CustomUrlHook::class,
        ]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $src = 'http://photo.test/img/photo.jpg';

        // 実行
        $profile->photos()->create([
            'title' => 'テスト写真',
            'src' => CustomUrlHook::PREFIX . $src,
            'posted_at' => now(),
        ]);

        // 評価
        // 設定時にURLキャストフックが利用できること
        $this->assertDatabaseHas('photos', [
            'profile_id' => $profile->id,
            'src' => $src,
        ]);
    }

    /**
     * 新規作成
     * 
     * - 写真の作成は、写真を追加したいプロフィールの写真リストに追加することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#新規作成
     */
    public function test_create()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $src = '/mbox/photo.jpg';
        $postedAt = now();

        // 実行
        $photo = $profile->photos()->create([
            'src' => $src,
            'posted_at' => $postedAt,
        ]);

        // 評価
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'profile_id' => $profile->id,
            'src' => $src,
            'posted_at' => $postedAt,
        ]);
    }

    /**
     * 新規作成
     * 
     * - 写真ソースは、必須であることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#新規作成
     */
    public function test_create_src_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $this->assertThrows(function () use ($profile) {
            $profile->photos()->create([
                'posted_at' => now(),
            ]);
        }, ApplicationException::class, 'PhotoSrcRequired');
    }

    /**
     * 新規作成
     * 
     * - 投稿日時を省略した場合は、システム日時が設定されることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#新規作成
     */
    public function test_create_posted_at_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $src = '/mbox/photo.jpg';

        // 実行
        $photo = $profile->photos()->create([
            'src' => $src,
        ]);

        // 評価
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'profile_id' => $profile->id,
            'src' => $src,
            'posted_at' => $photo->posted_at->format('Y-m-d H:i:s'), // システム日時が設定されていること
        ]);
    }

    /**
     * 写真リストの並び順
     *
     * - 写真リストのデフォルトの並び順は、投稿日時降順（最新順）であることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#写真リストの並び順
     */
    public function test_collection_sort_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory(
            ['nickname' => 'Feeldee']
        )->create();
        $postA = Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-22 10:00:00'),
        ]);
        $postB = Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-23 10:00:00'),
        ]);
        $postC = Photo::factory()->create([
            'profile_id' => $profile->id,
            'posted_at' => Carbon::parse('2025-04-21 10:00:00'),
        ]);

        // 実行
        $photos = Profile::of('Feeldee')->first()->photos;

        // 評価
        $this->assertEquals(3, $photos->count());
        $this->assertEquals($postB->id, $photos[0]->id);
        $this->assertEquals($postA->id, $photos[1]->id);
        $this->assertEquals($postC->id, $photos[2]->id);
    }
}
