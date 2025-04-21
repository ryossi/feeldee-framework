<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * コンテンツをあらわすベースモデル
 */
abstract class Content extends Model
{
    use HasFactory, SetUser,  AccessCounter;

    /**
     * コンテンツ種別
     * 
     * @return string コンテンツ種別
     */
    abstract public static function type();

    /**
     * コンテンツ所有プロフィール
     *
     * @return BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * コンテンツ公開フラグ
     *
     * @return bool
     */
    protected function getIsPublicAttribute(): bool
    {
        return $this->attributes['is_public'] ?? false;
    }

    /**
     * 公開
     * 
     * コンテンツを公開します。
     * 
     * @return void
     */
    public function doPublic(): void
    {
        $this->is_public = true;
        $this->save();

        $this->afterPublic();
    }

    /**
     * 非公開
     * 
     * コンテンツを非公開にします。
     * 
     * @return void
     */
    public function doPrivate(): void
    {
        $this->is_public = false;
        $this->save();

        $this->afterPrivate();
    }

    /**
     * コンテンツ公開後処理
     */
    protected function afterPublic(): void {}


    /**
     * コンテンツ非公開後処理
     */
    protected function afterPrivate(): void {}

    /**
     * コンテンツ公開レベル
     *
     * @return Attribute
     */
    protected function publicLevel(): Attribute
    {
        $setter = function ($value, $attributes) {
            $after = $value instanceof PublicLevel ? $value : PublicLevel::from($value);
            if (array_key_exists('public_level', $attributes)) {
                $before = $attributes['public_level'] instanceof PublicLevel ? $attributes['public_level'] : PublicLevel::from($attributes['public_level']);
                if ($before !== $after) {
                    $this->changePublicLevel($before, $after);
                }
            }
            return [
                'public_level' => $after
            ];
        };

        return Attribute::make(
            get: fn($value) => !is_null($value) ? ($value instanceof PublicLevel ? $value : PublicLevel::from($value)) : PublicLevel::Private,
            set: fn($value, $attributes) => $setter($value, $attributes)
        );
    }

    /**
     * 公開レベルが変更された場合の処理を記述します。
     * 
     * @param ?PublicLevel $before 変更前
     * @param PublicLevel $after 変更後
     */
    protected function changePublicLevel(PublicLevel $before, PublicLevel $after): void {}

    /**
     * コンテンツカテゴリ
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ========================== ここまで整理ずみ ==========================

    /**
     * 省略テキストを取得します。
     * 
     * @param int $width 文字数
     * @param string $trim_marker 省略文字 
     * @param ?string $encoding エンコード
     */
    public function textTruncate(int $width, string $trim_marker, ?string $encoding)
    {
        return mb_strimwidth($this->text, 0, $width, $trim_marker, $encoding);
    }

    /**
     * このコンテンツのレコーダリスト
     */
    public function recorders()
    {
        return $this->profile->recorders()->ofType($this->type());
    }

    /**
     * レコーダ名を指定してコンテンツのレコーダプロキシを取得します。
     * 
     * @param string $name レコーダ名
     * @return mixed レコーダプロキシ、存在しない場合はnull
     */
    public function recorder(string $name): mixed
    {
        // レコーダ存在チェク
        $recorder = $this->recorders()->ofName($name)->first();
        if ($recorder === null) {
            return null;
        }

        // コンテンツレコーダプロキシ
        return new class($recorder, $this)
        {
            private $recorder;

            private $content;

            function __construct($recorder, $content)
            {
                $this->recorder = $recorder;
                $this->content = $content;
            }

            public function record(mixed $value): ?Record
            {
                return $this->recorder->record($this->content, $value);
            }
        };
    }

    /**
     * このコンテンツのレコードリスト
     */
    public function records()
    {
        return $this->hasMany(Record::class, 'content_id')
            ->join('recorders', 'records.recorder_id', 'recorders.id')
            ->select('records.*')
            ->orderBy('order_number');
    }

    /**
     * JsonArrayからレコードリストを再編成します。
     * JsonArrayの構造
     * [
     *     {
     *         'recorder' => {
     *             'name' => 'レコーダ名'
     *         }
     *         'value' => 'レコード値'
     *     },
     *     ...
     * ]
     * 
     * @param mixed $values レコード値リストのJsonArray
     */
    public function createOrReplaceManyRecords(mixed $values): void
    {
        if (!is_array($values)) {
            return;
        }

        // 新しいレコードの追加
        foreach ($values as $value) {

            // レコーダ存在チェク
            $recorder = $this->recorder($value['recorder']['name']);
            if ($recorder === null) {
                throw new ApplicationException('RecorderNotFound', 70001, ['name' => $value['recorder']['name']]);
            }

            // レコード記録
            $recorder->record($value['value']);
        }
    }

    /**
     * このコンテンツのタグリスト
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable')->withTimestamps();
    }

    /**
     * タグ名を指定してコンテンツをタグ付けします。
     * $valueが文字列の場合は、既存のタグリストに追加します。
     * $valueが配列の場合は、既存のタグリストを置換します。
     * $valueがnullまたは空文字の場合は、既に割り当てられているタグリストを全て解除します。
     * 
     * @param  mixed $value
     * @param bool $ignoreNotExists 存在しないタグを無視する場合true（デフォルトはfalse）
     * @return Collection タグ名リスト
     */
    public function taggedByName(mixed $value, bool $ignoreNotExists = false): Collection
    {
        if (is_null($value) || $value == '' || $value == array()) {
            $this->tags()->detach();
        } else if (is_string($value)) {
            // タグ取得
            $tag = $this->profile->tags()->ofType($this->type())->ofName($value)->first();
            if ($tag == null) {
                throw new ApplicationException('TagNotFound', 72002, ['name' => $value]);
            }
            $this->tags()->attach($tag->id, ['created_by' => Auth::id(), 'updated_by' => Auth::id()]);
        } else {
            $ids = array();
            foreach ($value as $name) {
                // タグ取得
                $tag = $this->profile->tags()->ofType($this->type())->ofName($name)->first();
                if ($tag == null) {
                    throw new ApplicationException('TagNotFound', 72002, ['name' => $name]);
                }
                $ids[$tag->id] = ['created_by' => Auth::id(), 'updated_by' => Auth::id()];
            }
            $this->tags()->sync($ids);
        }

        return $this->tags()->get(['name']);
    }

    /**
     * コンテンツが閲覧可能か判定します。
     * コンテンツが閲覧可能かどうかは、コンテンツの公開レベルが閲覧者の最小公開レベル以上かどうかで決定されます。
     * 
     * コンテンツが未公開・・・閲覧不可
     * コンテンツが公開済み、かつ公開レベルが「自分」・・・閲覧者が自分自身の場合のみ閲覧可能
     * コンテンツが公開済み、かつ公開レベルが「友達」・・・閲覧者がコンテンツを所有するプロフィールの友達リストに含まれる場合のみ閲覧可能
     * コンテンツが公開済み、かつ公開レベルが「会員」・・・閲覧者が特定できている（null以外）場合のみ閲覧可能
     * コンテンツが公開済み、かつ公開レベルが「全員」・・・閲覧者が未特定（null）の場合でも閲覧可能
     * 
     * @param ?Profile 閲覧者（未特定の場合null）
     * @return bool 閲覧可能な場合true、閲覧不可の場合false
     */
    public function isView(?Profile $viewer): bool
    {
        if (!$this->isPublic()) {
            // コンテンツが未公開の場合、閲覧不可
            return false;
        }

        // 公開レベルと最小公開レベルを比較
        return $this->public_level->value >= $this->profile->minPublicLevel($viewer)->value;
    }

    /**
     * コンテンツのコメントリストを取得します。
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * コンテンツ閲覧履歴リストを取得します。
     */
    public function viewHistories()
    {
        return $this->morphMany(ContentViewHistory::class, 'content');
    }

    /**
     * タイトルを条件に含むようにクエリのスコープを設定
     */
    public function scopeOfTitle($query, ?string $title, SqlLikeBuilder $like = SqlLikeBuilder::All)
    {
        $like->build($query, 'title', $title);
    }

    /**
     * コンテンツをタイトル順に並び替えるクエリのスコープを設定
     */
    public function scopeOrderTitle($query, string $direction = 'asc')
    {
        return $query->orderBy('title', $direction);
    }

    /**
     * 公開済み、かつ公開レベルに応じて公開範囲を制御するようにクエリのスコープを設定
     */
    public function scopePublic($query)
    {
        $table = (new $this)->getTable();
        $query->where($table . '.is_public', true);
        $query->where(function (Builder $query) use ($table) {
            // 公開レベル「全員」
            $query->orWhere($table . '.public_level', PublicLevel::Public);
            // 公開レベル「会員」
            $query->orWhere(function (Builder $query) use ($table) {
                $query->where($table . '.public_level', PublicLevel::Member)
                    ->whereRaw('1 = ?', [!is_null(Auth::user()?->profile)]);
            });
            // 公開レベル「友達」
            // TODO::友達機能未実装
            $query->orWhere(function (Builder $query) use ($table) {
                $query->where($table . '.public_level', PublicLevel::Friend)
                    ->where($table . '.profile_id', Auth::user()?->profile->id);
            });
            // 公開レベル「自分」
            $query->orWhere(function (Builder $query) use ($table) {
                $query->where($table . '.public_level', PublicLevel::Private)
                    ->where($table . '.profile_id', Auth::user()?->profile->id);
            });
        });
        return $query;
    }

    protected $order_column = null;

    /**
     * コンテンツを最新のものから並び替えるクエリのスコープを設定
     */
    public function scopeOrderLatest($query)
    {
        return $query->latest($this->order_column);
    }

    /**
     * コンテンツを古いものから並び替えるクエリのスコープを設定
     */
    public function scopeOrderOldest($query)
    {
        return $query->oldest($this->order_column);
    }

    /**
     * 最新(desc|latest)または古いもの(asc|oldest)を指定してコンテンツを並び替えるクエリのスコープを設定
     */
    public function scopeOrderDirection($query, string $direction = 'asc')
    {
        if ($direction == 'desc' || $direction == 'latest') {
            $query->latest($this->order_column);
        } else if ($direction == 'asc' || $direction == 'oldest') {
            $query->oldest($this->order_column);
        }
    }

    /**
     * カテゴリを条件に含むようにクエリのスコープを設定
     *
     * @param Category|string|null $category カテゴリ条件
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
