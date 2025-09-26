<?php

namespace Feeldee\Framework\Services;

use Feeldee\Framework\Models\Profile;

class UserService
{
    const KEY = 'UserService.Profile';

    /**
     * URLからユーザのプロフィールを取得します。
     * プロフィールが存在しない場合は、nullを返却します。
     * 取得したプロフィールはセッションに一時保存され、同一リクエスト間でプロフィールを共有できます。
     * ニックネームが指定された場合は、常に新しいプロフィールが検索されます。
     * 
     * @param string $nickname ニックネーム
     * @return ?Profile プロフィールまたはnull
     */
    public function profile(string $nickname = null): ?Profile
    {
        if ($nickname !== null) {
            $profile = Profile::of($nickname)->first();
            if ($profile !== null) {
                session()->flash(self::KEY, $profile);
            } else {
                session()->forget(self::KEY);
            }
        }
        $profile = session(self::KEY);
        return $profile;
    }

    public function __get($key)
    {
        if ($key === 'profile') {
            return $this->profile();
        }
        return null;
    }
}
