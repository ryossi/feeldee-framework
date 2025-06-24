<?php

namespace Tests\Feature;

use Feeldee\Framework\Models\PublicLevel;
use Tests\TestCase;

class PublicLevelTest extends TestCase
{

    /**
     * 公開レベルラベル配列取得
     * 
     * - すべての公開レベルとそのラベルの組み合わせを連想配列で取得できることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/公開レベル#公開レベル配列取得
     */
    public function test_PublicLevel_labels()
    {

        // 準備
        $config = config('feeldee.public_level.label');

        // 実行
        $labels = PublicLevel::labels();

        // 評価
        foreach ($labels as $key => $label) {
            $this->assertEquals($config[$key], $label, 'すべての公開レベルとそのラベルの組み合わせを連想配列で取得できること');
        }
    }

    /**
     * 公開レベルラベル配列取得
     * 
     * - Bladeテンプレートで公開レベルを選択するセレクトボックスを作成することができることを確認します。
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/公開レベル#公開レベル配列取得
     */
    public function test_PublicLevel_labels_blade()
    {

        // 実行
        $view = $this->blade(
            '<select id="PublicLevel" name="public_level">
                @foreach (PublicLevel::labels() as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>'
        );

        // 評価
        $view->assertSeeInOrder([
            '<option value="0">自分</option>',
            '<option value="2">友達</option>',
            '<option value="5">会員</option>',
            '<option value="10">全員</option>',
        ], false);
    }
}
