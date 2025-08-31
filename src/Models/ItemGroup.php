<?php

namespace Feeldee\Framework\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * アイテムグループをあらわすモデル
 */
class ItemGroup extends Model
{
    use HasFactory, SetUser;

    protected $fillable = ['name'];

    protected $visible = ['id', 'name', 'items'];

    protected $with = ['items'];

    /**
     * アイテムグループを所有する投稿
     */
    public function post()
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * アイテムグループに所属するアイテム
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'posted_items');
    }

    /**
     * アイテムグループ名リストを取得します。
     * 
     * @param Profile $profile プロフィール
     * @param mixed $term 検索条件（nullの場合条件なし）
     * @param SqlLikeBuilder $like 検索条件一致タイプ（デフォルは前方一致）
     * @return Collection アイテムグループ名リスト
     * 
     */
    public static function findNameList(Profile $profile, mixed $term = null, SqlLikeBuilder $like = SqlLikeBuilder::Prefix): Collection
    {
        $itemGroupTbleName = with(new static)->getTable();
        $postTableName = with(new Journal())->getTable();
        $sql = self::join($postTableName, $postTableName . '.id', '=', $itemGroupTbleName . '.post_id')
            ->where($postTableName . '.profile_id', $profile->id)
            ->select($itemGroupTbleName . '.name')
            ->groupBy($itemGroupTbleName . '.name')
            ->orderBy($itemGroupTbleName . '.name');
        if ($term !== null) {
            $like->build($sql, $itemGroupTbleName . '.name', $term);
        }
        return $sql->get()->transform(function ($item, $key) {
            return $item->name;
        });
    }
}
