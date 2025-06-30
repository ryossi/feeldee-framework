<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * ValueObjectの用語を担保するための機能テストです。
 * 
 * @link https://github.com/ryossi/feeldee-framework/wiki/ValueObject
 */
class ValueObjectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * シリアライズ
     * 
     * - クラスをJSON形式の文字列に変換することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/ValueObject
     */
    public function test_serialize()
    {

        // 準備
        $value = new \Tests\ValueObjects\Configs\CustomConfig('xxxx', 'yyyy');

        // 実行
        $json = $value->toJson();

        // 評価
        $this->assertEquals('{"value1":"xxxx","value2":"yyyy"}', $json, 'ValueObjectをJSON形式の文字列に変換できること');
    }

    /**
     * デシリアライズ
     * 
     * - JSON形式の文字列からValueObjectを復元できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/ValueObject
     */
    public function test_deserialize()
    {
        // 準備
        $json = '{"value1":"xxxx","value2":"yyyy"}';
        $value = new \Tests\ValueObjects\Configs\CustomConfig();

        // 実行
        $value->fromJson($json);

        // 評価
        $this->assertEquals('xxxx', $value->value1, 'ValueObjectをJSON形式の文字列から復元できること');
        $this->assertEquals('yyyy', $value->value2, 'ValueObjectをJSON形式の文字列から復元できること');
    }

    /**
     * 永続化から属性を除外
     * 
     * - ValueObjectの属性を永続化から除外できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/ValueObject
     */
    public function test_exclude_attributes()
    {
        // 準備
        $value = new \Tests\ValueObjects\Configs\CustomConfig('xxxx', 'yyyy', 'excluded_value');;

        // 実行
        $json = $value->toJson();

        // 評価
        $this->assertEquals('{"value1":"xxxx","value2":"yyyy"}', $json, 'ValueObjectの属性を永続化から除外できること');
    }

    /**
     * キャスト
     * 
     * - ValueObjectの属性をキャストできることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/ValueObject
     */
    public function test_cast_set()
    {
        // 準備
        $value = new  \Tests\ValueObjects\Configs\PointConfig(
            point_types: collect(['type1', 'type2'])
        );

        // 実行
        $json = $value->toJson();

        // 評価
        $this->assertEquals('{"point_types":["type1","type2"]}', $json, 'ValueObjectの属性をキャストできること');
    }

    /**
     * キャスト
     * 
     * - 配列をLaravel標準のCollectionクラスにキャストすることができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/ValueObject
     */
    public function test_cast_get()
    {
        // 準備
        $json = '{"point_types":["type1","type2"]}';

        // 実行
        $value = new \Tests\ValueObjects\Configs\PointConfig();
        $value->fromJson($json);

        // 評価
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $value->point_types, 'ValueObjectの属性をLaravel標準のCollectionクラスにキャストできること');
        $this->assertEquals(['type1', 'type2'], $value->point_types->toArray(), 'ValueObjectの属性をLaravel標準のCollectionクラスにキャストできること');
    }
}
