<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Casts\Html;
use Feeldee\Framework\Casts\URL;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 場所をあらわすモデル
 * 
 */
class Location extends Content
{
    /**
     * コンテンツ種別
     * 
     * @return string
     */
    public static function type()
    {
        return 'location';
    }

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['profile', 'public_level', 'category', 'category_id', 'tags', 'title', 'value', 'latitude', 'longitude', 'zoom', 'thumbnail'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['id', 'profile', 'is_public', 'public_level', 'category', 'title', 'latitude', 'longitude', 'zoom', 'thumbnail'];

    /**
     * キャストする必要のある属性
     *
     * @var array
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'zoom' => 'integer',
        'value' => HTML::class,
        'thumbnail' => URL::class,
    ];

    /**
     * 必須にする属性
     * 
     * @var array
     */
    protected $required = [
        'title' => 40001,
    ];

    /**
     * 文字列から HTML および PHP タグを取り除く属性
     * 
     * @var array
     */
    protected $strip_tags = ['value' => 'text'];

    // ========================== ここまで整理済み ==========================

    /**
     * 場所の中央の位置情報（緯度,経度のカンマ区切り文字列）
     */
    protected function center(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => implode(',', [$attributes['latitude'], $attributes['longitude']]),
        );
    }

    /**
     * 場所の位置情報（緯度,経度,縮尺のカンマ区切り文字列）
     */
    protected function position(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => implode(',', [$attributes['latitude'], $attributes['longitude'], $attributes['zoom']]),
        );
    }

    /**
     * 緯度（精度に合わせて四捨五入）
     */
    protected function latitude(): Attribute
    {
        return Attribute::make(
            get: fn($value) => round($value, config('feeldee.location.precision.latitude', 7)),
        );
    }

    /**
     * 経度（精度に合わせて四捨五入）
     */
    protected function longitude(): Attribute
    {
        return Attribute::make(
            get: fn($value) => round($value, config('feeldee.location.precision.longitude', 7)),
        );
    }

    /**
     * 地図データ（緯度,経度,縮尺のJSONデータ
     */
    protected function map(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => json_encode(
                new class($this->latitude, $this->longitude, $this->zoom)
                {
                    public function __construct(public $latitude, public $longitude, public $zoom) {}
                }
            ),
            set: fn($value) => array_filter(json_decode($value, true), function ($key) {
                return in_array($key, $this->fillable);
            }, ARRAY_FILTER_USE_KEY)
        );
    }

    /**
     * 東西の経度と南北の緯度の矩形に囲まれた場所のみを含むようにクエリのスコープを設定
     * 
     * @param float $east 東経
     * @param float $west 西経
     * @param float $south 南緯
     * @param float $north 北緯
     */
    public function scopeRectangle($query, float $east, float $west, float $south, float $north): void
    {
        $query->where('latitude', '<=', $north)
            ->where('latitude', '>=', $south)
            ->where('longitude', '<=', $east)
            ->where('longitude', '>=', $west);
    }

    /**
     * 指定した緯度のみを含むようにクエリのスコープを設定
     *
     * @param float $latitude 緯度
     */
    public function scopeLat($query, float $latitude): void
    {
        // 精度を揃える
        $latitude = round($latitude, config('feeldee.location.precision.latitude', 7));
        $query->where('latitude', $latitude);
    }

    /**
     * 指定した経度のみを含むようにクエリのスコープを設定
     *
     * @param float $longitude 経度
     */
    public function scopeLng($query, float $longitude): void
    {
        // 精度を揃える
        $longitude = round($longitude, config('feeldee.location.precision.longitude', 7));
        $query->where('longitude', $longitude);
    }

    /**
     * この場所に属する投稿
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'timelines', 'location_id', 'post_id');
    }
}
