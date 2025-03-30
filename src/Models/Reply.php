<?php

namespace Feeldee\Framework\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
    protected $fillable = ['body', 'replyer', 'nickname', 'replied_at'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['id', 'body', 'replied_at', 'is_public', 'replyer', 'nickname'];

    /**
     * 配列に追加する属性
     * 
     * @var array
     */
    protected $appends = ['replyer', 'nickname'];

    /**
     * 変換する属性
     */
    protected $casts = [
        'replied_at' => 'datetime'
    ];

    /**
     * モデルの「起動」メソッド
     */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            // 返信公開フラグ
            // 作成時は、返信対象のコメント公開フラグと同じ
            $model->is_public = $model->comment->isPublic();
        });
    }

    /**
     * 返信を作成します。
     * 
     * @param array<string, mixed>  $attributes　属性
     * @param Comment $comment 返信対象
     * @return Self 作成した返信
     */
    public static function create($attributes = [], Comment $comment): Self
    {
        // ログインユーザ取得
        $user = Auth::user();

        // バリデーション
        Validator::validate($attributes, [
            // 匿名ユーザは、ニックネームが必須
            'nickname' => Rule::requiredIf(!$user),
        ]);

        // 返信日時
        if (!array_key_exists('replied_at', $attributes) || empty($attributes['replied_at'])) {
            // 返信日時が指定されなかった場合
            // システム日時が自動で設定される
            $attributes['replied_at'] = Carbon::now();
        }

        // 返信作成
        return $comment->replies()->create(
            array_merge(
                $attributes,
                [
                    'replyer' => $user?->getProfile(),
                ]
            )
        );
    }

    /**
     * 返信対象
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * 返信者
     *
     * @return Attribute
     */
    protected function replyer(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['replyer_profile_id'] ? $this->belongsTo(Profile::class, 'replyer_profile_id')->get()->first() : null,
            set: fn($value) => [
                'replyer_profile_id' => $value
            ]
        );
    }

    /**
     * 返信者ニックネーム
     */
    protected function nickname(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => empty($attributes['replyer_nickname']) ? $this->replyer->nickname : $attributes['replyer_nickname'],
            set: fn($value) => [
                'replyer_nickname' => $value,
            ]
        );
    }
}
