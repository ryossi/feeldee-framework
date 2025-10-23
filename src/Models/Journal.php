<?php

namespace Feeldee\Framework\Models;

use Carbon\CarbonImmutable;
use Feeldee\Framework\Casts\HTML;
use Feeldee\Framework\Casts\URL;
use Feeldee\Framework\Database\Factories\JournalFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 記録をあらわすモデル
 */
class Journal extends Post
{
    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['profile', 'public_level', 'category', 'category_id', 'tags', 'records', 'posted_at', 'title', 'value', 'thumbnail'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['id', 'profile', 'is_public', 'public_level', 'category', 'posted_at', 'title', 'archive_month', 'count_of_items', 'thumbnail'];

    /**
     * キャストする必要のある属性
     *
     * @var array
     */
    protected $casts = [
        'is_public' => 'boolean',
        'posted_at' => 'date',
        'value' => HTML::class,
        'thumbnail' => URL::class,
    ];

    /**
     * ソートするカラム名
     */
    protected $order_column = 'posted_at';

    /**
     * 必須にする属性
     * 
     * @var array
     */
    protected $required = [
        'title' => 20001, // 記事タイトルが指定されていない
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
        return JournalFactory::new();
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
                    $model->posted_at = CarbonImmutable::today();
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
        return 'journal';
    }

    /**
     * 写真リスト
     * 
     * @return BelongsToMany
     */
    public function photos(): BelongsToMany
    {
        return $this->belongsToMany(Photo::class, 'posted_photos');
    }

    // ========================== ここまで整理ずみ ==========================

    /**
     * システム日時現在からの投稿日の〇〇前
     */
    protected function ago(): Attribute
    {
        $ago = function ($value, $attributes) {
            $post_date = strtotime($attributes['post_date']);
            $today = strtotime(CarbonImmutable::now());
            $seconds = $today - $post_date;
            $hours = floor($seconds / 60 / 60);
            if ($hours < config('feeldee.post.ago.boundary.hour')) {
                return $hours . config('feeldee.post.ago.label.hour');
            }
            $days = floor($hours / 24);
            if ($days < config('feeldee.post.ago.boundary.day')) {
                return $days . config('feeldee.post.ago.label.day');
            }
            $weeks = floor($days / 7);
            if ($weeks < config('feeldee.post.ago.boundary.week')) {
                return $weeks . config('feeldee.post.ago.label.week');
            }
            $months = floor($weeks / 4);
            if ($months < config('feeldee.post.ago.boundary.month')) {
                return $months . config('feeldee.post.ago.label.month');
            }
            $years = floor($months / 12);
            if ($years < config('feeldee.post.ago.boundary.year')) {
                return $years . config('feeldee.post.ago.label.year');
            }
            return null;
        };

        return Attribute::make(
            get: fn($value, $attributes) => $ago($value, $attributes),
        );
    }

    /**
     * この投稿に関連するアイテムグループリスト
     */
    public function itemGroups()
    {
        return $this->hasMany(ItemGroup::class);
    }

    /**
     * JsonArrayからアイテムグループを作成します。
     * カテゴリー名に一致するアイテムカテゴリーが存在しない場合、新規作成します。
     * アイテム名に一致するアイテムが存在しない場合、新規作成します。
     * JsonArrayの構造
     * [
     *     'name' => 'アイテムグループ名', 
     *     'items' => [
     *         [
     *             'category_name' => 'アイテムカテゴリー名',
     *             'name' => 'アイテム名'
     *         ]
     *     ]
     * ]
     * 
     * @param mixed $value アイテムグループのJsonArray
     */
    public function createItemGroups(mixed $value): void
    {
        $itemGroup = $this->itemGroups()->create($value);
        if (array_key_exists('items', $value)) {
            foreach ($value['items'] as $itemValue) {

                $category = null;
                if (array_key_exists('category_name', $itemValue) && !empty($itemValue['category_name'])) {
                    // アイテムカテゴリー存在チェック
                    $category_name = $itemValue['category_name'];
                    $category = $this->profile->categories()->ofType(Item::type())->ofName($category_name)->first();
                    if ($category === null) {
                        // アイテムカテゴリー新規作成
                        $category = Category::add($this->profile, Item::type(), $category_name);
                    }
                }

                // アイテム存在チェック
                $item_title = $itemValue['title'];
                $item = $this->profile->items()->ofTitle($item_title)->ofCategory($category)->first();
                if ($item === null) {
                    // アイテム新規作成
                    $item = Item::create([
                        'profile' => $this->profile,
                        'title' => $item_title
                    ]);
                    // カテゴリー分け
                    $item->categorizedByName($category_name);
                    $item->save();
                }

                // アイテム追加
                $itemGroup->items()->save($item);
            }
        }
    }

    /**
     * JsonArrayからアイテムグループリストを作成します。
     * 
     * @param mixed $values アイテムグループリストのJsonArray
     */
    public function createManyItemGroups(mixed $values): void
    {
        // 新しいアイイテムグループ追加
        foreach ($values as $value) {
            $this->createItemGroups($value);
        }
    }

    /**
     * この投稿に関連するタイムラインリスト
     */
    public function timelines()
    {
        return $this->hasMany(Timeline::class);
    }

    /**
     * JsonArrayからタイムラインを作成します。
     * JsonArrayの構造
     * [
     *     [
     *         'start_time' => '開始時刻',
     *         'end_time' => '終了時刻',
     *         'location' => [
     *             'id' => '場所ID'
     *         ]
     *     ],
     *     ...
     * ]
     * 配列の要素は、タイムライン順に指定します。
     * 
     * @param mixed $values タイムラインリストのJsonArray
     */
    public function createManyTimelines(mixed $values): void
    {
        // 投稿日をタイムラインの基準日とする
        $date_time = $this->post_date;

        foreach ($values as $value) {

            $start_datetime = null;
            $end_datetime = null;

            if (array_key_exists('start_time', $value) &&  !empty($value['start_time'])) {
                // 開始日時
                $start_datetime = date('Y-m-d', strtotime($date_time)) . ' ' . date('H:i', strtotime($value['start_time']));
                if ($start_datetime < $date_time) {
                    // 開始日時が直前より過去になる場合、日付を1日進める
                    $start_datetime = date('Y-m-d H:i', strtotime($start_datetime . ' 1 day'));
                }
                $date_time = $start_datetime;
            }

            if (array_key_exists('end_time', $value) &&  !empty(strtotime($value['end_time']))) {
                // 終了日時
                $end_datetime = date('Y-m-d', strtotime($date_time)) . ' ' . date('H:i', strtotime(($value['end_time'])));
                if ($end_datetime < $date_time) {
                    // 終了日時が直前より過去になる場合、日付を1日進める
                    $end_datetime = date('Y-m-d H:i', strtotime($end_datetime . ' 1 day'));
                }
                $date_time = $end_datetime;
            }

            // タイムライン作成
            $this->timelines()->create([
                'location_id' => $value['location']['id'],
                'start_datetime' => $start_datetime,
                'end_datetime' => $end_datetime,
            ]);
        }
    }

    /**
     * この投稿に関連するポイントリスト
     */
    public function points()
    {
        return $this->hasMany(Point::class);
    }

    /**
     * JsonArrayからポイントを作成します。
     * JsonArrayの構造
     * [
     *     [
     *         'title' => 'タイトル',
     *         'time' => '時刻',
     *         'memo' => 'メモ',
     *         'latitude' => '緯度',
     *         'longitude' => '経度',
     *         'point_type' => 'ポイントタイプ',
     *         'image_src' => 'イメージソース'
     *     ],
     *     ...
     * ]
     * 
     * @param mixed $values ポイントリストのJsonArray
     */
    public function createManyPoints(mixed $values): void
    {
        // 投稿日をタイムラインの基準日とする
        $date_time = $this->post_date;

        foreach ($values as $value) {

            $point_datetime = null;

            if (array_key_exists('time', $value) &&  !empty($value['time'])) {
                // ポイント日時
                $point_datetime = date('Y-m-d', strtotime($date_time)) . ' ' . date('H:i', strtotime($value['time']));
            }

            // ポイント作成
            $this->points()->create([
                'title' => $value['title'],
                'point_datetime' => $point_datetime,
                'memo' => $value['memo'],
                'latitude' => $value['latitude'],
                'longitude' => $value['longitude'],
                'point_type' => $value['point_type'],
                'image_src' => $value['image_src'],
            ]);
        }
    }

    protected function afterPublic(): void
    {
        // 投稿に添付されている写真リストも全て公開
        foreach ($this->photos as $photo) {
            $photo->doPublic();
        }
    }

    protected function afterPrivate(): void
    {
        // 投稿に添付されている写真リストも全て非公開
        foreach ($this->photos as $photo) {
            $photo->doPrivate();
        }
    }

    protected function changePublicLevel(PublicLevel $before, PublicLevel $after): void
    {
        // 投稿に添付されている写真リストの公開レベルも全て揃える
        foreach ($this->photos as $photo) {
            $photo->publicLevel = $after;
            $photo->save();
        }
    }

    /**
     * 東西の経度と南北の緯度の矩形に囲まれた公開済みの場所に関連のある投稿のみを含むようにクエリのスコープを設定
     * 最小公開レベルが指定されている場合は、最小公開レベル以上の場所のみを検索するようにクエリのスコープを設定
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param float $east 東経
     * @param float $west 西経
     * @param float $south 南緯
     * @param float $north 北緯
     * @param ?PublicLevel $minPublicLevel 最小公開レベル
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRectangle($query, float $east, float $west, float $south, float $north, ?PublicLevel $minPublicLevel = null)
    {
        $query->join('timelines', 'timelines.post_id', 'posts.id')
            ->join('locations', 'timelines.location_id', 'locations.id')
            ->where('locations.latitude', '<=', $north)
            ->where('locations.latitude', '>=', $south)
            ->where('locations.longitude', '<=', $east)
            ->where('locations.longitude', '>=', $west)
            ->where('locations.is_public', true)
            ->select('posts.*')->distinct();
        if ($minPublicLevel) {
            $query->where('locations.public_level', '>=', $minPublicLevel);
        }

        return $query;
    }

    /**
     * キーワードでタイトルと本文を検索するクエリのスコープを設定
     * キーワードはタイトルまたは本文と部分一致で検索されます。
     * キーワードには、区切り文字で区切って複数指定することができます。
     * 複数のキーワードを指定した場合、区切り文字で区切られたそれぞれのキーワードはAND条件で連結されます。
     * 
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param ?string $keyrowd キーワード
     * @param string $separator 区切り文字
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchKeyword($query, ?string $keyword = null, string $separator = ' ')
    {
        if ($keyword) {
            $keywords = explode($separator, $keyword);
            foreach ($keywords as $keyword) {
                $query->where(
                    function ($query) use ($keyword) {
                        $query->where('title', 'like', "%$keyword%")->orWhere('text', 'like', "%$keyword%");
                    }
                );
            }
        }

        return $query;
    }
}
