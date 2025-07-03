<?php

namespace Tests\Feature;

use Feeldee\Framework\Models\Config;
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
        config([Config::CONFIG_VALUE_OBJECTS_KEY => [
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
        $this->assertDatabaseHas('configs', [
            'type' => 'custom_config_1',
            'value' => '{"value1":null,"value2":null}',
        ]);
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
     * コンフィグ
     * 
     * - config(type)メソッドを使用することでコンフィグタイプに一致するコンフィグが取得できることを確認します。
     * - コンフィグタイプに一致するコンフィグが登録されてない場合でも、データベースに登録してからコンフィグクラスを返してくれることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#コンフィグ
     */
    public function test_config_method_access()
    {
        // 準備
        config([Config::CONFIG_VALUE_OBJECTS_KEY => [
            'custom_config' => \Tests\ValueObjects\Configs\CustomConfig::class,
        ]]);
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 実行
        $config = $profile->config('custom_config');

        // 評価
        $this->assertNotNull($config, 'コンフィグタイプがデータベースに登録されていない場合でも、カスタムコンフィグクラスのクラスインスタンスを取得できること');
        $this->assertInstanceOf(\Tests\ValueObjects\Configs\CustomConfig::class, $config->value, 'コンフィグタイプに一致するコンフィグが取得できること');
        // コンフィグタイプに一致するコンフィグが登録されてない場合でも、データベースに登録してからコンフィグクラスを返してくれること
        $this->assertDatabaseHas('configs', [
            'type' => 'custom_config',
            'value' => '{"value1":null,"value2":null}',
        ]);
    }

    /**
     * コンフィグ
     * 
     * - config(type)メソッドを使用することでコンフィグタイプに一致するコンフィグが取得できることを確認します。
     * - コンフィグタイプが未定義の場合には、アプリケーションエラーが発生することを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#コンフィグ
     */
    public function test_config_method_access_type_undefined()
    {
        // 準備
        Auth::shouldReceive('id')->andReturn(1);
        $profile = Profile::factory()->create();

        // 評価
        $this->expectException(\Feeldee\Framework\Exceptions\ApplicationException::class);
        $this->expectExceptionMessage('ProfileConfigTypeUndefined');

        // 実行
        $profile->config('custom_config_1');
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
        config([Config::CONFIG_VALUE_OBJECTS_KEY => [
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
        config([Config::CONFIG_VALUE_OBJECTS_KEY => [
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
        config([Config::CONFIG_VALUE_OBJECTS_KEY => [
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
        config([Config::CONFIG_VALUE_OBJECTS_KEY => [
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
        config([Config::CONFIG_VALUE_OBJECTS_KEY => [
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

    /**
     * スタムコンフィグクラスからコンフィグへのアクセス
     *
     * - コンフィグ値生成時に生成元となるコンフィグをセットしてくれることを確認します。
     * - カスタムコンフィグクラスの中で生成元であるコンフィグにアクセスすることができることを確認します。
     *
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#スタムコンフィグクラスからコンフィグへのアクセス
     */
    public function test_config_access_from_custom_config()
    {
        // 準備
        config([Config::CONFIG_VALUE_OBJECTS_KEY => [
            'custom_config' => \Tests\ValueObjects\Configs\CustomConfigWithModel::class,
        ]]);
        Auth::shouldReceive('id')->andReturn(1);
        $nickname = 'Test User';
        $profile = Profile::factory()->create(
            [
                'nickname' => $nickname,
            ]
        );
        $profile->configs()->create([
            'type' => 'custom_config',
        ]);

        // 実行
        $accepted_nickname = $profile->custom_config->getProfileNickname();

        // 評価
        $this->assertEquals($accepted_nickname, $nickname, 'カスタムコンフィグクラスの中で生成元であるコンフィグにアクセスすることができること');
    }

    /**
     * スタムコンフィグクラスからコンフィグへのアクセス
     * 
     * - シリアライズ時に生成元となるコンフィグをセットしてくれることを確認します。
     * - カスタムコンフィグクラスの中で生成元であるコンフィグにアクセスすることができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#スタムコンフィグクラスからコンフィグへのアクセス
     */
    public function test_config_access_from_custom_config_serialized()
    {
        // 準備
        config([Config::CONFIG_VALUE_OBJECTS_KEY => [
            'custom_config' => \Tests\ValueObjects\Configs\CustomConfigWithModel::class,
        ]]);
        Auth::shouldReceive('id')->andReturn(1);
        $nickname = 'Test User';
        $profile = Profile::factory()->create(
            [
                'nickname' => $nickname,
            ]
        );
        $profile->configs()->create([
            'type' => 'custom_config',
            'value' => new \Tests\ValueObjects\Configs\CustomConfigWithModel(null, 'xxxx', 'yyyy'),
        ]);
        $this->assertDatabaseHas('configs', [
            'type' => 'custom_config',
            'value' => '{"value1":"xxxx","value2":"yyyy"}',
        ]);

        // 実行
        $accepted_nickname = $profile->custom_config->getProfileNickname();

        // 評価
        $this->assertEquals($accepted_nickname, $nickname, 'カスタムコンフィグクラスの中で生成元であるコンフィグにアクセスすることができること');
    }
}
