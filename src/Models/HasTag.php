<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Exceptions\ApplicationException;
use Illuminate\Support\Facades\Auth;

trait HasTag
{
    private $_tags = null;

    public static function bootHasTag()
    {
        static::saving(function (Self $model) {
            // タグリストにタグの配列またはコレクションが設定されている場合には、
            // ローカルタグリストに一時的に保存
            $model->_tags = $model->tags;
            unset($model['tags']);
        });

        static::deleting(function (Post $post) {
            // タグ付け解除
            $post->tags()->detach();
        });

        static::saved(function (Self $model) {
            if (is_null($model->_tags) || $model->_tags == '' || $model->_tags == array()) {
                $model->tags()->detach();
            } else {
                if (!empty($model->_tags) || $model->_tags->isNotEmpty()) {
                    // ローカル投稿リストを
                    $id = Auth::id();
                    $ids = array();
                    foreach ($model->_tags as $tag) {
                        if (is_int($tag)) {
                            $tag = Tag::find($tag);
                        } else if (is_string($tag)) {
                            $tag = $model->profile->tags()->ofType($model::type())->ofName($tag)->first();
                        }
                        if ($tag == null || !($tag instanceof Tag)) {
                            continue;
                        }
                        if ($tag->profile_id !== $model->profile_id) {
                            // タグ所有プロフィールと投稿者プロフィールが一致しない場合
                            throw new ApplicationException(72005);
                        }
                        if ($tag->type !== $model::type()) {
                            // タグタイプと投稿種別が一致しない場合
                            throw new ApplicationException(72006);
                        }
                        $ids[$tag->id] = [
                            'taggable_type' => $model::type(),
                            'created_by' => $id,
                            'updated_by' => $id
                        ];
                    }
                    $model->tags()->sync($ids);
                }
            }
            $model->_tags = null;
        });
    }

    /**
     * 投稿タグリスト
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable')->withTimestamps();
    }
}
