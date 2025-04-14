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
}
