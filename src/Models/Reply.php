<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Observers\ReplyObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

/**
 * 返信をあらわすモデル
 */
#[ObservedBy([ReplyObserver::class])]
class Reply extends Model
{
    use HasFactory, SetUser;

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['body', 'replyer', 'replied_at'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['id', 'body', 'replied_at', 'is_public', 'replyer', 'nickname'];

    /**
     * 配列に追加する属性
     * 
     * @var array
     */
    protected $appends = ['replyer', 'nickname'];

    /**
     * 返信対象
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * 変換する属性
     */
    protected $casts = [
        'replied_at' => 'datetime'
    ];

    /**
     * 返信者
     *
     * @return Attribute
     */
    protected function replyer(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['replyer_profile_id'] ? $this->belongsTo(Profile::class, 'replyer_profile_id')->get()->first() : null,
            set: fn($value) => [
                'replyer_profile_id' => $value instanceof Profile ? $value->id : null,
                'replyer_nickname' => $value instanceof Profile ? $value->nickname : $value,
            ]
        );
    }

    /**
     * ニックネーム
     */
    protected function nickname(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $this->replyer instanceof Profile ? $this->replyer->nickname : $attributes['replyer_nickname'],
        );
    }
}
