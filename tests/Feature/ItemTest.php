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
     * コンテンツ種別
     * 
     * - アイテムのコンテンツ種別は、"item"であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツ種別
     */
    public function test_type()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $item = Item::create([
            'title' => 'テストアイテム',
            'profile' => $profile,
        ]);

        // 検証
        $this->assertEquals('item', $item->type(), 'アイテムのコンテンツ種別は、"item"であること');
    }

    /**
     * コンテンツ所有プロフィール
     * 
     * - アイテムを作成したユーザのプロフィールであることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/アイテム#コンテンツ所有プロフィール
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $item = Item::create([
            'title' => 'テストアイテム',
            'profile' => $profile,
        ]);

        // 検証
        $this->assertEquals($profile->id, $item->profile->id, 'アイテムを作成したユーザのプロフィールであること');
        $this->assertDatabaseHas('items', [
            'profile_id' => $profile->id,
        ]);
    }
}
