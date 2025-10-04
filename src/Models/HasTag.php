<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Exceptions\ApplicationException;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
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
                    $ids = array();
                    if (!is_array($model->_tags) && !($model->_tags instanceof \Illuminate\Support\Collection)) {
                        if (is_string($model->_tags)) {
                            $model->_tags = explode(',', $model->_tags);
                        } else {
                            $model->_tags = [$model->_tags];
                        }
                    }
                    foreach ($model->_tags as $tag) {
                        if (is_int($tag)) {
                            $tag = Tag::find($tag);
                        } else if (is_string($tag)) {
                            $tag = $model->profile->tags()->of($model::type())->name($tag)->first();
                        }
                        if ($tag == null || !($tag instanceof Tag)) {
                            continue;
                        }
                        if ($tag->profile_id !== $model->profile_id) {
                            // タグ所有プロフィールと投稿者プロフィールが一致しない場合
                            throw new ApplicationException(80003);
                        }
                        if ($tag->type !== $model::type()) {
                            // タグタイプと投稿種別が一致しない場合
                            throw new ApplicationException(80004);
                        }
                        $ids[$tag->id] = [
                            'taggable_type' => $model::type(),
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
        $tagsPivot = new class extends MorphPivot {
            use SetUser;
            protected $table = 'taggables';
        };
        return $this->morphToMany(Tag::class, 'taggable')->using($tagsPivot)->withTimestamps();
    }
}
