<?php

namespace Feeldee\Framework\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Intervention\Image\Facades\Image;

/**
 * プロフィールをあらわすモデル
 */
class Profile extends Model
{
    use HasFactory, SetUser, AccessCounter;

    protected $fillable = ['nickname', 'title', 'subtitle', 'introduction', 'home', 'user_id', 'show_members'];

    /**
     * プロフィールのメディアボックスを取得
     */
    public function mediaBox(): HasOne
    {
        return $this->hasOne(MediaBox::class, 'user_id', 'user_id');
    }

    /**
     * 投稿リストを取得します。
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * 写真リストを取得します。
     */
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * 場所リストを取得します。
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    /**
     * アイテムリストを取得します。
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * カテゴリーリストを取得します。
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    /**
     * タグリストを取得します。
     */
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

    /**
     * レコーダリストを取得します。
     */
    public function recorders()
    {
        return $this->hasMany(Recorder::class);
    }

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

    protected function configs()
    {
        return $this->hasMany(Config::class);
    }

    /**
     * プロフィールに関連しているコンフィグ取得
     */
    public function config(string $type)
    {
        $config = $this->configs()->where('type', $type)->get()->first();
        if ($config === null) {
            $config = $this->configs()->create([
                'type' => $type,
                'value' => Config::newValue($type),
            ]);
        }
        return $config;
    }

    public function __get($key)
    {
        if ($key === 'config') {
            return new class($this->configs())
            {
                private $configs;

                public function __construct($configs)
                {
                    $this->configs = $configs;
                }

                public function __get($type)
                {
                    $config = $this->configs->where('type', $type)->get()->first();
                    return $config === null ?  Config::newValue($type) :  $config->value;
                }
            };
        }
        return parent::__get($key);
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

    /**
     * コンテンツ閲覧履歴リストを取得します。
     */
    public function viewHistories()
    {
        return $this->hasMany(ContentViewHistory::class);
    }

    /**
     * ニックネームを条件に含むようにクエリのスコープを設定
     */
    public function scopeOfNickname($query, ?string $nickname)
    {
        return $query->where('nickname', $nickname);
    }

    /**
     * メンバーリスト表示対象プロフィールのみを含むようにクエリのスコープを設定
     */
    public function scopeMembers($query)
    {
        return $query->where('show_members', true);
    }
}
