<?php

namespace Feeldee\Framework\Models;

use Carbon\CarbonImmutable;
use Feeldee\Framework\Casts\HTML;
use Feeldee\Framework\Casts\URL;
use Intervention\Image\Facades\Image;

/**
 * アイテムをあらわすモデル
 */
class Item extends Post
{
    /**
     * 投稿種別
     * 
     * @return string
     */
    public static function type()
    {
        return 'item';
    }

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['profile', 'public_level', 'category', 'category_id', 'tags', 'records', 'title', 'value', 'thumbnail', 'order_number'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['id', 'profile', 'is_public', 'public_level', 'category', 'title', 'category_name', 'thumbnail', 'text'];

    /**
     * 配列に追加する属性
     * 
     * @var array
     */
    protected $appends = ['category_name'];

    /**
     * キャストする必要のある属性
     *
     * @var array
     */
    protected $casts = [
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
        'title' => 50001,
    ];

    /**
     * 文字列から HTML および PHP タグを取り除く属性
     * 
     * @var array
     */
    protected $strip_tags = ['value' => 'text'];

    /**
     * モデルの「起動」メソッド
     */
    protected static function onBooted(): void
    {
        static::addGlobalScope('defaultSort', function ($builder) {
            $builder->orderBy('order_number')->orderByDesc('posted_at');
        });

        static::creating(function (self $item) {
            // 表示順割り当て
            if (empty($item->order_number)) {
                $item->newOrderNumber();
            }
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
     * アイテム表示順を新しく割り当てます。
     */
    protected function newOrderNumber()
    {
        $last = $this->profile->items()->get()->last();
        if ($last == null) {
            $this->order_number = 1;
        } else {
            $this->order_number = $last->order_number + 1;
        }
    }

    // ========================== ここまで整理済み ==========================

    /**
     * アイテムを入れ替えます。
     * 同じカテゴリまたはカテゴリ未指定の場合は、表示順が入れ替わります。
     * 別カテゴリの場合は、カテゴリと表示順が入れ替わります。
     * 
     * @param Item $item 入れ替え対象アイテム
     * @return void
     */
    public function swap(Item $item): void
    {
        // 表示順入れ替え
        $order_number = $this->order_number;
        $this->order_number = $item->order_number;
        $item->order_number = $order_number;

        if ($this->category != $item->category) {
            // 別カテゴリの場合

            // カテゴリ入れ替え
            $category = $this->category;
            $this->category = $item->category;
            $item->category = $category;
        }

        $this->save();
        $item->save();
    }

    /**
     * ファイルパスまたはデータを指定してアイテムイメージを保存します。
     * 
     * @param string $data ファイルデータ(パス|バイナリ)
     */
    public function storeImage(mixed $data): void
    {
        $this->image = 'data:image/jpeg;base64,' . base64_encode(Image::make($data)->encode(config('feeldee.item.image.format'), config('feeldee.item.image.quality')));
    }
}
