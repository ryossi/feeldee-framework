<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Models\Journal;
use PHPHtmlParser\Dom;

class PostPhotoSyncObserver
{
    protected function sync(Journal $model): void
    {
        // 投稿の記事内容から写真を登録
        $value = $model->value;
        $photo_ids = array();
        if (!empty($value)) {
            $dom = new Dom();
            $dom->loadStr($value);
            $images = $dom->find('img');
            foreach ($images as $image) {
                $photo = $model->photos()->ofSrc($image->src)->first();
                if ($photo === null) {
                    $photo = $model->profile->photos()->create([
                        'src' => $image->src,
                        'posted_at' => $model->posted_at,
                    ]);
                    $model->photos()->attach($photo->id);
                }
                array_push($photo_ids, $photo->id);
            }
        }
        // 投稿で使用されなくなった写真は削除
        foreach ($model->photos as $photo) {
            if (!in_array($photo->id, $photo_ids)) {
                $photo->delete();
            }
        }
    }

    /**
     * Handle the Journal "updated" event.
     *
     * @param  \Feeldee\Framework\Models\Journal  $model
     * @return void
     */
    public function saved(Journal $model)
    {
        // 写真リスト同期
        $this->sync($model);

        // モデルリフレッシュ
        $model->refresh();
    }

    /**
     * Handle the Journal "deleting" event.
     *
     * @param  \Feeldee\Framework\Models\Journal  $model
     * @return void
     */
    public function deleting(Journal $model)
    {
        // 投稿写真リスト削除
        $model->photos()->delete();
    }
}
