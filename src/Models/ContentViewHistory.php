<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Observers\ContentViewHistoryObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

/**
 * コンテンツ閲覧履歴をあらわすモデル
 * 
 */
#[ObservedBy([ContentViewHistoryObserver::class])]
class ContentViewHistory extends Model
{
    use HasFactory, SetUid;

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['viewed_at'];

    /**
     * 閲覧対象コンテンツに紐づくプロフィール
     *
     * @return Attribute
     */
    protected function profile(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->belongsTo(Profile::class, 'profile_id')->get()->first(),
            set: fn($value) => [
                'profile_id' => $value == null ? null : $value->id
            ]
        );
    }

    /**
     * 閲覧対象コンテンツ
     */
    public function content()
    {
        return $this->morphTo();
    }
}
