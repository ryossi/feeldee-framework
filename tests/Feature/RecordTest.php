<?php

namespace Tests\Feature;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Post;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\Recorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RecordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * レコーダ所有プロフィール
     * 
     * - レコードを作成したユーザのプロフィールであることを確認します。
     * - プロフィールのIDが、レコーダ所有プロフィールIDに設定されていることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ所有プロフィール
     */
    public function test_profile()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $recorder = $profile->recorders()->create([
            'name' => 'テストレコード',
            'type' => Post::type(),
            'data_type' => 'int',
        ]);

        // 評価
        $this->assertEquals($profile->id, $recorder->profile->id, 'レコーダを作成したユーザのプロフィールであること');
        // プロフィールのIDが、レコーダ所有プロフィールIDに設定されていること
        $this->assertDatabaseHas('recorders', [
            'profile_id' => $profile->id,
        ]);
    }

    /** 
     * レコーダ所有プロフィール
     * 
     * - レコーダ所有プロフィールは必須項目であることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/レコード#レコーダ所有プロフィール
     */
    public function test_profile_required()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);

        // 実行
        $this->assertThrows(function () {
            Recorder::create([
                'name' => 'テストレコード',
                'type' => Post::type(),
                'data_type' => 'int',
            ]);
        }, ApplicationException::class, 'RecordRecorderProfileRequired');
    }
}
