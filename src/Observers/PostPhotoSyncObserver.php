<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Models\Post;
use PHPHtmlParser\Dom;

class PostPhotoSyncObserver
{
    protected function sync(Post $model): void
    {
        // モデルリフレッシュ
        $model->refresh();

        // 投稿写真の登録と削除
        $value = $model->getRawOriginal('value');
        $photo_ids = array();
        if (!empty($value)) {
            $dom = new Dom();
            $dom->loadStr($value);
            $images = $dom->find('img');
            foreach ($images as $image) {
                $photo = $model->photos()->src($image->src)->first();
                if ($photo === null) {
                    $photo = $model->profile->photos()->create([
                        'src' => $image->src,
                        'regist_datetime' => $model->post_date,
                        'is_public' => $model->is_public,
                        'public_level' => $model->public_level
                    ]);
                }
                array_push($photo_ids, $photo->id);
            }
        }
        foreach ($model->photos as $photo) {
            if (!in_array($photo->id, $photo_ids)) {
                // 投稿で使用されなくなった写真は削除
                $photo->delete();
            }
        }
        $model->photos()->sync($photo_ids);
    }

    /**
     * Handle the Post "updated" event.
     *
     * @param  \Feeldee\Framework\Models\Post  $model
     * @return void
     */
    public function saving(Post $model)
    {
        // 写真リスト同期
        $this->sync($model);
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
