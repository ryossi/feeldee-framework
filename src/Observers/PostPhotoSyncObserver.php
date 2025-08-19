<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Models\Post;
use PHPHtmlParser\Dom;

class PostPhotoSyncObserver
{
    protected function sync(Post $model): void
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
     * Handle the Post "updated" event.
     *
     * @param  \Feeldee\Framework\Models\Post  $model
     * @return void
     */
    public function saved(Post $model)
    {
        // 写真リスト同期
        $this->sync($model);

        // モデルリフレッシュ
        $model->refresh();
    }

    /**
     * Handle the Post "deleting" event.
     *
     * @param  \Feeldee\Framework\Models\Post  $model
     * @return void
     */
    public function deleting(Post $model)
    {
        // 投稿写真リスト削除
        $model->photos()->delete();
    }
}
