<?php

namespace Tests\Feature;

use Auth;
use Feeldee\Framework\Contracts\HssProfile;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 場所
     * 
     * - ログインユーザのみが作成できること
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所
     */
    public function test_create()
    {
        // 準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $this->assertThrows(function () {
            Location::create([]);
        }, \Feeldee\Framework\Exceptions\LoginRequiredException::class);
    }

    /**
     * コンテンツ種別
     * 
     * - 場所のコンテンツ種別（type）は、"location"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ種別
     */
    public function test_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($profile);
        Auth::shouldReceive('user')->andReturn($user);

        // 実行
        $location = Location::create([
            'title' => 'テスト',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
        ]);

        // 検証
        $this->assertEquals('location', $location->type(), '場所のコンテンツ種別（type）は、"location"であること');
    }

    /**
     * コンテンツ所有者
     * 
     * - ログインユーザのプロフィールのIDがコンテンツ所有者プロフィールIDに設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/場所#コンテンツ所有者
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $user = $this->mock(HssProfile::class);
        $user->shouldReceive('getProfile')->andReturn($profile);
        Auth::shouldReceive('user')->andReturn($user);

        // 実行
        $location = Location::create([
            'title' => 'テスト',
            'latitude' => 35.681236,
            'longitude' => 139.767125,
            'zoom' => 15,
        ]);

        // 検証
        $this->assertEquals($profile->id, $location->profile_id, 'ログインユーザのプロフィールのIDがコンテンツ所有者プロフィールIDに設定されること');
        $this->assertDatabaseHas('locations', [
            'profile_id' => $profile->id,
        ]);
    }
}
