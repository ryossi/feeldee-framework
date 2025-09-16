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
}
