<?php

namespace Feeldee\Framework\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 投稿アイテムをあらわすモデル
 */
class PostedItem extends Model
{
    use HasFactory, SetUser;

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['post', 'item', 'label'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['id', 'post', 'item', 'label'];

    /**
     * 変換する属性
     */
    protected $casts = [
        'commented_at' => 'datetime',
        'is_public' => 'boolean',
    ];

    /**
     * モデルの「起動」メソッド
     */
    protected static function booted(): void
    {
        static::addGlobalScope('defaultSort', function (Builder $builder) {
            $table = $builder->getModel()->getTable();
            $builder->leftJoin('items', 'items.id', '=', $table . '.item_id')
                ->select($table . '.*')
                ->orderBy($table . '.order_number')
                ->orderBy('items.order_number')
                ->orderByDesc('items.posted_at');
        });

        static::creating(function (self $model) {
            if (key_exists('post', $model->attributes) && $model->attributes['post'] instanceof Post && isset($model->attributes['post']->id)) {
                $model->post_id = $model->attributes['post']->id;
                $model->post_type = get_class($model->attributes['post'])::type();
                unset($model->attributes['post']);
            }
            if (key_exists('item', $model->attributes) && $model->attributes['item'] instanceof Item && isset($model->attributes['item']->id)) {
                $model->item_id = $model->attributes['item']->id;
                unset($model->attributes['item']);
            }
        });
    }

    /**
     * 投稿
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function post()
    {
        return $this->morphTo();
    }

    /**
     * アイテム
     *
     * @return BelongsTo
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
