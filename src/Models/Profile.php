<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Exceptions\ApplicationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Intervention\Image\Facades\Image;

/**
 * プロフィールをあらわすモデル
 */
class Profile extends Model
{
    use HasFactory, SetUser, Required;

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
        'user_id' => 10002,
        'nickname' => 10003,
        'title' => 10004,
    ];

    protected static function bootedNickname(Self $model)
    {
        if (Profile::ofNickname($model->nickname)->first()?->id !== $model->id) {
            // ニックネームが重複している場合
            throw new ApplicationException(10001, ['nickname' => $model->nickname]);
        }
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
     * 投稿リスト
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
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
     * コンフィグリスト
     */
    public function configs()
    {
        return $this->hasMany(Config::class);
    }

    /**
     * ユーザIDを条件に含むようにクエリスコープを設定
     */
    public function scopeOfUserId($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * ニックネームを条件に含むようにクエリのスコープを設定
     */
    public function scopeOfNickname($query, ?string $nickname)
    {
        return $query->where('nickname', $nickname);
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
        $config = $this->configs()->ofType($type)->first();
        if ($config === null) {
            // コンフィグが存在しない場合は新しい値オブジェクトを作成
            $config = $this->configs()->create([
                'type' => $type,
                'value' => Config::newValue($type),
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
            $config = $this->configs()->ofType($key)->first();
            if ($config === null) {
                // コンフィグが存在しない場合は新しい値オブジェクトを作成
                $config = $this->configs()->create([
                    'type' => $key,
                    'value' => Config::newValue($key),
                ]);
            }
            $this->configCache[$key] = $config;
            return $config->value;
        }
        return parent::__get($key);
    }

    /**
     * コンフィグ値によるプロフィール絞り込みのスコープを設定
     * 
     * @param Builder $query クエリビルダ
     * @param string $type コンフィグのタイプ
     * @param mixed $value コンフィグの値
     * @return void
     */
    public function scopeWhereConfigContains(Builder $query, string $type, string $key, mixed $value): void
    {
        $query->whereHas('configs', function ($q) use ($type, $key, $value) {
            $q->where('type', $type)->where("value->$key", $value);
        });
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

    /**
     * 閲覧者に対する最小公開レベルを返却します。
     * 
     * 閲覧者が自分自身の場合、「自分」
     * 閲覧者が友達リストに含まれる場合、「友達」
     * 閲覧者が友達または自分以外の場合、「会員」
     * 閲覧者不明の場合、「全員」
     * 
     * @param ?Profile $viewer 閲覧者
     * @return PublicLevel 最小公開レベル
     */
    public function minPublicLevel(?Profile $viewer): PublicLevel
    {
        if (!$viewer) {
            // 閲覧者不明の場合、「全員」
            return PublicLevel::Public;
        }
        if ($viewer == $this) {
            // 閲覧者が自分自身の場合、「自分」
            return PublicLevel::Private;
        }
        if ($this->isFriend($viewer)) {
            // 閲覧者が友達リストに含まれる場合、「友達」
            return PublicLevel::Friend;
        }
        // 閲覧者が友達または自分以外の場合、「会員」
        return PublicLevel::Member;
    }

    /**
     * 閲覧者が友達かどうかを判断します。
     * 
     * @param Profile viewer 閲覧者
     * @return bool 友達の場合true、友達以外の場合false
     */
    public function isFriend(?Profile $viewer): bool
    {
        if (!$viewer) return false;

        return false;
    }

    /**
     * コメントリストを取得します。
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
