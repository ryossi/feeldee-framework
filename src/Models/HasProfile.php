<?php

namespace Feeldee\Framework\Models;

use Illuminate\Database\Eloquent\Model;

trait HasProfile
{
    public static function bootHasProfile()
    {
        static::deleting(function (Model $cmodel) {
            if (config('feeldee.profile.user_relation_type') === 'composition') {
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
        if (config('feeldee.profile.default') === 'latest') {
            // デフォルトのプロファイル取得方法がlatestの場合
            return $this->hasOne(Profile::class, 'user_id')->latestOfMany();
        } elseif (config('feeldee.profile.default') === 'oldest') {
            // デフォルトのプロファイル取得方法がoldestの場合
            return $this->hasOne(Profile::class, 'user_id')->oldestOfMany();
        } else {
            // デフォルトのプロファイル取得方法が設定されていない場合はnullを返す
            return null;
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
