<?php

namespace Feeldee\Framework\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * レコードをあらわすモデル
 */
class Record extends Model
{
    use HasFactory, SetUser;

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['recorder', 'value'];

    /**
     * 配列に追加する属性
     * 
     * @var array
     */
    protected $appends = ['recorder', 'content'];

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['recorder', 'content', 'value'];

    /**
     * レコードを登録しているレコーダを取得
     *
     * @return Recorder
     */
    protected function recorder(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->belongsTo(Recorder::class, 'recorder_id')->get()->first(),
            set: fn($value) => [
                'recorder_id' => $value == null ? null : $value->id
            ]
        );
    }

    /**
     * レコードのコンテンツ取得
     *
     * @return Content
     */
    protected function content(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->belongsTo(Relation::getMorphedModel($this->recorder->type), 'content_id')->get()->first(),
            set: fn($value) => [
                'content_id' => $value == null ? null : $value->id
            ]
        );
    }
}
