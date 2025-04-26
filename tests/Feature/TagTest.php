<?php

namespace Tests\Feature;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Post;
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
            'type' => Post::type(),
        ]);

        // 評価
        $this->assertEquals($profile->id, $tag->profile->id, 'タグを作成したユーザのプロフィールであること');
        // プロフィールのIDが、タグ所有プロフィールIDに設定されていること
        $this->assertDatabaseHas('tags', [
            'profile_id' => $profile->id,
        ]);
    }

    /** 
     * カテゴリ所有プロフィール
     * 
     * - カテゴリ所有プロフィールは必須項目であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ所有プロフィール
     */
    public function test_profile_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);

        // 実行
        $this->assertThrows(function () {
            Tag::create([
                'name' => 'テストタグ',
                'type' => Post::type(),
            ]);
        }, ApplicationException::class, 'TagProfileRequired');
    }
}
