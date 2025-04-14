<?php

namespace Tests\Feature;

use Feeldee\Framework\Contracts\HssProfile;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Profile;
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
        $photo = Photo::create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
            'profile' => $profile,
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
        $photo = Photo::create([
            'src' => '/mbox/photo.jpg',
            'regist_datetime' => now(),
            'profile' => $profile,
        ]);

        // 検証
        $this->assertEquals($profile->id, $photo->profile->id, '写真を作成したユーザのプロフィールあること');
        $this->assertDatabaseHas('photos', [
            'profile_id' => $profile->id,
        ]);
    }
}
