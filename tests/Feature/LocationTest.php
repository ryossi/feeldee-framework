<?php

namespace Tests\Feature;

use Auth;
use Feeldee\Framework\Contracts\HssProfile;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\PublicLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * コンテンツ種別
     * 
     * - 場所のコンテンツ種別は、"location"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ種別
     */
    public function test_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $location = Location::create([
            'title' => 'テスト',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'profile' => $profile,
        ]);

        // 検証
        $this->assertEquals('location', $location->type(), '場所のコンテンツ種別は、"location"であること');
    }

    /**
     * コンテンツ所有プロフィール
     * 
     * - 場所を作成したユーザのプロフィールであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ所有プロフィール
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $location = Location::create([
            'title' => 'テスト',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'profile' => $profile,
        ]);

        // 検証
        $this->assertEquals($profile->id, $location->profile->id, '場所を作成したユーザのプロフィールであること');
        $this->assertDatabaseHas('locations', [
            'profile_id' => $profile->id,
        ]);
    }

    /**
     * コンテンツ公開フラグ
     * 
     * - デフォルトは、非公開であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ公開フラグ
     */
    public function test_is_public_default()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $location = Location::create([
            'title' => 'テスト',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'profile' => $profile,
        ]);

        // 評価
        $this->assertFalse($location->isPublic, 'デフォルトは、非公開であること');
    }

    /**
     * コンテンツ公開フラグ
     * 
     * - 公開できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ公開フラグ
     */
    public function test_is_public_doPublic()
    {
        // コメント対象準備
        Auth::shouldReceive('id')->andReturn(1);
        $location = Location::factory([
            'is_public' => false,
            'profile' => Profile::factory()->create(),
        ])->create();

        // 実行
        $location->doPublic();

        // 評価
        $this->assertTrue($location->isPublic, '公開できること');
    }

    /**
     * コンテンツ公開フラグ
     * 
     * - 非公開にできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ公開フラグ
     */
    public function test_is_public_doPrivate()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $location = Location::factory([
            'is_public' => true,
            'profile' => Profile::factory()->create(),
        ])->create();

        // 実行
        $location->doPrivate();

        // 評価
        $this->assertFalse($location->isPublic, '非公開にできること');
    }

    /**
     * コンテンツ公開レベル
     * 
     * - デフォルトは、"自分"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ公開レベル
     */
    public function test_public_level_default()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $location = Location::factory([
            'profile' => $profile,
        ])->create();

        // 評価
        $this->assertEquals(PublicLevel::Private, $location->public_level, 'デフォルトは、"自分"であること');
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'public_level' => PublicLevel::Private,
        ]);
    }

    /**
     * コンテンツ公開レベル
     * 
     * - コンテンツ公開レベルを指定できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ公開レベル
     */
    public function test_public_level()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $location = Location::create([
            'title' => 'テスト',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
            'profile' => $profile,
            'public_level' => PublicLevel::Friend,
        ]);

        // 評価
        $this->assertEquals(PublicLevel::Friend, $location->public_level, 'コンテンツ公開レベルを指定できること');
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'public_level' => PublicLevel::Friend,
        ]);
    }

    /**
     * コンテンツ公開レベル
     * 
     * - コンテンツ公開レベルを変更できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ公開レベル
     */
    public function test_public_level_update()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $location = Location::factory([
            'profile' => Profile::factory()->create(),
        ])->create();

        // 実行
        $location->public_level = PublicLevel::Friend;
        $location->save();

        // 評価
        $this->assertEquals(PublicLevel::Friend, $location->public_level, 'コンテンツ公開レベルを変更できること');
        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'public_level' => PublicLevel::Friend,
        ]);
    }
}
