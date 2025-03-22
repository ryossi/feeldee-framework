<?php

namespace Feeldee\Framework\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 追跡をあらわすモデル
 * 
 */
class Track extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'uid';

    protected $keyType = 'string';

    /**
     * 一意の識別子を受け取るカラムの取得
     *
     * @return array
     */
    public function uniqueIds()
    {
        return ['uid'];
    }

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['ip_address', 'user_agent'];

    /**
     * トラッキング情報が存在するか確認します。
     * 
     * @return bool 存在する場合true、しない場合false
     */
    public static function existsByUid($uid): bool
    {
        return self::where('uid', $uid)->exists();
    }
}
