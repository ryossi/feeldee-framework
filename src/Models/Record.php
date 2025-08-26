<?php

namespace Feeldee\Framework\Models;

use App;
use Carbon\Carbon;
use Feeldee\Framework\Exceptions\ApplicationException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * レコードをあらわすモデル
 */
class Record extends Model
{
    use HasFactory, SetUser;

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['content', 'recordable_id', 'value'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['recorder', 'content', 'value'];

    /**
     * 配列に追加する属性
     * 
     * @var array
     */
    protected $appends = ['recorder', 'content'];

    /**
     * モデルの「起動」メソッド
     */
    protected static function booted(): void
    {
        static::addGlobalScope('order_number', function ($builder) {
            $builder->join('recorders', 'records.recorder_id', 'recorders.id')
                ->select('records.*')
                ->orderBy('order_number');
        });

        static::saving(function (Self $model) {
            // レコーダ所有プロフィールが投稿者プロフィールと一致しているかチェック
            if ($model->content->profile->id !== $model->recorder->profile->id) {
                throw new ApplicationException(73007);
            }
            // レコーダタイプと投稿種別が一致しているかチェック
            if ($model->content::type() !== $model->recorder->type) {
                throw new ApplicationException(73008);
            }
            // レコード対象投稿に投稿オブジェクが直接指定されている場合
            if (array_key_exists('post', $model->attributes)) {
                // 投稿IDに変換
                $model->recordable_id = $model->post->id;
                unset($model['post']);
            }
            // 設定時にレコードデータ型に従って型チェック
            $type = $model->recorder->data_type;
            $value = $model->attributes['value'];
            $check = true;
            if ($type == 'string') {
                $check = is_string($value);
            } elseif ($type == 'int' || $type == 'integer') {
                $check = is_int($value);
            } elseif ($type == 'float') {
                $check = is_float($value);
            } elseif ($type == 'double') {
                $check = is_double($value);
            } elseif ($type == 'bool' || $type == 'boolean') {
                $check = is_bool($value);
            } elseif ($type == 'date' || $type == 'datetime') {
                $check = strtotime($value) !== false;
            }
            if (!$check) {
                throw new ApplicationException(73004);
            }
            // レコードデータ型がdateの場合は、時刻は省略
            if ($type == 'date') {
                $model->attributes['value'] = Carbon::parse($value)->format('Y-m-d');
            }
            // レコードデータ型がdatetime、かつ時刻が省略された場合は、00:00:00を補完
            if ($type == 'datetime' && !str_contains($value, ':')) {
                $model->attributes['value'] = Carbon::parse($value)->format('Y-m-d H:i:s');
            }
        });
    }

    /**
     * レコーダ
     *
     * @return BelongsTo
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(Recorder::class, 'recorder_id');
    }

    /**
     * 投稿
     *
     * @return BelongsTo
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Relation::getMorphedModel($this->recorder->type), 'recordable_id');
    }

    protected function value(): Attribute
    {
        $getter = function ($value, $attributes) {
            $type = $this->recorder->data_type;
            if ($type == 'string') {
                return strval($value);
            }
            if ($type == 'int' || $type == 'integer') {
                return intval($value);
            }
            if ($type == 'float') {
                return floatval($value);
            }
            if ($type == 'double') {
                return doubleval($value);
            }
            if ($type == 'bool' || $type == 'boolean') {
                return boolval($value);
            }
            if ($type == 'date' || $type == 'datetime') {
                return Carbon::parse($value);
            }
        };
        return Attribute::make(
            get: fn($value, $attributes) => $getter($value, $attributes),
        );
    }
}
