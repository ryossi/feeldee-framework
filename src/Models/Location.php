<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Casts\Html;
use Feeldee\Framework\Casts\URL;
use Feeldee\Framework\Facades\ImageText;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 場所をあらわすモデル
 * 
 */
class Location extends Content
{
    protected $fillable = ['profile', 'title', 'latitude', 'longitude', 'zoom', 'public_level', 'thumbnail'];

    protected $visible = ['id', 'title', 'latitude', 'longitude', 'zoom', 'is_public', 'thumbnail'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'zoom' => 'integer',
        'thumbnail' => URL::class,
        'value' => Html::class,
    ];

    protected const THUMBNAIL_UPLOAD_DIRECTORY = 'location';

    /**
     * モデルの「起動」メソッド
     */
    protected static function booted(): void
    {
        static::created(function (Location $location) {
            // サムネイルイメージアップロード
            if (ImageText::isImageText($location->thumbnail)) {
                $media = $location->profile->mediaBox->upload($location->thumbnail, $location->id, self::THUMBNAIL_UPLOAD_DIRECTORY);
                $location->thumbnail = $media;
                $location->save(['timestamps' => false]);
            }
        });

        static::updating(function (Location $location) {
            // サムネイルイメージアップロード
            if (ImageText::isImageText($location->thumbnail)) {
                $media = $location->profile->mediaBox->upload($location->thumbnail, $location->id, self::THUMBNAIL_UPLOAD_DIRECTORY);
                $location->thumbnail = $media;
            }
        });

        static::deleted(function (Location $location) {
            // サムネイルイメージ削除
            if ($location->thumbnail) {
                $media = $location->profile->mediaBox->find($location->thumbnail);
                $media->delete();
            }
        });
    }

    /**
     * 場所のタイプ文字列
     */
    public static function type()
    {
        return 'location';
    }

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
