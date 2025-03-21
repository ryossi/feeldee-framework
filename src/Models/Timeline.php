<?php

namespace Feeldee\Framework\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * タイムラインをあらわすモデル
 */
class Timeline extends Model
{
    use HasFactory, SetUser;

    protected $fillable = ['location_id', 'start_datetime', 'end_datetime'];

    protected $appends = ['start_time', 'end_time', 'location'];

    protected $visible = ['id', 'start_time', 'end_time', 'location'];

    /**
     * タイムラインに紐付く投稿
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * タイムラインに紐付く場所
     *
     * @return Attribute
     */
    protected function location(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->belongsTo(Location::class, 'location_id', 'id')->get()->first(),
            set: fn($value) => [
                'profile_id' => $value == null || !($value instanceof Location) ? null : $value->id
            ]
        );
    }

    /**
     * 開始時刻
     */
    protected function startTime(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['start_datetime'] ? date('H:i', strtotime($attributes['start_datetime'])) : null,
        );
    }

    /**
     * 終了時刻
     */
    protected function endTime(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => strtotime($attributes['end_datetime']) ? date('H:i', strtotime($attributes['end_datetime'])) : null,
        );
    }
}
