<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Profile;
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
    use HasFactory, Required, SetUser;

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['type', 'name',  'image'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['id', 'profile', 'type', 'name', 'image', 'parent'];

    /**
     * 必須にする属性
     * 
     * @var array
     */
    protected $required = [
        'profile_id' => 71008,
        'type' => 71009,
        'name' => 71010,
    ];

    protected static function bootedProfile(Self $model)
    {
        if ($model->parent_id) {
            // 親カテゴリが存在する場合
            if ($model->profile_id) {
                $profile = $model->parent?->profile;
                if (!$profile) {
                    $profile = $model->parent()->first()?->profile;
                }
                // カテゴリ所有プロフィールIDが指定されている場合
                if ($profile?->id !== $model->profile_id) {
                    // カテゴリと親カテゴリでカテゴリ所有プロフィールが異なる場合
                    throw new ApplicationException(71003);
                }
            } else {
                // 親カテゴリのものを継承
                $model->profile_id = $model->parent->profile->id;
            }
        }
    }

    protected static function bootedType(Self $model)
    {
        if ($model->parent) {
            // 親カテゴリが存在する場合
            if ($model->type) {
                // カテゴリタイプが指定されている場合
                if ($model->parent->type !== $model->type) {
                    // カテゴリと親カテゴリでカテゴリタイプが異なる場合
                    throw new ApplicationException(71004);
                }
            } else {
                // 親カテゴリのものを継承
                $model->type = $model->parent->type;
            }
        }
    }

    /**
     * カテゴリ名重複チェック
     * 
     * @param Self $model モデル
     * @return void
     * @throws ApplicationException カテゴリ所有プロフィールとカテゴリタイプの中でカテゴリ名が重複している場合、71011エラーをスローします。
     */
    protected static function validateNameDuplicate(Self $model)
    {
        if ($model->profile->categories()->of($model->type)->name($model->name)->first()?->id !== $model->id) {
            // カテゴリ所有プロフィールとカテゴリタイプの中でカテゴリ名が重複している場合
            throw new ApplicationException(71011, ['ptofile_id' => $model->profile->id, 'type' => $model->type, 'name' => $model->name]);
        }
    }

    /**
     * カテゴリ表示順決定
     * 
     * 同じカテゴリ所有プロフィール、カテゴリタイプ、カテゴリ階層内で新しく追加されたカテゴリが最後に並ぶようカテゴリ表示順を自動採番します。
     * 
     * @param Self $model モデル
     * @return void
     */
    protected static function decideOrderNumber(Self $model)
    {
        if (!$model->profile) {
            // カテゴリ所有プロフィールが存在しない場合
            return;
        }

        // 同一階層のカテゴリリスト取得
        $categories = $model->profile->categories()->ofParent($model->parent)->get();

        // 表示順生成
        if ($categories->isEmpty()) {
            $model->order_number = 1;
        } else {
            $last = $categories->last();
            $model->order_number = $last->order_number + 1;
        }
    }

    /**
     * モデルの「起動」メソッド
     */
    protected static function booted(): void
    {
        // デフォルトの並び順は、カテゴリ表示順
        static::addGlobalScope('order_number', function ($builder) {
            $builder->orderBy('order_number');
        });

        static::saving(function (Self $model) {
            // カテゴリ所有プロフィール
            static::bootedProfile($model);
            // カテゴリタイプ
            static::bootedType($model);
        });

        static::creating(function (Self $model) {
            // カテゴリ名重複チェック
            static::validateNameDuplicate($model);
            // カテゴリ表示順決定
            static::decideOrderNumber($model);
        });

        static::updating(function (Self $model) {
            // カテゴリ名重複チェック
            static::validateNameDuplicate($model);
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
     * 子カテゴリが存在するかどうか
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
     * 投稿リスト
     */
    public function posts()
    {
        return $this->hasMany(Relation::getMorphedModel($this->type));
    }

    /**
     * カテゴリ階層連続リスト
     */
    public function serials(): Attribute
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
            $this->parent_id = $parent->parent?->id;

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
            $this->refresh();
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

        // 表示順
        $categories = $this->profile->categories()->ofParent($prev)->get();
        if ($categories->isEmpty()) {
            // 最初の子カテゴリの場合
            $order_number = 1;
        } else {
            // 既に子カテゴリが存在する場合
            // 表示順は、移動後のカテゴリ階層の最後
            $order_number = $categories->last()->order_number + 1;
        }

        // 階層をつ下げる
        // 新たな親カテゴリは、移動前の階層の表示順で一つ上のカテゴリ
        $this->parent_id = $prev->id;
        $this->order_number = $order_number;
        $this->save();
        $this->refresh();
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
     * @throws ApplicationException カテゴリ所有プロフィールが異なる場合、71001
     * @throws ApplicationException カテゴリタイプが異なる場合、71002
     */
    public function swap(Category $target): void
    {
        if ($this->profile->id != $target->profile->id) {
            // プロフィールが異なる場合
            throw new ApplicationException(71001, ['source' => $this->profile->id, 'target' => $target->profile->id]);
        }
        if ($this->type != $target->type) {
            // タイプが異なる場合
            throw new ApplicationException(71002, ['source' => $this->type, 'target' => $target->type]);
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
                        $child->parent_id = $this->id;
                        $child->save();
                        array_push($new_childres, $child->id);
                    }
                    // 対象カテゴリの親カテゴリと入替
                    $target_parent = $target->parent;
                    if ($target_parent?->id == $this->id) {
                        // 対象カテゴリの親カテゴリが入替元カテゴリの場合
                        $target->parent_id = $this->parent?->id;
                        $this->parent_id = $target->id;
                    } else {
                        // 対象カテゴリの親カテゴリが入替元カテゴリでない場合
                        $target->parent_id = $this->parent?->id;
                        $this->parent_id = $target_parent?->id;
                    }
                    $this->save();
                    $target->save();
                    // 子カテゴリの親カテゴリを対象カテゴリに変更
                    foreach ($this->children as $child) {
                        if (!in_array($child->id, $new_childres)) {
                            // 既に入替済みの子カテゴリは除外
                            $child->parent_id = $target->id;
                            $child->save();
                        };
                    }
                }
            }
        );
        $this->refresh();
        $target->refresh();
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
     * 削除
     * 
     * 子カテゴリが存在するカテゴリは削除できません。
     * 
     * @return bool|null
     * @throws ApplicationException カテゴリ削除において子カテゴリが存在する場合、71005
     */
    public function delete(): bool|null
    {
        if ($this->hasChild) {
            // カテゴリ削除において子カテゴリが存在する場合エラー
            throw new ApplicationException(71005, ['type' => $this->type, 'name' => $this->name]);
        }
        // カテゴリー削除
        return parent::delete();
    }

    /**
     * カテゴリ所有者による絞り込みのためのローカルスコープ
     * 
     * @param Builder $query
     * @param string|Profile|null $profile プロフィールまたはニックネーム
     * @return void
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ所有者による絞り込み
     */
    public function scopeBy($query, string|Profile|null $profile): void
    {
        if ($profile instanceof Profile) {
            $query->where('profile_id', $profile->id);
        } elseif (is_string($profile)) {
            $query->whereHas('profile', function ($q) use ($profile) {
                $q->where('nickname', $profile);
            });
        }
    }

    /**
     * カテゴリタイプによる絞り込みのためのローカルスコープ
     * 
     * @param Builder $query
     * @param Post|string $type カテゴリタイプ
     * @return void
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリタイプによる絞り込み
     */
    public function scopeOf($query, Post|string $type)
    {
        if (is_subclass_of($type, Post::class)) {
            $type = $type::type();
        }
        return $query->where('type', $type);
    }

    /**
     * カテゴリ名による絞り込みのためのローカルスコープ
     * 
     * @param Builder $query
     * @param string|null $name カテゴリ名
     * @param Like $like LIKE列挙型（デフォルトは、完全一致）
     * @return void
     * @link https://github.com/ryossi/feeldee-framework/wiki/カテゴリ#カテゴリ名による絞り込み
     */
    public function scopeName($query, ?string $name, Like $like = Like::All): void
    {
        $like->build($query, 'name', $name);
    }

    // ========================== ここまで整理済み ==========================

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
     * 投稿カウントを追加するようにクエリのスコープを設定
     */
    public function scopeAddCount($query)
    {
        $categoryTableName = with(new static)->getTable();
        $morphMap = Relation::morphMap();
        $categorizables = null;
        foreach ($morphMap as $type => $value) {
            $class = Relation::getMorphedModel($type);
            $post = new $class();
            $table = $post->getTable();
            $union = DB::table($table)
                ->selectRaw("$table.category_id, count($table.id) as count_of_posts")
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
        })->select(["$categoryTableName.*", 'count_of_posts']);
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
