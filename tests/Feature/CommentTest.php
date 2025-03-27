<?php

namespace Tests\Feature;

use Feeldee\Framework\Models\Profile;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * コメントの用語を担保するための機能テストです。
 * 
 * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント
 */
class CommentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * コメント日時
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時
     */
    public function test_コメント日時_コメント日時指定あり() {}

    /**
     * コメント日時
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#コメント日時
     */
    public function test_コメント日時_コメント日時指定なし() {}

    /**
     * 公開フラグ
     * 
     * @link https://github.com/ryossi/feeldee-framework/wiki/コメント#公開フラグ
     */
    public function test_コメント公開() {}
}
