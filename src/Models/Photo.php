<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Casts\HTML;
use Feeldee\Framework\Casts\URL;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/**
 * 写真をあらわすモデル
 *
 */
class Photo extends Content
{
    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['profile', 'public_level', 'category', 'category_id', 'tags', 'title', 'value', 'photo_type', 'src', 'regist_datetime'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['id', 'profile', 'is_public', 'public_level', 'category', 'title', 'photo_type', 'src', 'regist_datetime', 'albums'];

    /**
     * 配列に追加する属性
     * 
     * @var array
     */
    protected $appends = ['albums'];

    /**
     * キャストする必要のある属性
     *
     * @var array
     */
    protected $casts = [
        'regist_datetime' => 'date',
        'value' => HTML::class,
        'src' => URL::class
    ];

    /**
     * コンテンツをソートするカラム名
     * 
     * @var array
     */
    protected $order_column = 'regist_datetime';

    /**
     * 文字列から HTML および PHP タグを取り除く属性
     * 
     * @var array
     */
    protected $strip_tags = ['value' => 'text'];

    /**
     * モデルの「起動」メソッド
     */
    protected static function booted(): void
    {
        static::creating(function (Self $model) {
            // 写真準備
            $model->prepare();
        });
    }

    /**
     * コンテンツ種別
     * 
     * @return string
     */
    public static function type()
    {
        return 'photo';
    }

    /**
     * 投稿リスト
     * 
     * @return BelongsToMany
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'posted_photos');
    }

    /**
     * 写真ソースで絞り込むクエリのスコープを設定
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param mixed $src 写真ソース
     */
    public function scopeOfSrc($query, mixed $src)
    {
        $query->where('src', $src);
    }

    // ========================== ここまで整理済み ==========================

    /**
     * 写真を準備します。
     */
    protected function prepare(): void
    {
        $media = $this->profile->mediaBox?->find($this->src);
        if ($media !== null) {
            // メディアが存在する場合
            $this->photo_type = PhotoType::Feeldee;
            $this->width = $media->width;
            $this->height = $media->height;
        } else {
            // メディアが存在しない場合
            if (strpos($this->src, PhotoType::Google->value) !== false) {
                $this->photo_type =  PhotoType::Google;
            } else {
                $this->photo_type =  PhotoType::Other;
            }
        }
    }

    /**
     * アルバム名
     */
    protected function albums(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $this->tags()->get(['name'])->map(function ($item, $key) {
                return $item->name;
            }),
        );
    }

    /**
     * 写真タイプ
     */
    protected function photoType(): Attribute
    {
        return Attribute::make(
            get: fn($value) => empty($value) ? null : PhotoType::from($value),
            set: fn($value) => $value->value,
        );
    }

    /**
     * タイトルを変更します。
     *
     * @param  ?string $new_title 新しいタイトル
     * @return void
     */
    public function rename(?string $new_title): void
    {
        $this->title = $new_title;
        $this->save();
    }

    /**
     * 写真ギャラリーデータを準備します。
     * 
     * @param Profile $profile プロフィール
     * @param int $pageSize ページサイズ
     * @return array [日付リスト, 写真リスト]の配列
     */
    public static function preparePhotoGalleryData(Profile $profile, int $pageSize, PublicLevel $minPublicLevel = PublicLevel::Private): array
    {
        $date_list = self::leftJoin('posted_photos', 'posted_photos.photo_id', 'photos.id')
            ->leftJoin('posts', 'posts.id', 'posted_photos.post_id')
            ->select(
                DB::raw('ifnull(posts.post_date, photos.regist_datetime) as date')
            )
            ->where('photos.profile_id', $profile->id)
            ->where('photos.is_public', true)
            ->where('photos.public_level', '>=', $minPublicLevel)
            ->groupBy('date', 'posts.id')
            ->orderBy('date', 'desc')
            ->simplePaginate($pageSize);
        if (!$date_list->first()) {
            return [$date_list, array()];
        }
        $photo_list = Photo::leftJoin('posted_photos', 'posted_photos.photo_id', 'photos.id')
            ->leftJoin('posts', 'posts.id', 'posted_photos.post_id')
            ->select(
                DB::raw('ifnull(posts.post_date, photos.regist_datetime) as date'),
                'photos.id',
                'photos.photo_type',
                'photos.src',
                'photos.title',
                'photos.width',
                'photos.height',
                'posts.title as post_title',
            )
            ->whereRaw('ifnull(posts.post_date, photos.regist_datetime) <= ?', [$date_list->first()->date])
            ->whereRaw('ifnull(posts.post_date, photos.regist_datetime) >= ?', [$date_list->last()->date])
            ->where('photos.is_public', true)
            ->where('photos.public_level', '>=', $minPublicLevel)
            ->orderBy('date', 'desc')
            ->orderBy('photos.id')
            ->orderBy('post_title')
            ->get();

        return [$date_list, $photo_list];
    }
}
