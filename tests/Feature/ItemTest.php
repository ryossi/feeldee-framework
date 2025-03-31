<?php

namespace Tests\Feature;

use Feeldee\Framework\Contracts\HssProfile;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * アイテム
     * 
     * - ログインユーザのみが作成できること
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム
     */
    public function test_create()
    {
        // 準備
        Auth::shouldReceive('user')->andReturn(null);

        // 実行
        $this->assertThrows(function () {
            Item::create([]);
        }, \Feeldee\Framework\Exceptions\LoginRequiredException::class);
    }

    /**
     * コンテンツ種別
     * 
     * - アイテムのコンテンツ種別（type）は、"item"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツ種別
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
        $item = Item::create([
            'title' => 'テストアイテム',
        ]);

        // 検証
        $this->assertEquals('item', $item->type(), 'アイテムのコンテンツ種別（type）は、"item"であること');
    }

    /**
     * コンテンツ所有者
     * 
     * - ログインユーザのプロフィールのIDがコンテンツ所有者プロフィールIDに設定されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツ所有者
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
        $item = Item::create([
            'title' => 'テストアイテム',
        ]);

        // 検証
        $this->assertEquals($profile->id, $item->profile_id, 'ログインユーザのプロフィールのIDがコンテンツ所有者プロフィールIDに設定されること');
        $this->assertDatabaseHas('items', [
            'profile_id' => $profile->id,
        ]);
    }
}
