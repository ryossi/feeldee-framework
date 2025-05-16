<?php

namespace Feeldee\Framework\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * コンテンツをあらわすベースモデル
 */
abstract class Content extends Model
{
    use HasFactory, HasCategory, HasTag, HasRecord, Required, StripTags, SetUser, AccessCounter;

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
}
