<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Exceptions\ApplicationException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCategory
{
    protected static function bootedCategory(Post $post)
    {
        if (array_key_exists('category', $post->attributes)) {
            if ($post->category instanceof Category) {
                // カテゴリオブジェクトを直接指定している場合
                $post->category_id = $post->category->id;
            } else if (is_null($post->category) || $post->category === '') {
                // nullを設定した場合は、カテゴリをクリア
                $post->category_id = null;
            } else {
                $obj = null;
                if (is_int($post->category)) {
                    // カテゴリIDで検索
                    $obj = $post->profile->categories()->find($post->category);
                }
                if (!$obj) {
                    // カテゴリ名で検索
                    $obj = $post->profile->categories()->of($post->type())->name($post->category)->first();
                }
                if ($obj instanceof Category) {
                    $post->category_id = $obj->id;
                }
            }
            unset($post['category']);
        }
    }

    protected static function bootedProfile(Post $post)
    {
        if (!is_null($post->category) && $post->profile->id !== $post->category->profile->id) {
            // カテゴリ所有プロフィールと投稿者プロフィールが一致しない場合
            throw new ApplicationException(80001);
        }
    }

    protected static function bootedType(Post $post)
    {
        if (!is_null($post->category) && $post->category->type !== $post::type()) {
            // カテゴリ種別と投稿種別が一致しない場合
            throw new ApplicationException(80002);
        }
    }

    public static function bootHasCategory()
    {
        static::creating(function (Post $post) {
            // 投稿カテゴリ
            static::bootedCategory($post);
            // 投稿種別
            static::bootedType($post);
            // 投稿者プロフィール
            static::bootedProfile($post);
        });

        static::updating(function (Post $post) {
            // 投稿カテゴリ
            static::bootedCategory($post);
            // 投稿種別
            static::bootedType($post);
            // 投稿者プロフィール
            static::bootedProfile($post);
        });
    }

    /**
     * 投稿カテゴリ
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * カテゴリを条件に含むようにクエリのスコープを設定
     *
     * @param Category|string|null $category カテゴリまたはカテゴリ名
     */
    public function scopeOfCategory($query, Category|string|null $category)
    {
        if (!is_null($category)) {
            if ($category instanceof Category) {
                $query->where('category_id', $category->id);
            } else {
                $table = (new $this)->getTable();
                $query->leftJoin('categories', "$table.category_id", '=', 'categories.id')
                    ->select("$table.*")
                    ->where('categories.name', $category);
            }
        } else {
            $query->whereNull('category_id');
        }

        return $query;
    }
}
