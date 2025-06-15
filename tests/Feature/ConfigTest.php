<?php

namespace Tests\Feature;

use Feeldee\Framework\Models\Profile;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

/**
 * コンフィグの用語を担保するための機能テストです。
 * 
 * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#コンフィグ
 */
class ConfigTest extends TestCase
{
    use RefreshDatabase;

    /**
     * コンフィグ
     * 
     * - コンフィグタイプをプロフィールに直接指定してアクセスすることができることを確認します。
     * - コンフィグタイプに一致するコンフィグがデータベースに登録されていない場合、新しいカスタムコンフィグクラスのインスタンスを取得できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#コンフィグ
     */
    public function test_config_access()
    {
        // 準備
        config(['feeldee.profile.config.value_objects' => [
            'custom_config_1' => \Tests\ValueObjects\Configs\CustomConfig::class,
            'custom_config_2' => \Tests\ValueObjects\Configs\CustomConfig::class,
        ]]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $value = $profile->custom_config_1;

        // 評価
        $this->assertNotNull($value, 'コンフィグタイプがデータベースに登録されていない場合でも、カスタムコンフィグクラスのクラスインスタンスを取得できること');
        $this->assertInstanceOf(\Tests\ValueObjects\Configs\CustomConfig::class, $value, 'コンフィグタイプを直接指定して取得することができること');
    }

    /**
     * コンフィグ
     * 
     * - コンフィグタイプが未定義の場合には、コンフィグタイプをプロフィールに直接指定してアクセスすることができないことを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#コンフィグ
     */
    public function test_config_access_type_undefined()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $value = $profile->custom_config_1;

        // 評価
        $this->assertNull($value, 'コンフィグタイプが未定義の場合には、コンフィグタイプを直接指定してアクセスすることができないこと');
    }

    /**
     * コンフィグ値
     * 
     * - コンフィグタイプごとに事前に定義しておいたカスタムコンフィグクラスに変換されることを確認します。
     * - 自動的にデシリアライズされることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#コンフィグ値
     */
    public function test_config_value_deserialized()
    {
        // 準備
        config(['feeldee.profile.config.value_objects' => [
            'custom_config' => \Tests\ValueObjects\Configs\CustomConfig::class,
        ]]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $profile->configs()->create([
            'type' => 'custom_config',
            'value' => new \Tests\ValueObjects\Configs\CustomConfig('xxxx', 'yyyy'),
        ]);

        // 実行
        $config = $profile->configs()->ofType('custom_config')->first();

        // 評価
        $this->assertInstanceOf(\Tests\ValueObjects\Configs\CustomConfig::class, $config->value, 'コンフィグタイプごとに事前に定義しておいたカスタムコンフィグクラスに変換されること');
        $this->assertEquals('xxxx', $config->value->value1, '自動的にデシリアライズされること');
        $this->assertEquals('yyyy', $config->value->value2, '自動的にデシリアライズされること');
    }

    /**
     * コンフィグ値
     * 
     * - カスタムコンフィグクラスのインスタンスに設定した値が自動的にJSON形式にシリアライズされてカスタム値に保存されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#コンフィグ値
     */
    public function test_config_value_serialized()
    {
        // 準備
        config(['feeldee.profile.config.value_objects' => [
            'custom_config' => \Tests\ValueObjects\Configs\CustomConfig::class,
        ]]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $custom_config = new \Tests\ValueObjects\Configs\CustomConfig();
        $custom_config->value1 = 'xxxx';
        $custom_config->value2 = 'yyyy';
        $config = $profile->configs()->create([
            'type' => 'custom_config',
            'value' => $custom_config,
        ]);

        // 評価

        // カスタムコンフィグクラスのインスタンスに設定した値が自動的にJSON形式にシリアライズされてカスタム値に保存されること
        $this->assertDatabaseHas('configs', [
            'id' => $config->id,
            'type' => 'custom_config',
            'value' => '{"value1":"xxxx","value2":"yyyy"}',
        ]);
    }

    /**
     * コンフィグ値
     * 
     * - まとめて値を設定することもできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#コンフィグ値
     */
    public function test_config_value_fill()
    {

        // 準備
        config(['feeldee.profile.config.value_objects' => [
            'custom_config' => \Tests\ValueObjects\Configs\CustomConfig::class,
        ]]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $profile->configs()->create([
            'type' => 'custom_config',
            'value' => new \Tests\ValueObjects\Configs\CustomConfig(),
        ]);

        // 実行
        $config = $profile->configs()->ofType('custom_config')->first();
        $config->value->fill([
            'value1' => 'xxxx',
            'value2' => 'yyyy',
        ]);
        $config->save();

        // 評価

        // まとめて値を設定することもできること
        $this->assertDatabaseHas('configs', [
            'id' => $config->id,
            'type' => 'custom_config',
            'value' => '{"value1":"xxxx","value2":"yyyy"}',
        ]);
    }

    /**
     * コンフィグ値
     * 
     * - プロフィールからコンフィグタイプを指定して直接アクセスして値を変更すると、プロフィール保存時にまとめて変更されることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#コンフィグ値
     */
    public function test_config_value_update()
    {
        // 準備
        config(['feeldee.profile.config.value_objects' => [
            'custom_config' => \Tests\ValueObjects\Configs\CustomConfig::class,
        ]]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();
        $profile->configs()->create([
            'type' => 'custom_config',
            'value' => new \Tests\ValueObjects\Configs\CustomConfig(),
        ]);

        // 実行
        $profile->custom_config->value1 = 'xxxx';
        $profile->custom_config->value2 = 'yyyy';
        $profile->save();

        // 評価

        // プロフィールからコンフィグタイプを指定して直接アクセスして値を変更すると、プロフィール保存時にまとめて変更されること
        $this->assertDatabaseHas('configs', [
            'type' => 'custom_config',
            'value' => '{"value1":"xxxx","value2":"yyyy"}',
        ]);
    }

    /**
     * コンフィグ値によるプロフィール絞り込み
     * 
     * - コンフィグ値でのプロフィールの絞り込みができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#コンフィグ値によるプロフィール絞り込み
     */
    public function test_config_value_filter()
    {
        // 準備
        config(['feeldee.profile.config.value_objects' => [
            'custom_config' => \Tests\ValueObjects\Configs\CustomConfig::class,
        ]]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile1 = Profile::factory()->create();
        $profile2 = Profile::factory()->create();

        // プロフィール1にカスタムコンフィグを設定
        $profile1->configs()->create([
            'type' => 'custom_config',
            'value' => new \Tests\ValueObjects\Configs\CustomConfig('filter_value', 'value2'),
        ]);

        // プロフィール2にカスタムコンフィグを設定
        $profile2->configs()->create([
            'type' => 'custom_config',
            'value' => new \Tests\ValueObjects\Configs\CustomConfig('value1', 'value2'),
        ]);

        // 実行
        $filteredProfiles = Profile::whereConfigContains('custom_config', 'value1', 'filter_value')->get();

        // 評価
        $this->assertCount(1, $filteredProfiles, 'コンフィグ値でのプロフィールの絞り込みができること');
        $this->assertEquals($profile1->id, $filteredProfiles->first()->id, '正しいプロフィールが取得されること');
    }
}
