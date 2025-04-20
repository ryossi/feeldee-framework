<?php

namespace Tests\Feature;

use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\PublicLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PhotoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * コンテンツ種別
     * 
     * - 写真のコンテンツ種別は、"photo"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツ種別
     */
    public function test_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
        ]);

        // 検証
        $this->assertEquals('photo', $photo->type(), '写真のコンテンツ種別は、"photo"であること');
    }

    /**
     * コンテンツ所有プロフィール
     * 
     * - 写真を作成したユーザのプロフィールあることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#ココンテンツ所有プロフィール
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
        ]);

        // 検証
        $this->assertEquals($profile->id, $photo->profile->id, '写真を作成したユーザのプロフィールあること');
        $this->assertDatabaseHas('photos', [
            'profile_id' => $profile->id,
        ]);
    }

    /**
     * コンテンツ公開フラグ
     * 
     * - デフォルトは、非公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツ公開フラグ
     */
    public function test_is_public_default()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
        ]);

        // 評価
        $this->assertFalse($photo->isPublic, 'デフォルトは、非公開であること');
    }

    /**
     * コンテンツ公開フラグ
     * 
     * - 公開できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツ公開フラグ
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
     * コンテンツ公開フラグ
     * 
     * - 非公開にできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツ公開フラグ
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
     * コンテンツ公開レベル
     * 
     * - デフォルトは、"自分"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツ公開レベル
     */
    public function test_public_level_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
        ]);

        // 評価
        $this->assertEquals(PublicLevel::Private, $photo->public_level, 'デフォルトは、"自分"であること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'public_level' => PublicLevel::Private,
        ]);
    }

    /**
     * コンテンツ公開レベル
     * 
     * - コンテンツ公開レベルを指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツ公開レベル
     */
    public function test_public_level()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $photo = $profile->photos()->create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
            'public_level' => PublicLevel::Member,
        ]);

        // 評価
        $this->assertEquals(PublicLevel::Member, $photo->public_level, 'コンテンツ公開レベルを指定できること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'public_level' => PublicLevel::Member,
        ]);
    }

    /**
     * コンテンツ公開レベル
     * 
     * - コンテンツ公開レベルを変更できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/写真#コンテンツ公開レベル
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
        $this->assertEquals(PublicLevel::Member, $photo->public_level, 'コンテンツ公開レベルを変更できること');
        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'public_level' => PublicLevel::Member,
        ]);
    }
}
