<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Profile;
use Feeldee\Framework\Models\SqlLikeBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;

/**
 * カテゴリーをあらわすモデル
 */
class Category extends Model
{
    use HasFactory, SetUser;

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
     * モデルの「起動」メソッド
     */
    protected static function booted(): void
    {
        static::addGlobalScope('order_number', function ($builder) {
            $builder->orderBy('order_number');
        });

        static::creating(function (self $category) {
            // 表示順割り当て
            $category->newOrderNumber();
        });
    }

    /**
     * カテゴリを作成します。
     * 
     * @param array $attributes カテゴリの属性
     * @param Profile $profile カテゴリ所有プロフィール
     * @return self カテゴリ
     */
    public static function create(array $attributes = [], Profile $profile): self
    {
        // バリデーション
        Validator::validate($attributes, [
            // カテゴリタイプ
            'type' => 'required|string|max:255',
            // カテゴリ名
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($profile, $attributes) {
                    // カテゴリ名重複チェック
                    return $query->where('profile_id', $profile->id)
                        ->where('type', $attributes['type'] ?? null);
                }),
            ],
        ]);

        // カテゴリ作成
        return $profile->categories()->create($attributes);
    }

    // /**
    //  * カテゴリーを追加します。
    //  * 
    //  * @param Profile $profile プロフィール
    //  * @param string $type タイプ
    //  * @param string $name カテゴリー名
    //  * @param mixed $parent 親カテゴリー（カテゴリー|カテゴリー名）
    //  * @return self 追加したカテゴリー
    //  */
    // public static function add(Profile $profile, string $type, string $name, mixed $parent = null): self
    // {
    //     // カテゴリー名重複チェック
    //     if ($profile->categories()->ofType($type)->ofName($name)->exists()) {
    //         // 同一カテゴリー名のカテゴリーが既に存在する場合
    //         throw new ApplicationException('CategorySameNameExists', 71003, ['name' => $name]);
    //     }

    //     if (is_string($parent)) {
    //         // カテゴリー名の場合

    //         // 親カテゴリー存在チェック
    //         $parent_name = $parent;
    //         $parent = $profile->categories()->ofType($type)->ofName($parent_name)->first();
    //         if ($parent == null) {
    //             // 親カテゴリーが見つからない場合
    //             throw new ApplicationException('CategoryParentNotFound', 71001, ['parent_name' => $parent_name]);
    //         }
    //     }

    //     // 投稿カテゴリー新規作成
    //     $category = Category::create([
    //         'profile' => $profile,
    //         'type' => $type,
    //         'name' => $name,
    //         'parent' => $parent
    //     ]);
    //     return $category;
    // }

    /**
     * カテゴリ所有プロフィール
     *
     * @return Attribute
     */
    protected function profile(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->belongsTo(Profile::class, 'profile_id')->get()->first(),
            set: fn($value) => [
                'profile_id' => $value?->id
            ]
        );
    }

    // ========================== ここまで整理済み ==========================

    /**
     * 同一階層の最後に表示順を新しく割り当てます。
     */
    protected function newOrderNumber()
    {
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
     * このカテゴリーの親カテゴリー
     */
    public function parent(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->belongsTo(Category::class, 'parent_id')->get()->first(),
            set: fn($value) => [
                'parent_id' => $value ? $value->id : null
            ]
        );
    }

    /**
     * このカテゴリーの子カテゴリーリスト
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * このカテゴリーに所属するコンテンツリスト
     * 注）このリストには、未公開のコンテンツは含まれません。
     */
    public function contents()
    {
        return $this->hasMany(Relation::getMorphedModel($this->type));
    }

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
     * カテゴリーの階層を取得します。
     * ルート階層を1として階層が下がるごとにプラス1されま
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
        )->shouldCache();
    }

    /**
     * 子カテゴリーが存在するかどうかを判定します。
     * 
     * @return bool 存在する場合true、しない場合false
     */
    public function hasChild(): bool
    {
        return $this->children()->count() > 0;
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
     * 表示順で一つ前のカテゴリーを取得します。
     *
     * @return mixed 一つ前のカテゴリー。存在しない場合null
     */
    public function previous(): mixed
    {
        return $this->where('profile_id', '=', $this->profile->id)
            ->where('parent_id', '=', $this->parent_id)
            ->where('order_number', '<', $this->order_number)->orderBy('order_number', 'desc')->first();
    }

    /**
     * 表示順で一つ後のカテゴリーを取得します。
     * 
     * @return mixed 一つ後のカテゴリー。存在しない場合null
     */
    public function next(): mixed
    {
        return $this->where('profile_id', '=', $this->profile->id)
            ->where('parent_id', '=', $this->parent_id)
            ->where('order_number', '>', $this->order_number)->orderBy('id', 'asc')->first();
    }

    /**
     * カテゴリーの表示順を同一階層内で一つ上げます。
     * 表示順が既に先頭の場合は、何もしません（空振り）。
     *
     * @return void
     */
    public function orderUp(): void
    {
        $target = $this->previous();
        if ($target) {
            // 一つ前のカテゴリーが存在する場合
            // 表示順を入れ替え
            $prev = $target->order_number;
            $target->order_number = $this->order_number;
            $this->order_number = $prev;

            $target->save();
            $this->save();
        }
    }

    /**
     * カテゴリーの表示順を同一階層内で一つ下げます。
     * 表示順が既に最後の場合は、何もしません（空振り）。
     *
     * @return void
     */
    public function orderDown(): void
    {
        $target = $this->next();
        if ($target) {
            // 一つ後のカテゴリーが存在する場合
            // 表示順を入れ替え
            $prev = $target->order_number;
            $target->order_number = $this->order_number;
            $this->order_number = $prev;

            $target->save();
            $this->save();
        }
    }

    /**
     * カテゴリーの階層を一つ上げます。
     * 表示順は、移動前に親カテゴリーだったカテゴリーの次に並ぶように調整されます。
     * カテゴリーがルートカテゴリーまたは移動することによりルートとなってしまう2階層目のカテゴリーの場合は、何もしません（空振り）。
     * 
     * @return void
     */
    public function hierarchyUp(): void
    {
        if ($this->level <= 2) {
            // カテゴリーがルートカテゴリーまたは移動することによりルートとなってしまう2階層目のカテゴリーの場合
            return;
        }

        // 階層を一つ上げる
        $parent = $this->parent;
        $this->order_number = $parent->order_number + 1;
        $this->parent = $parent->parent;

        // 表示順は、移動前に親カテゴリーだったカテゴリーの次に並ぶように調整
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
    }

    /**
     * カテゴリーの階層を一つ下げます。
     * 新たな親カテゴリーは、移動前の階層の表示順で一つ上のカテゴリーとなります。
     * 表示順は、移動後のカテゴリー階層の最後になります。
     * カテゴリーが同階層の表示順で先頭の場合は、何もしません（空振り）。
     *
     * @return void
     */
    public function hierarchyDown(): void
    {
        $prev = $this->previous();
        if ($prev == null) {
            // カテゴリーが同階層の表示順で先頭の場合
            return;
        }

        // 階層を一つ下げる
        // 新たな親カテゴリーは、移動前の階層の表示順で一つ上のカテゴリー
        $this->parent = $prev;

        // 表示中調整
        $categories = $this->profile->categories()->ofParent($this->parent)->get();
        if ($categories->isEmpty()) {
            // 最初の子カテゴリーの場合
            $this->order_number = 1;
        } else {
            // 既に子カテゴリーが存在する場合
            // 表示順は、移動後のカテゴリー階層の最後
            $this->order_number = $categories->last()->order_number + 1;
        }

        $this->save();
    }

    /**
     * ソース名とターゲット名を指定してカテゴリを入れ替えます。
     * 
     * @param Profile $profile プロフィール
     * @param string $type タイプ
     * @param string $source_name ソースカテゴリ名
     * @param string $target_name ターゲットカテゴリ名
     * @return bool 入れ替えした場合true
     */
    public static function swap(Profile $profile, string $type, string $source_name, string $target_name): bool
    {
        $source = $profile->categories()->ofType($type)->ofName($source_name)->first();
        if ($source === null) {
            return false;
        }

        $target = $profile->categories()->ofType($type)->ofName($target_name)->first();
        if ($target === null) {
            return false;
        }

        if ($source->parent === $target->parent) {
            // 同一階層のカテゴリどうしの場合

            // 表示順のみ入れ替え
            $order_number = $source->order_number;
            $source->order_number = $target->order_number;
            $target->order_number = $order_number;
        } else {
            // 異なる階層のカテゴリどうしの場合

            // 階層を入れ替える（表示順は、それぞれを維持）
            $parent = $source->parent;
            $source->parent = $target->parent;
            $target->parent = $parent;
        }

        $source->save();
        $target->save();
        return true;
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
