<?php

namespace Feeldee\Framework\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
    protected $fillable = ['body', 'commenter', 'nickname', 'commented_at'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['id', 'body', 'commented_at', 'is_public', 'commenter', 'nickname', 'replies', 'commentable'];

    /**
     * 配列に追加する属性
     * 
     * @var array
     */
    protected $appends = ['commenter', 'nickname', 'replies', 'commentable'];

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
        static::creating(function (self $model) {
            // コメント所有者
            $model->profile = $model->commentable->profile;
        });
    }

    /**
     * コメントを作成します。
     * 
     * @param array<string, mixed>  $attributes　属性
     * @param Content $ommentable コメント対象
     * @return Self 作成したコメント
     */
    public static function create($attributes = [], Content $commentable): Self
    {
        // ログインユーザ取得
        $user = Auth::user();

        // バリデーション
        Validator::validate($attributes, [
            // 匿名ユーザは、ニックネームが必須
            'nickname' => Rule::requiredIf(!$user),
        ]);

        // コメント日時
        if (!array_key_exists('commented_at', $attributes) || empty($attributes['commented_at'])) {
            // コメント日時が指定されなかった場合
            // システム日時が自動で設定される
            $attributes['commented_at'] = Carbon::now();
        }

        // コメント作成
        return $commentable->comments()->create(
            array_merge(
                $attributes,
                [
                    'commenter' => $user?->profile,
                ]
            )
        );
    }

    /**
     * コメント所有者
     *
     * @return Attribute
     */
    protected function profile(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->belongsTo(Profile::class, 'profile_id')->get()->first(),
            set: fn($value) => [
                'profile_id' => $value?->id
            ]
        );
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
     * コメント対象コンテンツ
     * TODO: 冗長なのでコメント対象と統一する
     */
    protected function getCommentableAttribute()
    {
        return  $this->commentable()->first();
    }

    /**
     * コメント者
     *
     * @return Attribute
     */
    protected function commenter(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['commenter_profile_id'] ? $this->belongsTo(Profile::class, 'commenter_profile_id')->get()->first() : null,
            set: fn($value) => [
                'commenter_profile_id' => $value?->id,
            ]
        );
    }

    /**
     * コメント者ニックネーム
     * 
     * @return Attribute
     */
    protected function nickname(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => empty($attributes['commenter_nickname']) ? $this->commenter->nickname : $attributes['commenter_nickname'],
            set: fn($value) => [
                'commenter_nickname' => $value,
            ]
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
     * TODO: 冗長なので返信リストと統一する
     */
    protected function getRepliesAttribute()
    {
        return  $this->replies()->get();
    }

    /**
     * コメント公開フラグ
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
     * コメント対象種別を条件に含むようにクエリのスコープを設定
     */
    public function scopeOfType($query, string $commentableType)
    {
        $query->where('commentable_type', $commentableType);
    }

    /**
     * 公開済みのみを含むようにクエリのスコープを設定
     */
    public function scopePublic($query)
    {
        $query->where('is_public', true);
        $morphMap = Relation::morphMap();
        $whenIsPublic = array();
        $whenPublicLevel = array();
        foreach ($morphMap as $type => $value) {
            $class = Relation::getMorphedModel($type);
            $content = new $class();
            $table = $content->getTable();
            array_push($whenIsPublic, "when commentable_type = '$type' then (select is_public from $table where profile_id = comments.profile_id and id = comments.commentable_id)");
            array_push($whenPublicLevel, "when commentable_type = '$type' then (select public_level from $table where profile_id = comments.profile_id and id = comments.commentable_id)");
        }
        $query->where(function ($query) use ($whenIsPublic) {
            $query->selectRaw('case ' . implode(' ', $whenIsPublic) . ' else 0 end')->from('comments', 'c1')->whereColumn('c1.id', 'comments.id');
        }, true);
        $query->where(function ($query) use ($whenPublicLevel) {
            // 公開レベル「全員」
            $query->orWhere(function ($query) use ($whenPublicLevel) {
                $query->selectRaw('case ' . implode(' ', $whenPublicLevel) . ' else 0 end')->from('comments', 'c2')->whereColumn('c2.id', 'comments.id');
            }, PublicLevel::Public);
            // 公開レベル「会員」
            $query->orWhere(function ($query) use ($whenPublicLevel) {
                $query->where(function ($query) use ($whenPublicLevel) {
                    $query->selectRaw('case ' . implode(' ', $whenPublicLevel) . ' else 0 end')->from('comments', 'c2')->whereColumn('c2.id', 'comments.id');
                }, PublicLevel::Member)
                    ->whereRaw('1 = ?', [!is_null(Auth::user()?->profile)]);
            });
            // 公開レベル「友達」
            // TODO::友達機能未実装
            $query->orWhere(function ($query) use ($whenPublicLevel) {
                $query->where(function ($query) use ($whenPublicLevel) {
                    $query->selectRaw('case ' . implode(' ', $whenPublicLevel) . ' else 0 end')->from('comments', 'c2')->whereColumn('c2.id', 'comments.id');
                }, PublicLevel::Friend)
                    ->where('profile_id', Auth::user()?->profile->id);
            });
            // 公開レベル「自分」
            $query->orWhere(function ($query) use ($whenPublicLevel) {
                $query->where(function ($query) use ($whenPublicLevel) {
                    $query->selectRaw('case ' . implode(' ', $whenPublicLevel) . ' else 0 end')->from('comments', 'c2')->whereColumn('c2.id', 'comments.id');
                }, PublicLevel::Private)
                    ->where('profile_id', Auth::user()?->profile->id);
            });
        });
    }

    /**
     * 最新のものから並び替えるクエリのスコープを設定
     */
    public function scopeOrderLatest($query)
    {
        return $query->latest('commented_at');
    }

    /**
     * 古いものから並び替えるクエリのスコープを設定
     */
    public function scopeOrderOldest($query)
    {
        return $query->oldest('commented_at');
    }
}
