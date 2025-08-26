<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Casts\URL;
use Feeldee\Framework\Observers\PointObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

/**
 * ポイントをあらわすモデル
 * 
 */
#[ObservedBy([PointObserver::class])]
class Point extends Model
{
    use HasFactory, SetUser;

    protected $fillable = ['title', 'point_datetime', 'memo', 'latitude', 'longitude', 'point_type', 'image_src'];

    protected $appends = ['date', 'time'];

    protected $visible = ['id', 'title', 'point_datetime', 'date', 'time', 'memo', 'latitude', 'longitude', 'point_type', 'image_src'];

    /**
     * 投稿者プロフィール
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
     * ポイントに紐付く投稿
     */
    public function post()
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * 日付
     */
    protected function date(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['point_datetime'] ? date('Y-m-d', strtotime($attributes['point_datetime'])) : null,
        );
    }

    /**
     * 時刻
     */
    protected function time(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['point_datetime'] ? date('H:i', strtotime($attributes['point_datetime'])) : null,
        );
    }

    /**
     * 公開済みの投稿に関連のあるポイントのみを含むようにクエリのスコープを設定
     * 最小公開レベルが指定されている場合は、最小公開レベル以上の投稿に関連のあるポイントのみを含むようにクエリのスコープを設定
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param ?PublicLevel $minPublicLevel 最小公開レベル
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query, ?PublicLevel $minPublicLevel = null)
    {
        $query->join('posts', 'posts.id', 'points.post_id')->where('is_public', true);
        if ($minPublicLevel) {
            $query->where('public_level', '>=', $minPublicLevel);
        }

        return $query;
    }

    /**
     * 東西の経度と南北の緯度の矩形に囲まれたポイントのみを含むようにクエリのスコープを設定
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param float $east 東経
     * @param float $west 西経
     * @param float $south 南緯
     * @param float $north 北緯
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRectangle($query, float $east, float $west, float $south, float $north)
    {
        return $query->where('latitude', '<=', $north)
            ->where('latitude', '>=', $south)
            ->where('longitude', '<=', $east)
            ->where('longitude', '>=', $west)
            ->select('points.*');
    }

    /**
     * 投稿を最新のものから並び替えるクエリのスコープを設定
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderLatest($query)
    {
        return $query->orderBy('point_datetime', 'desc');
    }
}
