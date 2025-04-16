<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Casts\Html;
use Feeldee\Framework\Exceptions\LoginRequiredException;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

/**
 * アイテムをあらわすモデル
 */
class Item extends Content
{
    /**
     * コンテンツ種別
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
    protected $fillable = ['profile', 'public_level', 'category', 'title', 'value'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['id', 'profile', 'is_public', 'public_level', 'category', 'title', 'category_name', 'image', 'text'];

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
        'value' => Html::class,
    ];

    /**
     * モデルの「起動」メソッド
     */
    protected static function booted(): void
    {
        static::saving(function (Self $model) {
            // テキストは、自動補完
            $model->text = strip_tags($model->value);
        });

        static::addGlobalScope('order_number', function ($builder) {
            $builder->orderBy('order_number');
        });

        static::creating(function (self $item) {
            // 表示順割り当て
            $item->newOrderNumber();
        });
    }

    // ========================== ここまで整理済み ==========================

    /**
     * 最後に表示順を新しく割り当てます。
     */
    protected function newOrderNumber()
    {
        // 同一階層のカテゴリリスト取得
        $last = $this->profile->items()->get()->last();

        // 表示順生成
        if ($last == null) {
            $this->order_number = 1;
        } else {
            $this->order_number = $last->order_number + 1;
        }
    }

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
