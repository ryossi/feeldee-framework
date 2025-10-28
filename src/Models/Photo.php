<?php

namespace Feeldee\Framework\Models;

use Carbon\CarbonImmutable;
use Feeldee\Framework\Casts\HTML;
use Feeldee\Framework\Casts\URL;
use Feeldee\Framework\Database\Factories\PhotoFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/**
 * 写真をあらわすモデル
 *
 */
class Photo extends Post
{
    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['profile', 'public_level', 'category', 'category_id', 'tags', 'records', 'title', 'value', 'src', 'photo_type', 'posted_at'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['id', 'profile', 'is_public', 'public_level', 'category', 'title', 'photo_type', 'src', 'posted_at', 'albums'];

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
        'posted_at' => 'datetime',
        'value' => HTML::class,
        'thumbnail' => URL::class,
    ];

    /**
     * ソートするカラム名
     * 
     * @var array
     */
    protected $order_column = 'posted_at';

    /**
     * 必須にする属性
     * 
     * @var array
     */
    protected $required = [
        'src' => 30001, // 写真ソースが指定されていない
    ];

    /**
     * 文字列から HTML および PHP タグを取り除く属性
     * 
     * @var array
     */
    protected $strip_tags = ['value' => 'text'];

    /**
     * ファクトリインスタンスを返す
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return PhotoFactory::new();
    }

    /**
     * モデルの「起動」メソッド
     */
    protected static function onBooted(): void
    {
        static::addGlobalScope('defaultSort', function ($builder) {
            $builder->orderByDesc('posted_at');
        });

        static::saving(
            function (self $model) {
                // 投稿日時
                if (empty($model->posted_at)) {
                    $model->posted_at = CarbonImmutable::now();
                }
            }
        );
    }

    /**
     * 投稿タイプ
     * 
     * @return string
     */
    public static function type()
    {
        return 'photo';
    }

    /**
     * 関連記事リスト
     * 
     * @return BelongsToMany
     */
    public function relatedJournals(): BelongsToMany
    {
        return $this->belongsToMany(Journal::class, 'posted_photos');
    }

    /**
     * 写真ソース
     */
    protected function src(): Attribute
    {
        $cast = new URL();

        return Attribute::make(
            get: fn($value, $attributes) => $cast->get($this, 'url', $value, $attributes),
            set: function ($value, $attributes) use ($cast) {
                $detected = null;
                $mapping = config('feeldee.photo_types', []);

                if (is_array($mapping) && !empty($value)) {
                    foreach ($mapping as $key => $pattern) {
                        if (@preg_match($pattern, $value) === 1) {
                            $detected = $key;
                            break;
                        }
                    }
                }
                return [
                    'src' => $cast->set($this, 'url', $value, $attributes),
                    'photo_type' => $detected,
                ];
            },
        );
    }

    // ========================== ここまで整理済み ==========================

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
            ->leftJoin('journals', 'journals.id', 'posted_photos.journal_id')
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
            ->leftJoin('journals', 'journals.id', 'posted_photos.journal_id')
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
