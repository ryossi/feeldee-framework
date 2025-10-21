<?php

namespace Feeldee\Framework\Models;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Facades\FDate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 返信をあらわすモデル
 */
class Reply extends Model
{
    use HasFactory, SetUser;

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['body', 'replyer', 'replyer_nickname', 'replied_at'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['id', 'body', 'replied_at', 'is_public', 'replyer', 'replyer_nickname'];

    /**
     * 変換する属性
     */
    protected $casts = [
        'replied_at' => 'datetime',
        'is_public' => 'boolean',
    ];

    /**
     * モデルの「起動」メソッド
     */
    protected static function booted(): void
    {
        // デフォルトの並び順は、返信日時降順
        static::addGlobalScope('order_number', function ($builder) {
            $builder->orderBy('replied_at', 'desc');
        });

        static::saving(function (self $model) {
            // 返信日時
            if (empty($model->replied_at)) {
                $model->replied_at = Carbon::now();
            }
            // 返信者プロフィール
            if (!empty($model->replyer) && $model->replyer instanceof Profile) {
                $model->replyer_profile_id = $model->replyer->id;
                unset($model->replyer);
            } elseif (empty($model->replyer_nickname)) {
                // 返信者ニックネームが指定されていない場合は、例外をスロー
                throw new ApplicationException(61001);
            }
        });

        static::creating(function (self $model) {
            // 返信所有者
            $model->profile_id = $model->comment->commentable->profile_id;
        });
    }

    /**
     * 返信対象
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * 返信者プロフィール
     *
     * @return BelongsTo
     */
    public function replyer(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'replyer_profile_id');
    }

    /**
     * 返信者ニックネーム
     * 
     * @return Attribute
     */
    protected function replyerNickname(): Attribute
    {
        return Attribute::make(
            get: fn($value) => empty($value) ? $this->replyer?->nickname : $value,
            set: fn($value) => [
                'replyer_nickname' => $value,
            ]
        );
    }

    /**
     * 返信公開フラグ
     * 
     * @return Attribute
     */
    protected function isPublic(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->comment?->is_public ? (boolval($value) ?? false) : false,
        );
    }

    /**
     * 公開
     * 
     * 返信を公開します。
     * 
     * @return void
     */
    public function doPublic(): void
    {
        $this->is_public = true;
        $this->save();
    }

    /**
     * 非公開
     * 
     * 返信を非公開にします。
     * 
     * @return void
     */
    public function doPrivate(): void
    {
        $this->is_public = false;
        $this->save();
    }

    /**
     * 最新のものから並び替えるローカルスコープ
     */
    public function scopeOrderLatest($query): void
    {
        $query->latest('replied_at');
    }

    /**
     * 古いものから並び替えるローカルスコープ
     */
    public function scopeOrderOldest($query): void
    {
        $query->oldest('replied_at');
    }

    /**
     * 最新(latest|desc)または古いもの(oldest|asc)の文字列を直接指定してソートするローカルスコープ
     */
    public function scopeOrderDirection($query, string $direction = 'asc'): void
    {
        if ($direction == 'desc' || $direction == 'latest') {
            $query->orderLatest();
        } else if ($direction == 'asc' || $direction == 'oldest') {
            $query->orderOldest();
        }
    }

    /**
     * 返信者ニックネームで絞り込むためのローカルスコープ
     */
    public function scopeBy(Builder $query, $nickname): void
    {
        $query->where(function ($query) use ($nickname) {
            $query->where('replyer_nickname', $nickname)
                ->orWhereHas('replyer', function ($query) use ($nickname) {
                    $query->where('nickname', $nickname);
                });
        });
    }

    /**
     * 返信日時で絞り込むためのローカルスコープ
     */
    public function scopeAt(Builder $query, $datetime): void
    {
        if (is_string($datetime)) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}(?: \d{2}(?::\d{2})?)?$/', $datetime)) {
                // 時刻以下が省略されている場合は、前方一致検索
                $query->where('replied_at', 'like', $datetime . '%');
            } else {
                $query->where('replied_at', $datetime);
            }
        } elseif ($datetime instanceof CarbonImmutable) {
            // CarbonImmutableインスタンスの場合は、フォーマットして文字列に変換
            $datetime = $datetime->format('Y-m-d H:i:s');
            $query->where('replied_at', $datetime);
        }
    }

    /**
     * 返信日時の範囲を指定して取得するためのローカルスコープ
     */
    public function scopeBetween(Builder $query, $start, $end): void
    {
        $query->whereBetween('replied_at', [FDate::format($start, '+00:00:00'), FDate::format($end, '+23:59:59')]);
    }

    /**
     * 返信日時の未満で範囲指定して取得するためのローカルスコープ
     */
    public function scopeBefore(Builder $query, $datetime): void
    {
        $query->where('replied_at', '<', FDate::format($datetime, '+00:00:00'));
    }

    /**
     * 返信日時のより先で範囲指定して取得するためのローカルスコープ
     */
    public function scopeAfter(Builder $query, $datetime): void
    {
        $query->where('replied_at', '>', FDate::format($datetime, '+00:00:00'));
    }

    /**
     * 返信日時の以前で範囲指定して取得するためのローカルスコープ
     */
    public function scopeBeforeEquals(Builder $query, $datetime): void
    {
        $query->where('replied_at', '<=', FDate::format($datetime, '+00:00:00'));
    }

    /**
     * 返信日時の以降で範囲指定して取得するためのローカルスコープ
     */
    public function scopeAfterEquals(Builder $query, $datetime): void
    {
        $query->where('replied_at', '>=', FDate::format($datetime, '+00:00:00'));
    }

    /**
     * 公開されたコンテンツのみ取得するためのローカルスコープ
     */
    public function scopePublic($query): void
    {
        $query->where('is_public', true)
            ->whereHas('comment', function ($q) {
                $q->where('is_public', true)
                    ->where(function ($q2) {
                        $q2->whereHasMorph(
                            'commentable',
                            [Journal::class, Photo::class, Location::class, Item::class],
                            function ($q3) {
                                $q3->where('is_public', true);
                            }
                        );
                    });
            });
    }

    /**
     * 非公開のコンテンツのみ取得するためのローカルスコープ
     */
    public function scopePrivate($query): void
    {
        $query->where('is_public', false)
            ->orWhereHas('comment', function ($q) {
                $q->where('is_public', false)
                    ->orWhereHasMorph(
                        'commentable',
                        [Journal::class, Photo::class, Location::class, Item::class],
                        function ($q2) {
                            $q2->where('is_public', false);
                        }
                    );
            });
    }

    /**
     * 閲覧可能な返信の絞り込むためのローカルスコープ
     * 
     * @see https://github.com/ryossi/feeldee-framework/wiki/返信#閲覧可能な返信の絞り込み
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
                $viewer = Profile::nickname($viewer)->first();
            } else {
                // デフォルトプロフィールが特定できない場合は、匿名ユーザー(null)として扱う
                $viewer = null;
            }
        }

        // 閲覧可能な返信を絞り込む
        $query->public()->where(
            function (Builder $query) use ($viewer) {
                $query->whereHas('comment', function ($q) use ($viewer) {
                    // 返信対象は公開済み
                    $q->where('is_public', true)
                        ->whereHasMorph(
                            'commentable',
                            [Journal::class, Photo::class, Location::class, Item::class],
                            function ($subQ) use ($viewer) {
                                // コメント対象は公開済み
                                $subQ->where('is_public', true);
                                $subQ->where(function ($q) use ($viewer) {
                                    // 公開レベル「全員」
                                    $q->where('public_level', PublicLevel::Public);
                                    if (!is_null($viewer)) {
                                        // 公開レベル「会員」
                                        $q->orWhere('public_level', PublicLevel::Member);
                                        // 公開レベル「友達」
                                        // 友達機能: viewerが投稿者のfriendsテーブルに含まれているか判定
                                        $q->orWhere(function (Builder $q2) use ($viewer) {
                                            $q2->where('public_level', PublicLevel::Friend)
                                                ->where(function ($friendQuery) use ($viewer) {
                                                    // 自分自身の場合も含める
                                                    $friendQuery->where('profile_id', $viewer->id)
                                                        ->orWhereHas('profile.friends', function ($fq) use ($viewer) {
                                                            $fq->where('friend_id', $viewer->id);
                                                        });
                                                });
                                        });
                                        // 公開レベル「自分」
                                        $q->orWhere(function (Builder $q2) use ($viewer) {
                                            $q2->where('public_level', PublicLevel::Private)
                                                ->where('profile_id', $viewer->id);
                                        });
                                        // 返信者が自分の場合も含める
                                        $q->orWhere('replyer_profile_id', $viewer->id);
                                    }
                                });
                            }
                        );
                });
            }
        );
    }

    /**
     * 取得した返信そのものが閲覧可能かどうかを判断します。
     *
     * @see https://github.com/ryossi/feeldee-framework/wiki/返信#閲覧可能な返信の絞り込み
     *
     * @param mixed $viewer 閲覧者（未特定の場合null）
     * @return bool 閲覧可能な場合true、閲覧不可の場合false
     */
    public function isViewable(mixed $viewer = null): bool
    {
        // 返信が非公開の場合は閲覧不可
        if (!$this->is_public) {
            return false;
        }

        // 返信対象のコメントが非公開の場合は閲覧不可
        if (!$this->comment?->is_public) {
            return false;
        }

        // 返信対象のコメントのコメント対象が非公開の場合も閲覧不可
        if (!$this->comment?->commentable?->is_public) {
            return false;
        }

        // viewerをProfileインスタンスに変換
        if (!($viewer instanceof Profile)) {
            if ($viewer && method_exists($viewer, 'profile')) {
                $viewer = $viewer->profile;
            } elseif (is_string($viewer)) {
                $viewer = Profile::nickname($viewer)->first();
            } else {
                $viewer = null;
            }
        }

        // 返信者自身の場合は常に閲覧可能
        if ($viewer && $this->replyer_profile_id === $viewer->id) {
            return true;
        }

        // コメント対象の公開レベルによる判定
        switch ($this->comment?->commentable?->public_level) {
            case PublicLevel::Public:
                return true;
            case PublicLevel::Member:
                return !is_null($viewer);
            case PublicLevel::Friend:
                return $viewer && ($viewer->id === $this->comment?->commentable?->profile_id || $this->comment?->commentable?->profile->isFriend($viewer));
            case PublicLevel::Private:
                return $viewer && $viewer->id === $this->comment?->commentable?->profile_id;
            default:
                return false;
        }
    }

    /**
     * 投稿タイプで返信の絞り込むためのローカルスコープ
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Post|string $type 投稿タイプ
     * @see https://github.com/ryossi/feeldee-framework/wiki/返信#投稿タイプによる返信の絞り込み
     */
    public function scopeOf($query, Post|string $type)
    {
        if (is_subclass_of($type, Post::class)) {
            $type = $type::type();
        }
        $query->whereHas('comment', function ($q) use ($type) {
            $q->where('commentable_type', $type);
        });
    }
}
