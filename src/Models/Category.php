<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\SqlLikeBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

/**
 * カテゴリーをあらわすモデル
 */
class Category extends Model
{
    use HasFactory, SetUser, WrappedId;

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['profile', 'type', 'name', 'parent'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['profile', 'id', 'type', 'name', 'parent'];

    /**
     * IDをラップする属性
     * 
     * @var array
     */
    protected $wrappable = [
        'profile' => 'profile_id',
        'parent' => 'parent_id',
    ];

    /**
     * 同一階層の最後に表示順を新しく割り当てます。
     */
    protected function newOrderNumber()
    {
        if (!$this->profile) {
            // カテゴリ所有プロフィールが存在しない場合
            return;
        }

        // 同一階層のカテゴリリスト取得
        $categories = $this->profile->categories()->ofParent($this->parent)->get();

        // 表示順生成
        if ($categories->isEmpty()) {
            $this->order_number = 1;
        } else {
            $last = $categories->last();
            $this->order_number = $last->order_number + 1;
        }
    }

    /**
     * モデルの「起動」メソッド
     */
    protected static function booted(): void
    {
        // カテゴリ表示順決定
        static::addGlobalScope('order_number', function ($builder) {
            $builder->orderBy('order_number');
        });

        static::creating(function (self $model) {
            if ($model->parent) {
                // 親カテゴリが存在する場合は、カテゴリ所有プロフィールおよびカテゴリタイプは親カテゴリから継承
                $model->profile = $model->parent->profile;
                $model->type = $model->parent->type;
            }
            // カテゴリ表示順自動採番
            $model->newOrderNumber();
        });

        static::updating(function (self $model) {
            if ($model->parent) {
                // 親カテゴリが存在する場合は、カテゴリ所有プロフィールおよびカテゴリタイプは親カテゴリから継承
                $model->profile = $model->parent->profile;
                $model->type = $model->parent->type;
            }
        });
    }

    /**
     * カテゴリ所有プロフィール
     *
     * @return BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * 親カテゴリ
     * 
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * ルートカテゴリかどうか
     *
     * @return bool ルートカテゴリの場合true、そうでない場合false
     */
    protected function getIsRootAttribute(): bool
    {
        return empty($this->attributes['parent_id']);
    }

    /**
     * 子カテゴリリスト
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * 子カテゴリーが存在するかどうか
     * 
     * @return bool 存在する場合true、しない場合false
     */
    public function getHasChildAttribute(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * カテゴリ階層レベル
     * 
     * ルート階層を1として階層が下がるごとにプラス1されます。
     * 
     * @return Attribute
     */
    public function level(): Attribute
    {
        $closure = function ($attributes) {
            $e = $this;
            $level = 0;
            while ($e != null) {
                $level++;
                $e = $e->parent;
            }
            return $level;
        };

        return Attribute::make(
            get: fn($value, $attributes) => $closure->call($this, $attributes),
        );
    }

    /**
     * カテゴリ階層アップ
     * 
     * カテゴリ階層を一つ上げます。
     * 
     * カテゴリ表示順は、移動前に親カテゴリだったカテゴリの次に並ぶように調整されます。
     * 
     * カテゴリがルートカテゴリまたは移動することによりルートとなってしまう2階層目のカテゴリの場合は、何もしません（空振り）。
     * 
     * @return void
     */
    public function hierarchyUp(): void
    {
        if ($this->level <= 2) {
            // カテゴリがルートカテゴリまたは移動することによりルートとなってしまう2階層目のカテゴリの場合
            return;
        }

        DB::transaction(function () {
            // 階層を一つ上げる
            $parent = $this->parent;
            $this->order_number = $parent->order_number + 1;
            $this->parent = $parent->parent;

            // 表示順は、移動前に親カテゴリだったカテゴリの次に並ぶように調整
            $categories = $this->profile->categories()->ofParent($this->parent)->get();
            $inc = false;
            foreach ($categories as $category) {
                if ($category->is($parent)) {
                    $inc = true;
                } else if ($inc) {
                    $category->order_number++;
                    $category->save();
                }
            }

            $this->save();
        });
    }

    /**
     * カテゴリ階層ダウン
     * 
     * カテゴリの階層を一つ下げます。
     * 
     * 新たな親カテゴリは、移動前の階層の表示順でつ上のカテゴリとなります。
     * 
     * 表示順は、移動後のカテゴリ階層の最後になります。
     * 
     * カテゴリが同階層の表示順で先頭の場合は、何もしません（空振り）。
     *
     * @return void
     */
    public function hierarchyDown(): void
    {
        $prev = $this->previous();
        if ($prev == null) {
            // カテゴリが同階層の表示順で先頭の場合
            return;
        }

        // 階層をつ下げる
        // 新たな親カテゴリは、移動前の階層の表示順で一つ上のカテゴリ
        $this->parent = $prev;

        // 表示中調整
        $categories = $this->profile->categories()->ofParent($this->parent)->get();
        if ($categories->isEmpty()) {
            // 最初の子カテゴリの場合
            $this->order_number = 1;
        } else {
            // 既に子カテゴリが存在する場合
            // 表示順は、移動後のカテゴリ階層の最後
            $this->order_number = $categories->last()->order_number + 1;
        }

        $this->save();
    }

    /**
     * カテゴリ入替
     * 
     * 対象カテゴリを指定してカテゴリどうしを入替えします。
     * 
     * 入替は、カテゴリ所有プロフィールとカテゴリタイプが同じ場合のみ行われます。
     * 
     * @param Category $target 対象カテゴリ
     * @return void
     * @throws ApplicationException カテゴリ所有プロフィールが異なる場合、CategorySwapProfileMissmatch[71001]
     * @throws ApplicationException カテゴリタイプが異なる場合、CategorySwapTypeMissmatch[71002]
     */
    public function swap(Category $target): void
    {
        if ($this->profile->id != $target->profile->id) {
            // プロフィールが異なる場合
            throw new ApplicationException('CategorySwapProfileMissmatch', 71001, ['source' => $this->profile->id, 'target' => $target->profile->id]);
        }
        if ($this->type != $target->type) {
            // タイプが異なる場合
            throw new ApplicationException('CategorySwapTypeMissmatch', 71002, ['source' => $this->type, 'target' => $target->type]);
        }
        if ($this->id == $target->id) {
            // 同一カテゴリの場合
            return;
        }

        DB::transaction(
            function () use ($target) {
                if ($this->parent?->id == $target->parent?->id) {
                    // 同一階層のカテゴリどうしの場合

                    // 表示順のみ入れ替え
                    $order_number = $this->order_number;
                    $this->order_number = $target->order_number;
                    $target->order_number = $order_number;

                    $this->save();
                    $target->save();
                } else {
                    // 異なる階層のカテゴリどうしの場合

                    // 対象カテゴリの親カテゴリを自カテゴリに変更しておく
                    $new_childres = array();
                    foreach ($target->children as $child) {
                        $child->parent = $this;
                        $child->save();
                        array_push($new_childres, $child->id);
                    }
                    // 対象カテゴリの親カテゴリと入替
                    $target_parent = $target->parent;
                    if ($target_parent?->id == $this->id) {
                        // 対象カテゴリの親カテゴリが入替元カテゴリの場合
                        $target->parent = $this->parent;
                        $this->parent = $target;
                    } else {
                        // 対象カテゴリの親カテゴリが入替元カテゴリでない場合
                        $target->parent = $this->parent;
                        $this->parent = $target_parent;
                    }
                    $this->save();
                    $target->save();
                    // 子カテゴリの親カテゴリを対象カテゴリに変更
                    foreach ($this->children as $child) {
                        if (!in_array($child->id, $new_childres)) {
                            // 既に入替済みの子カテゴリは除外
                            $child->parent = $target;
                            $child->save();
                        };
                    }
                }
            }
        );
    }

    /**
     * 表示順で前
     * 
     * 表示順で一つ前のカテゴリを取得します。
     * 
     * カテゴリが既に先頭にある場合は、nullを返します。
     *
     * @return Category|null 表示順で前のカテゴリ。存在しない場合null
     */
    public function previous(): Category|null
    {
        return $this->where('profile_id', '=', $this->profile->id)
            ->where('parent_id', '=', $this->parent_id)
            ->where('order_number', '<', $this->order_number)->orderBy('order_number', 'desc')->first();
    }

    /**
     * 表示順で後
     * 
     * 表示順で一つ後のカテゴリを取得します。
     * 
     * カテゴリが既に最後にある場合は、nullを返します。
     * 
     * @return Category|null 表示順で後ろのカテゴリ。存在しない場合null
     */
    public function next(): Category|null
    {
        return $this->where('profile_id', '=', $this->profile->id)
            ->where('parent_id', '=', $this->parent_id)
            ->where('order_number', '>', $this->order_number)->orderBy('id', 'asc')->first();
    }

    /**
     * 表示順を上
     * 
     * カテゴリの表示順を同一階層内で一つ上げます。
     * 
     * 表示順が既に先頭の場合は、何もしません（空振り）。
     *
     * @return void
     */
    public function orderUp(): void
    {
        $target = $this->previous();
        if ($target) {
            // 一つ前のカテゴリーが存在する場合
            DB::transaction(
                function () use ($target) {
                    // 表示順を入れ替え
                    $prev = $target->order_number;
                    $target->order_number = $this->order_number;
                    $this->order_number = $prev;

                    $target->save();
                    $this->save();
                }
            );
        }
    }

    /**
     * 表示順を下
     * 
     * カテゴリの表示順を同一階層内で一つ下げます。
     * 
     * 表示順が既に最後の場合は、何もしません（空振り）。
     *
     * @return void
     */
    public function orderDown(): void
    {
        $target = $this->next();
        if ($target) {
            // 一つ後のカテゴリーが存在する場合
            DB::transaction(
                function () use ($target) {
                    // 表示順を入れ替え
                    $prev = $target->order_number;
                    $target->order_number = $this->order_number;
                    $this->order_number = $prev;

                    $target->save();
                    $this->save();
                }
            );
        }
    }

    /**
     * タイプを条件に含むようにクエリのスコープを設定
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * 名前を条件に含むようにクエリのスコープを設定
     */
    public function scopeOfName($query, ?string $name, SqlLikeBuilder $like = SqlLikeBuilder::All)
    {
        $like->build($query, 'name', $name);
    }

    /**
     * 親カテゴリ（nullの場合は、ルート）を条件に含むようにクエリのスコープを設定
     */
    public function scopeOfParent($query, ?Category $parent)
    {
        if (is_null(($parent))) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parent->id);
        }
    }

    /**
     * コンテンツリスト
     * 
     * 注）このリストには、未公開のコンテンツは含まれません。
     */
    public function contents()
    {
        return $this->hasMany(Relation::getMorphedModel($this->type));
    }

    // ========================== ここまで整理済み ==========================

    /**
     * 直列化されたカテゴリー階層のコレクション
     */
    public function serial(): Attribute
    {
        $closure = function () {
            $serial = collect([$this]);
            $c = $this->parent;
            while ($c != null) {
                $serial->prepend($c);
                $c = $c->parent;
            }
            return $serial;
        };

        return Attribute::make(
            get: fn($value, $attributes) => $closure->call($this),
        )->shouldCache();
    }

    /**
     * 子カテゴリーを全て削除します。
     *
     * @return void
     */
    public function deleteChildren(): void
    {
        if ($this->hasChild()) {
            foreach ($this->children as $child) {
                $child->delete();
            }
        }
    }

    /**
     * カテゴリー名を変更します。
     *
     * @param  mixed $new_name 新しいカテゴリー名
     * @return void
     */
    public function rename(string $new_name): void
    {
        $same_name_category = $this->profile->categories()->ofType($this->type)->ofName($new_name)->first();
        if ($same_name_category != null && $same_name_category->id != $this->id) {
            // 同一カテゴリー名のカテゴリーが既に存在する場合
            throw new ApplicationException('CategorySameNameExists', 71003, ['name' => $new_name]);
        }
        $this->name = $new_name;
        $this->save();
    }

    /**
     * カテゴリーを削除します。
     * 
     * @param string $name カテゴリー名
     * @param bool $hierarchically 子カテゴリーも同時に削除する場合true、削除しない場合false
     */
    public function delete(bool $hierarchically = false): void
    {
        if (!$hierarchically) {
            // 子カテゴリーは削除しない場合

            // カテゴリー取得
            $category = $this->profile->categories()->ofType($this->type)->ofName($this->name)->first();
            if ($category->hasChild()) {
                // 子カテゴリーが存在する場合エラー
                throw new ApplicationException('ChildCategoryExists', 71002, ['name' => $this->name]);
            }
        }

        // カテゴリー削除
        self::whereId($this->id)->delete();
    }

    /**
     * カテゴリー名を指定してカテゴリーを削除します。
     * 
     * @param Profile $profile プロフィール
     * @param string $type タイプ
     * @param string $name カテゴリー名
     * @param bool $hierarchically 子カテゴリーも同時に削除する場合true、削除しない場合false
     */
    public static function deleteByName(Profile $profile, string $type, string $name, bool $hierarchically = false): void
    {

        // カテゴリー取得
        $category = $profile->categories()->ofType($type)->ofName($name)->first();

        // カテゴリー削除
        $category->delete($hierarchically);
    }

    /**
     * コンテンツカウントを追加するようにクエリのスコープを設定
     */
    public function scopeAddCount($query)
    {
        $categoryTableName = with(new static)->getTable();
        $morphMap = Relation::morphMap();
        $categorizables = null;
        foreach ($morphMap as $type => $value) {
            $class = Relation::getMorphedModel($type);
            $content = new $class();
            $table = $content->getTable();
            $union = DB::table($table)
                ->selectRaw("$table.category_id, count($table.id) as count_of_contents")
                ->where("$table.is_public", true)
                ->whereNotNull("$table.category_id")
                ->where(function ($query) use ($table) {
                    // 公開レベル「全員」
                    $query->orWhere($table . '.public_level', PublicLevel::Public);
                    // 公開レベル「会員」
                    $query->orWhere(function ($query) use ($table) {
                        $query->where($table . '.public_level', PublicLevel::Member)
                            ->whereRaw('1 = ?', [!is_null(Auth::user()?->profile)]);
                    });
                    // 公開レベル「友達」
                    // TODO::友達機能未実装
                    $query->orWhere(function ($query) use ($table) {
                        $query->where($table . '.public_level', PublicLevel::Friend)
                            ->where($table . '.profile_id', Auth::user()?->profile->id);
                    });
                    // 公開レベル「自分」
                    $query->orWhere(function ($query) use ($table) {
                        $query->where($table . '.public_level', PublicLevel::Private)
                            ->where($table . '.profile_id', Auth::user()?->profile->id);
                    });
                })->groupBy("$table.category_id");
            if (is_null($categorizables)) {
                $categorizables = $union;
            } else {
                $categorizables->union($union);
            }
        }
        $query->leftJoinSub($categorizables, 'categorizables', function (JoinClause $join) use ($categoryTableName) {
            $join->on($categoryTableName . '.id', '=', 'categorizables.category_id');
        })->select(["$categoryTableName.*", 'count_of_contents']);
    }

    /**
     * ファイルパスまたはデータを指定してカテゴリイメージを保存します。
     * カテゴリイメージは、元画像の80%に圧縮したJPEG画像となります。
     * 
     * @param string $data ファイルデータ(パス|バイナリ)
     */
    public function storeImage(mixed $data): void
    {
        $this->image = 'data:image/jpeg;base64,' . base64_encode(Image::make($data)->encode(config('category.image.format'), config('category.image.quality')));
    }
}
