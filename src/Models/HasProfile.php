<?php

namespace Feeldee\Framework\Models;

use Illuminate\Database\Eloquent\Model;

trait HasProfile
{
    public static function bootHasProfile()
    {
        static::deleting(function (Model $cmodel) {
            if (config(Profile::CONFIG_KEY_USER_RELATION_TYPE) === 'composition') {
                // 関連付けされた全てのプロフィールも同時に削除
                $cmodel->profiles()->delete();
            }
        });
    }

    /**
     * プロフィール
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile()
    {
        if (config(Profile::CONFIG_KEY_DEFAULT_ORDER) === 'latest') {
            // デフォルトのプロファイル取得方法がlatestの場合、最新のプロファイルを取得
            return $this->hasOne(Profile::class, 'user_id')->latestOfMany();
        } else {
            // その他全て最初のプロファイルを取得
            return $this->hasOne(Profile::class, 'user_id')->oldestOfMany();
        }
    }

    /**
     * プロフィールリスト
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function profiles()
    {
        return $this->hasMany(Profile::class, 'user_id');
    }
}
