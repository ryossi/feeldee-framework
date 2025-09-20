<?php

namespace Feeldee\Framework\Models;

use Carbon\CarbonImmutable;
use Feeldee\Framework\Facades\FDate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 投稿をあらわすベースモデル
 */
abstract class Post extends Model
{
    use HasFactory, HasCategory, HasTag, HasRecord, Required, StripTags, SetUser;

    /**
     * 投稿種別
     * 
     * @return string 投稿種別
     */
    abstract public static function type();

    /**
     * 投稿者プロフィール
     *
     * @return BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * 投稿公開フラグ
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
     * 投稿を公開します。
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
     * 投稿を非公開にします。
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
     * 投稿公開後処理
     */
    protected function afterPublic(): void {}


    /**
     * 投稿非公開後処理
     */
    protected function afterPrivate(): void {}

    /**
     * 投稿公開レベル
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
     * コメントリスト
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    protected $order_column = null;

    /**
     * 最新のものから並び替えるローカルスコープ
     */
    public function scopeOrderLatest($query): void
    {
        $query->latest($this->order_column);
    }

    /**
     * 古いものから並び替えるローカルスコープ
     */
    public function scopeOrderOldest($query): void
    {
        $query->oldest($this->order_column);
    }

    /**
     * 最新(latest|desc)または古いもの(oldest|asc)の文字列を直接指定してソートするローカルスコープ
     */
    public function scopeOrderDirection($query, string $direction = 'asc'): void
    {
        if ($direction == 'desc' || $direction == 'latest') {
            $query->latest($this->order_column);
        } else if ($direction == 'asc' || $direction == 'oldest') {
            $query->oldest($this->order_column);
        }
    }

    /**
     * 投稿者プロフィールのニックネームで絞り込むためのローカルスコープ
     */
    public function scopeBy(Builder $query, $nickname): void
    {
        $query->whereHas('profile', fn($q) => $q->where('nickname', $nickname));
    }

    /**
     * 投稿日時で絞り込むためのローカルスコープ
     */
    public function scopeAt(Builder $query, $datetime): void
    {
        if (is_string($datetime)) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}(?: \d{2}(?::\d{2})?)?$/', $datetime)) {
                // 時刻以下が省略されている場合は、前方一致検索
                $query->where('posted_at', 'like', $datetime . '%');
            } else {
                $query->where('posted_at', $datetime);
            }
        } elseif ($datetime instanceof CarbonImmutable) {
            // CarbonImmutableインスタンスの場合は、フォーマットして文字列に変換
            $datetime = $datetime->format('Y-m-d H:i:s');
            $query->where('posted_at', $datetime);
        }
    }

    /**
     * 投稿日時の範囲を指定して取得するためのローカルスコープ
     */
    public function scopeBetween(Builder $query, $start, $end): void
    {
        $query->whereBetween('posted_at', [FDate::format($start, '+00:00:00'), FDate::format($end, '+23:59:59')]);
    }

    /**
     * 投稿日時の未満で範囲指定して取得するためのローカルスコープ
     */
    public function scopeBefore(Builder $query, $datetime): void
    {
        $query->where('posted_at', '<', FDate::format($datetime, '+00:00:00'));
    }

    /**
     * 投稿日時のより先で範囲指定して取得するためのローカルスコープ
     */
    public function scopeAfter(Builder $query, $datetime): void
    {
        $query->where('posted_at', '>', FDate::format($datetime, '+00:00:00'));
    }

    /**
     * 投稿日時の以前で範囲指定して取得するためのローカルスコープ
     */
    public function scopeBeforeEquals(Builder $query, $datetime): void
    {
        $query->where('posted_at', '<=', FDate::format($datetime, '+00:00:00'));
    }

    /**
     * 投稿日時の以降で範囲指定して取得するためのローカルスコープ
     */
    public function scopeAfterEquals(Builder $query, $datetime): void
    {
        $query->where('posted_at', '>=', FDate::format($datetime, '+00:00:00'));
    }

    /**
     * 公開されたコンテンツのみ取得するためのローカルスコープ
     */
    public function scopePublic($query): void
    {
        $query->where('is_public', true);
    }

    /**
     * 非公開のコンテンツのみ取得するためのローカルスコープ
     */
    public function scopePrivate($query): void
    {
        $query->where('is_public', false);
    }

    /**
     * 閲覧可能なコンテンツの絞り込むためのローカルスコープ
     * 
     * @see https://github.com/ryossi/feeldee-framework/wiki/公開レベル
     */
    public function scopeViewable(Builder $query, $viewer = null): void
    {
        // 閲覧プロフィールの特定
        if (!($viewer instanceof Profile)) {
            // プロフィールが関連付けされているユーザEloquentモデルが指定された場合
            if ($viewer && method_exists($viewer, 'profile')) {
                // デフォルトプロフィールに基づき閲覧可否が判断されるため、profile()メソッドを呼び出してプロフィールを取得
                $viewer = $viewer->profile;
            } else if (is_string($viewer)) {
                // $viewerがstringの場合は、プロフィールニックネームとする
                $viewer = Profile::of($viewer)->first();
            } else {
                // デフォルトプロフィールが特定できない場合は、匿名ユーザー(null)として扱う
                $viewer = null;
            }
        }

        $query->public()->where(function (Builder $query) use ($viewer) {
            // 公開レベル「全員」
            $query->orWhere('public_level', PublicLevel::Public);
            if (!is_null($viewer)) {
                // 公開レベル「会員」
                $query->orWhere('public_level', PublicLevel::Member);
                // 公開レベル「友達」
                // 友達機能: viewerが投稿者のfriendsテーブルに含まれているか判定
                $query->orWhere(function (Builder $q) use ($viewer) {
                    $q->where('public_level', PublicLevel::Friend)
                        ->where(function ($friendQuery) use ($viewer) {
                            // 自分自身の場合も含める
                            $friendQuery->where('profile_id', $viewer->id)
                                ->orWhereHas('profile.friends', function ($fq) use ($viewer) {
                                    $fq->where('friend_id', $viewer->id);
                                });
                        });
                });
                // 公開レベル「自分」
                $query->orWhere(function (Builder $q) use ($viewer) {
                    $q->where('public_level', PublicLevel::Private)
                        ->where('profile_id', $viewer->id);
                });
            }
        });
    }

    /**
     * 取得したコンテンツそのものが閲覧可能かどうかを判断します。
     * 
     * @see https://github.com/ryossi/feeldee-framework/wiki/公開レベル
     *
     * @param mixed $viewer 閲覧者（未特定の場合null）
     * @return bool 閲覧可能な場合true、閲覧不可の場合false
     */
    public function isViewable(mixed $viewer = null): bool
    {
        if (!$this->is_public) {
            // 投稿が未公開の場合、閲覧不可
            return false;
        }

        if ($this->public_level === PublicLevel::Public) {
            // 投稿が公開レベル「全員」の場合、常に閲覧可能
            return true;
        }

        // 閲覧プロフィールの特定
        if (!($viewer instanceof Profile)) {
            // プロフィールが関連付けされているユーザEloquentモデルが指定された場合
            if ($viewer && method_exists($viewer, 'profile')) {
                // デフォルトプロフィールに基づき閲覧可否が判断されるため、profile()メソッドを呼び出してプロフィールを取得
                $viewer = $viewer->profile;
            } else if (is_string($viewer)) {
                // $viewerがstringの場合は、プロフィールニックネームとする
                $viewer = Profile::of($viewer)->first();
            } else {
                // デフォルトプロフィールが特定できない場合は、匿名ユーザー(null)として扱う
                return false;
            }
        }

        if ($this->public_level === PublicLevel::Member) {
            // 投稿が公開レベル「会員」の場合、viewerが特定されている場合に閲覧可能
            return !is_null($viewer);
        }

        if ($this->public_level === PublicLevel::Friend) {
            // 投稿が公開レベル「友達」の場合、自分自身もしくはviewerが友達である必要がある
            return $viewer->id === $this->profile_id || $this->profile->isFriend($viewer);
        }

        if ($this->public_level === PublicLevel::Private) {
            // 投稿が公開レベル「自分」の場合、viewerが投稿者自身である必要がある
            return $viewer->id === $this->profile_id;
        }

        return false;
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
     * タイトルを条件に含むようにクエリのスコープを設定
     */
    public function scopeOfTitle($query, ?string $title, SqlLikeBuilder $like = SqlLikeBuilder::All)
    {
        $like->build($query, 'title', $title);
    }

    /**
     * 投稿をタイトル順に並び替えるクエリのスコープを設定
     */
    public function scopeOrderTitle($query, string $direction = 'asc')
    {
        return $query->orderBy('title', $direction);
    }
}
