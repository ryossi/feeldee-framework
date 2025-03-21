<?php

namespace Feeldee\Framework\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;

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
    protected $fillable = ['body', 'commenter', 'commented_at'];

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
     * コメント対象コンテンツ
     */
    protected function getCommentableAttribute()
    {
        return  $this->commentable()->first();
    }

    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * コメントを所有するプロフィール
     *
     * @return Attribute
     */
    protected function profile(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->belongsTo(Profile::class, 'profile_id')->get()->first(),
            set: fn($value) => [
                'profile_id' => $value == null ? null : $value->id
            ]
        );
    }

    /**
     * コメンタープロフィール
     *
     * @return Attribute
     */
    protected function commenter(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['commenter_profile_id'] ? $this->belongsTo(Profile::class, 'commenter_profile_id')->get()->first() : null,
            set: fn($value) => [
                'commenter_profile_id' => $value instanceof Profile ? $value->id : null,
                'commenter_nickname' => $value instanceof Profile ? $value->nickname : $value,
            ]
        );
    }

    /**
     * コメント者ニックネーム
     */
    protected function nickname(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $this->commenter instanceof Profile ? $this->commenter->nickname : $attributes['commenter_nickname'],
        );
    }

    /**
     * 返信リスト
     */
    protected function getRepliesAttribute()
    {
        return  $this->replies()->get();
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    /**
     * コメントが公開中かどうかを判定します。
     * 
     * @return bool 公開中の場合true
     */
    public function isPublic(): bool
    {
        return $this->is_public;
    }

    /**
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
