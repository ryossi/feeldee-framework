<?php

namespace Feeldee\Framework\Models;

use App;
use Feeldee\Framework\Exceptions\ApplicationException;
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
    protected $fillable = ['content', 'content_id', 'value'];

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
            // レコーダ所有プロフィールがコンテンツ所有プロフィールと一致しているかチェック
            if ($model->content->profile->id !== $model->recorder->profile->id) {
                throw new ApplicationException(73007);
            }
            // レコーダタイプとコンテンツ種別が一致しているかチェック
            if ($model->content::type() !== $model->recorder->type) {
                throw new ApplicationException(73008);
            }
            // レコード対象コンテンツにコンテンツオブジェクが直接指定されている場合
            if (array_key_exists('content', $model->attributes)) {
                // コンテンツIDに変換
                $model->content_id = $model->content->id;
                unset($model['content']);
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
     * コンテンツ
     *
     * @return BelongsTo
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Relation::getMorphedModel($this->recorder->type), 'content_id');
    }
}
