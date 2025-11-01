<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Database\Factories\ProfileFactory;
use Feeldee\Framework\Exceptions\ApplicationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Intervention\Image\Facades\Image;

/**
 * プロフィールをあらわすモデル
 */
class Profile extends Model
{
    use HasFactory, SetUser, Required;

    /**
     * プロフィールとユーザとの関連付けタイプコンフィグレーションキー
     */
    public const CONFIG_KEY_USER_RELATION_TYPE = 'feeldee.profile_user_relation_type';

    /**
     * プロフィールデフォルト順位コンフィグレーションキー
     */
    public const CONFIG_KEY_DEFAULT_ORDER = 'feeldee.profile_default_order';

    /**
     * ニックネームが重複しているエラーコード
     */
    public const ERROR_CODE_NICKNAME_DUPLICATED = 10001;

    /**
     * ユーザIDが指定されていないエラーコード
     */
    public const ERROR_CODE_USER_ID_REQUIRED = 10002;

    /**
     * ニックネームが指定されていないエラーコード
     */
    public const ERROR_CODE_NICKNAME_REQUIRED = 10003;

    /**
     * タイトルが指定されていないエラーコード
     */
    public const ERROR_CODE_TITLE_REQUIRED = 10004;

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['user_id', 'nickname', 'image', 'title', 'subtitle', 'description'];

    /**
     * 必須にする属性
     * 
     * @var array
     */
    protected $required = [
        'user_id' => Profile::ERROR_CODE_USER_ID_REQUIRED,
        'nickname' => Profile::ERROR_CODE_NICKNAME_REQUIRED,
        'title' => Profile::ERROR_CODE_TITLE_REQUIRED,
    ];

    protected static function bootedNickname(Self $model)
    {
        if (Profile::nickname($model->nickname)->first()?->id !== $model->id) {
            // ニックネームが重複している場合
            throw new ApplicationException(Profile::ERROR_CODE_NICKNAME_DUPLICATED, ['nickname' => $model->nickname]);
        }
    }

    /**
     * ファクトリインスタンスを返す
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return ProfileFactory::new();
    }

    /**
     * モデルの「起動」メソッド
     */
    protected static function booted(): void
    {
        static::creating(function (Self $model) {
            // ニックネーム
            static::bootedNickname($model);
        });

        static::updating(function (Self $model) {
            // ニックネーム
            static::bootedNickname($model);
        });

        static::saved(function (Self $model) {
            foreach ($model->configCache as $key => $config) {
                // キャッシュに存在するコンフィグを保存
                $config->save();
            }
        });
    }

    /**
     * カテゴリリスト
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * タグリスト
     */
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * レコーダリスト
     */
    public function recorders()
    {
        return $this->hasMany(Recorder::class);
    }

    /**
     * 記録リスト
     */
    public function journals()
    {
        return $this->hasMany(Journal::class);
    }

    /**
     * 写真リスト
     */
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * 場所リスト
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    /**
     * アイテムリスト
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * コメントリスト
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * 返信リスト
     */
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    /**
     * コンフィグリスト
     */
    public function configs()
    {
        return $this->hasMany(Config::class);
    }

    /**
     * 友達リスト
     */
    public function friends()
    {
        $friendPivot = new class extends Pivot {
            use SetUser;
        };
        return $this->belongsToMany(Profile::class, 'friends', 'profile_id', 'friend_id')->using($friendPivot);
    }

    private $configCache = [];

    /**
     * コンフィグ取得メソッド
     * 
     * コンフィグタイプに一致するコンフィグを取得します。
     * 
     * コンフィグタイプに一致するコンフィグが登録されてない場合は、データベースに登録してからコンフィグクラスを返します。
     * 
     * @param string $type コンフィグタイプ
     * @return Config コンフィグクラス
     * @throws ApplicationException コンフィグタイプが未定義の場合
     */
    public function config(string $type): Config
    {
        $config = $this->configs()->of($type)->first();
        if ($config === null) {
            // コンフィグが存在しない場合は新しい値オブジェクトを作成
            $config = $this->configs()->create([
                'type' => $type,
            ]);
        }
        $this->configCache[$type] = $config;
        return $config;
    }

    /**
     * コンフィグ値を取得するためのマジックメソッド
     * 
     * プロパティ名がコンフィグタイプに一致する場合、対応するコンフィグ値を返します。
     * 
     * コンフィグタイプに一致するコンフィグが登録されてない場合は、データベースに登録してからコンフィグクラスを返します。
     * 
     * プロパティ名がコンフィグタイプに一致しない場合は、親クラスのマジックメソッドを呼び出します。
     * 
     * @param string $key プロパティ名
     * @return mixed コンフィグ値
     */
    public function __get($key)
    {
        if (in_array($key, Config::getTypes())) {
            if (isset($this->configCache[$key])) {
                // キャッシュに存在する場合はキャッシュから取得
                return $this->configCache[$key]->value;
            }
            $config = $this->configs()->of($key)->first();
            if ($config === null) {
                // コンフィグが存在しない場合は新しい値オブジェクトを作成
                $config = $this->configs()->create([
                    'type' => $key,
                ]);
            }
            $this->configCache[$key] = $config;
            return $config->value;
        }
        return parent::__get($key);
    }


    /**
     * ユーザを指定してプロフィールを絞り込むためのローカルスコープ
     * 
     * @param Builder $query クエリビルダ
     * @param mixed $user ユーザIDまたはIlluminate\Contracts\Auth\Authenticatableインターフェースを実装したオブジェクト。nullの場合は現在ログイン中のユーザが使用されます
     * @return void
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#ユーザによるプロフィールの絞り込み
     */
    public function scopeBy($query, mixed $user = null): void
    {
        $userId = $user instanceof \Illuminate\Contracts\Auth\Authenticatable
            ? $user->getAuthIdentifier()
            : ($user ?? auth()->id());
        $query->where('user_id', $userId);
    }

    /**
     * ニックネームを指定してプロフィールを特定します。
     * 
     * @param Builder $query クエリビルダ
     * @param string|null $nickname ニックネーム
     * @param Like $like LIKE列挙型（デフォルトは、完全一致）
     * @return void
     * @link https://github.com/ryossi/feeldee-framework/wiki/プロフィール#ニックネームによるプロフィールの絞り込み
     */
    public function scopeNickname($query, ?string $nickname, Like $like = Like::All): void
    {
        $like->build($query, 'nickname', $nickname);
    }

    /**
     * 指定したコンフィグタイプに一致するコンフィグのキーが、指定した値と一致する設定となっているプロフィールを絞り込みます。
     * 
     * @param Builder $query クエリビルダ
     * @param string $type コンフィグタイプ
     * @param string $key コンフィグのキー
     * @param mixed $value コンフィグの値
     * @return void
     */
    public function scopeWhereConfigContains(Builder $query, string $type, string $key, mixed $value): void
    {
        $query->whereHas('configs', function ($q) use ($type, $key, $value) {
            $q->where('type', $type)->where("value->$key", $value);
        });
    }

    /**
     * 友達リストに指定したプロフィールが含まれているかどうかを判断します。
     * 
     * @param Profile|string|null $profile プロフィールまたはニックネーム
     * @return bool 友達の場合true、友達以外の場合false
     */
    public function isFriend(Profile|string|null $profile): bool
    {
        $friendProfile = $profile instanceof Profile
            ? $profile
            : Profile::nickname($profile)->first();

        return $friendProfile
            ? $this->friends()->where('friend_id', $friendProfile->id)->exists()
            : false;
    }

    // ========================== ここまで整理済み ==========================

    /**
     * ポイントリストを取得します。
     */
    public function points()
    {
        return $this->hasMany(Point::class);
    }

    /**
     * ファイルパスまたはデータを指定してプロフィールイメージを保存します。
     * プロフィールイメージは、元画像の80%に圧縮し120x120にリサイズしたJPEG画像となります。
     * 
     * @param string $data ファイルデータ(パス|バイナリ)
     */
    public function storeImage(mixed $data): void
    {
        $this->image = 'data:image/jpeg;base64,' . base64_encode(Image::make($data)->resize(120, 120)->encode('jpg', 80));
    }
}
