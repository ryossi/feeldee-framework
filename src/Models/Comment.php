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
 * コメントをあらわすモデル
 */
class Comment extends Model
{
    use HasFactory, SetUser;

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['commenter', 'commenter_nickname', 'body',  'commented_at'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['id', 'body', 'commented_at', 'is_public', 'replies', 'commentable'];

    /**
     * 配列に追加する属性
     * 
     * @var array
     */
    protected $appends = ['replies', 'commentable'];

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
        // デフォルトの並び順は、コメント日時降順
        static::addGlobalScope('order_number', function ($builder) {
            $builder->orderBy('commented_at', 'desc');
        });

        static::saving(function (self $model) {
            // コメント日時
            if (empty($model->commented_at)) {
                $model->commented_at = Carbon::now();
            }
            // コメント者プロフィール
            if (!empty($model->commenter) && $model->commenter instanceof Profile) {
                $model->commenter_profile_id = $model->commenter->id;
                unset($model->commenter);
            } elseif (empty($model->commenter_nickname)) {
                // コメント者ニックネームが指定されていない場合は、例外をスロー
                throw new ApplicationException(60001);
            }
        });

        static::creating(function (self $model) {
            // コメント所有者
            $model->profile_id = $model->commentable->profile_id;
        });
    }

    /**
     * コメント所有者プロフィール
     *
     * @return BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * コメント対象
     * 
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * コメント者プロフィール
     *
     * @return BelongsTo
     */
    public function commenter(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'commenter_profile_id');
    }

    /**
     * コメント者ニックネーム
     * 
     * @return Attribute
     */
    protected function commenterNickname(): Attribute
    {
        return Attribute::make(
            get: fn($value) => empty($value) ? $this->commenter?->nickname : $value,
            set: fn($value) => [
                'commenter_nickname' => $value,
            ]
        );
    }

    /**
     * コメント公開フラグ
     * 
     * @return Attribute
     */
    protected function isPublic(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->commentable?->is_public ? (boolval($value) ?? false) : false,
        );
    }

    /**
     * 返信リスト
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    /**
     * 公開
     * 
     * コメントを公開します。
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
     * コメントを非公開にします。
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
        $query->latest('commented_at');
    }

    /**
     * 古いものから並び替えるローカルスコープ
     */
    public function scopeOrderOldest($query): void
    {
        $query->oldest('commented_at');
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
     * コメント者ニックネームで絞り込むためのローカルスコープ
     */
    public function scopeBy(Builder $query, $nickname): void
    {
        $query->where(function ($query) use ($nickname) {
            $query->where('commenter_nickname', $nickname)
                ->orWhereHas('commenter', function ($query) use ($nickname) {
                    $query->where('nickname', $nickname);
                });
        });
    }

    /**
     * コメント日時で絞り込むためのローカルスコープ
     */
    public function scopeAt(Builder $query, $datetime): void
    {
        if (is_string($datetime)) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}(?: \d{2}(?::\d{2})?)?$/', $datetime)) {
                // 時刻以下が省略されている場合は、前方一致検索
                $query->where('commented_at', 'like', $datetime . '%');
            } else {
                $query->where('commented_at', $datetime);
            }
        } elseif ($datetime instanceof CarbonImmutable) {
            // CarbonImmutableインスタンスの場合は、フォーマットして文字列に変換
            $datetime = $datetime->format('Y-m-d H:i:s');
            $query->where('commented_at', $datetime);
        }
    }

    /**
     * コメント日時の範囲を指定して取得するためのローカルスコープ
     */
    public function scopeBetween(Builder $query, $start, $end): void
    {
        $query->whereBetween('commented_at', [FDate::format($start, '+00:00:00'), FDate::format($end, '+23:59:59')]);
    }

    /**
     * コメント日時の未満で範囲指定して取得するためのローカルスコープ
     */
    public function scopeBefore(Builder $query, $datetime): void
    {
        $query->where('commented_at', '<', FDate::format($datetime, '+00:00:00'));
    }

    /**
     * コメント日時のより先で範囲指定して取得するためのローカルスコープ
     */
    public function scopeAfter(Builder $query, $datetime): void
    {
        $query->where('commented_at', '>', FDate::format($datetime, '+00:00:00'));
    }

    /**
     * コメント日時の以前で範囲指定して取得するためのローカルスコープ
     */
    public function scopeBeforeEquals(Builder $query, $datetime): void
    {
        $query->where('commented_at', '<=', FDate::format($datetime, '+00:00:00'));
    }

    /**
     * コメント日時の以降で範囲指定して取得するためのローカルスコープ
     */
    public function scopeAfterEquals(Builder $query, $datetime): void
    {
        $query->where('commented_at', '>=', FDate::format($datetime, '+00:00:00'));
    }

    /**
     * 公開されたコメントのみ取得するためのローカルスコープ
     */
    public function scopePublic($query): void
    {
        $query->where('is_public', true)
            ->where(function ($q) {
                $q->whereHasMorph(
                    'commentable',
                    [Journal::class, Photo::class, Location::class, Item::class],
                    function ($subQ) {
                        $subQ->where('is_public', true);
                    }
                );
            });
    }

    /**
     * 非公開のコメントのみ取得するためのローカルスコープ
     */
    public function scopePrivate($query): void
    {
        $query->where('is_public', false)
            ->orWhere(function ($q) {
                $q->whereHasMorph(
                    'commentable',
                    [Journal::class, Photo::class, Location::class, Item::class],
                    function ($subQ) {
                        $subQ->where('is_public', false);
                    }
                );
            });
    }

    /**
     * 閲覧可能なコメントの絞り込むためのローカルスコープ
     * 
     * @see https://github.com/ryossi/feeldee-framework/wiki/投稿#閲覧可能な投稿の絞り込み
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
            $query->whereHasMorph(
                'commentable',
                [Journal::class, Photo::class, Location::class, Item::class],
                function ($subQ) use ($viewer) {
                    // コメント対象は公開済みのみ
                    $subQ->where('is_public', true);
                    // 公開レベルによる閲覧可否を判定
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
                            // コメント者が自分の場合も含める
                            $q->orWhere('commenter_profile_id', $viewer->id);
                        }
                    });
                }
            );
        });
    }

    // ========================== ここまで整理ずみ ==========================

    /**
     * コメント対象種別を条件に含むようにクエリのスコープを設定
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $commentableType コメント対象種別
     */
    public function scopeOfCommentableType($query, string $commentableType)
    {
        $query->where('commentable_type', $commentableType);
    }
}
