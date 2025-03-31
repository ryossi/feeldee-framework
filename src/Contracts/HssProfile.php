<?php

namespace Feeldee\Framework\Contracts;

use Feeldee\Framework\Models\Profile;

interface HssProfile
{
    /**
     * ログインユーザのプロフィールを取得します。
     * 
     * @return Profile プロフィール
     */
    function getProfile(): Profile;
}
