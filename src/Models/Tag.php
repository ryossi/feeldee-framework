<?php

namespace Feeldee\Framework\Models;

use Feeldee\Framework\Exceptions\ApplicationException;
use Feeldee\Framework\Models\Profile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * タグをあらわすモデル
 */
class Tag extends Model
{
    use HasFactory, Required, SetUser;

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = ['profile', 'type', 'name', 'contents'];

    /**
     * 配列に表示する属性
     *
     * @var array
     */
    protected $visible = ['id', 'name', 'count_of_contents'];

    /**
     * 必須にする属性
     * 
     * @var array
     */
    protected $required = [
        'profile_id' => 72001,
        'type' => 72002,
        'name' => 72003,
    ];


    protected static function bootedName(Self $model)
    {
        if ($model->profile->tags()->ofType($model->type)->ofName($model->name)->first()?->id !== $model->id) {
            // タグ所有プロフィールとタグタイプの中でタグ名が重複している場合
            throw new ApplicationException(72004, ['ptofile_id' => $model->profile->id, 'type' => $model->type, 'name' => $model->name]);
        }
    }

    protected static function bootedOrderNumber(Self $model)
    {
        // 同一タイプの全てのタグリスト取得
        $tag_list = $model->profile->tags()->ofType($model->type)->get();

        // 表示順生成
        if ($tag_list->isEmpty()) {
            $model->order_number = 1;
        } else {
            $last = $tag_list->last();
            $model->order_number = $last->order_number + 1;
        }

        if (!$model->profile) {
            // カテゴリ所有プロフィールが存在しない場合
            return;
        }
    }

    private $_contents = null;

    /**
     * モデルの「起動」メソッド
     */
    protected static function booted(): void
    {
        // デフォルトの並び順は、カテゴリ表示順
        static::addGlobalScope('order_number', function ($builder) {
            $builder->orderBy('order_number');
        });

        static::creating(function (Self $model) {
            // タグ名
            static::bootedName($model);
            // カテゴリ表示順
            static::bootedOrderNumber($model);
        });

        static::updating(function (Self $model) {
            // タグ名
            static::bootedName($model);
        });

        static::saving(function (Self $model) {
            if ($model->type) {
                // コンテンツリストに直接コレクションが設定されている場合には、
                // ローカルコンテンツリストに一時的に保存
                $model->_contents = $model->contents;
                unset($model['contents']);
            }
        });

        static::saved(function (Self $model) {
            if ($model->_contents->isNotEmpty()) {
                // ローカルコンテンツリストを
                $id = Auth::id();
                $ids = array();
                foreach ($model->_contents as $content) {
                    if ($model->profile_id !== $content->profile_id) {
                        // タグ所有プロフィールとコンテンツ所有プロフィールが一致しない場合
                        throw new ApplicationException(72005);
                    }
                    if ($model->type !== $content::type()) {
                        // タグタイプとコンテンツ種別が一致しない場合
                        throw new ApplicationException(72006);
                    }
                    $ids[$content->id] = [
                        'taggable_type' => $model->type,
                        'created_by' => $id,
                        'updated_by' => $id
                    ];
                }
                $model->contents()->sync($ids);
            }
            $model->_contents = null;
        });
    }

    /**
     * タグ所有プロフィール
     *
     * @return BelongsTo
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * コンテンツリスト
     */
    public function contents()
    {
        return $this->belongsToMany(Relation::getMorphedModel($this->type), 'taggables', 'tag_id', 'taggable_id');
    }

    /**
     * 表示順で一つ前のタグを取得します。
     *
     * @return mixed 一つ前のタグ。存在しない場合null
     */
    public function previous(): mixed
    {
        return $this->where('profile_id', '=', $this->profile->id)
            ->where('order_number', '<', $this->order_number)->orderBy('order_number', 'desc')->first();
    }

    /**
     * 表示順で一つ後のタグを取得します。
     * 
     * @return mixed 一つ後のタグ。存在しない場合null
     */
    public function next(): mixed
    {
        return $this->where('profile_id', '=', $this->profile->id)
            ->where('order_number', '>', $this->order_number)->orderBy('id', 'asc')->first();
    }

    /**
     * タグの表示順を一つ上げます。
     * 表示順が既に先頭の場合は、何もしません（空振り）。
     *
     * @return void
     */
    public function orderUp(): void
    {
        $target = $this->previous();
        if ($target) {
            // 一つ前のタグが存在する場合
            // 表示順を入れ替え
            $prev = $target->order_number;
            $target->order_number = $this->order_number;
            $this->order_number = $prev;

            $target->save();
            $this->save();
        }
    }

    /**
     * タグの表示順を一つ下げます。
     * 表示順が既に最後の場合は、何もしません（空振り）。
     *
     * @return void
     */
    public function orderDown(): void
    {
        $target = $this->next();
        if ($target) {
            // 一つ後のタグが存在する場合
            // 表示順を入れ替え
            $prev = $target->order_number;
            $target->order_number = $this->order_number;
            $this->order_number = $prev;

            $target->save();
            $this->save();
        }
    }

    /**
     * タグタイプを条件に含むようにクエリのスコープを設定
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * タグ名を条件に含むようにクエリのスコープを設定
     */
    public function scopeOfName($query, ?string $name, SqlLikeBuilder $like = SqlLikeBuilder::All)
    {
        $like->build($query, 'name', $name);
    }

    // ========================== ここまで整理済み ==========================

    /**
     * このタグから該当のコンテンツを除去します。
     * 
     * @param ?array $ids ID配列
     */
    public function unlink(?array $ids): void
    {
        if (!is_array($ids)) return;

        $eliminates = $this->contents()->whereIn('id', $ids)->get();
        foreach ($eliminates as $content) {
            $content->delete();
        }
    }

    /**
     * コンテンツカウントを追加するようにクエリのスコープを設定
     */
    public function scopeAddCount($query)
    {
        $tagTableName = with(new static)->getTable();
        $morphMap = Relation::morphMap();
        $taggables = null;
        foreach ($morphMap as $type => $value) {
            $class = Relation::getMorphedModel($type);
            $content = new $class();
            $table = $content->getTable();
            $union = DB::table($table)->join('taggables', 'taggables.taggable_id', '=', "$table.id")
                ->join($tagTableName, "$tagTableName.id", '=', 'taggables.tag_id')
                ->selectRaw('taggables.tag_id, count(taggables.tag_id) as count_of_contents')
                ->where('is_public', true)
                ->where('type', $type)
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
                })->groupBy('taggables.tag_id');
            if (is_null($taggables)) {
                $taggables = $union;
            } else {
                $taggables->union($union);
            }
        }
        $query->leftJoinSub($taggables, 'taggables', function (JoinClause $join) use ($tagTableName) {
            $join->on($tagTableName . '.id', '=', 'taggables.tag_id');
        })->select(["$tagTableName.*", 'count_of_contents']);
    }

    /**
     * ソース名とターゲット名を指定してタグを入れ替えます。
     * 
     * @param Profile $profile プロフィール
     * @param string $type タイプ
     * @param string $source_name ソースタグ名
     * @param string $target_name ターゲットタグ名
     * @return bool 入れ替えした場合true
     */
    public static function swap(Profile $profile, string $type, string $source_name, string $target_name): bool
    {
        $source = $profile->tags()->ofType($type)->ofName($source_name)->first();
        if ($source === null) {
            return false;
        }

        $target = $profile->tags()->ofType($type)->ofName($target_name)->first();
        if ($target === null) {
            return false;
        }

        $order_number = $source->order_number;
        $source->order_number = $target->order_number;
        $target->order_number = $order_number;
        $source->save();
        $target->save();
        return true;
    }
}
