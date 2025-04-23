<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Models\Post;
use PHPHtmlParser\Dom;

class PostPhotoShareObserver
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
                $photo = $model->profile->photos()->ofSrc($image->src)->first();
                if ($photo === null) {
                    $photo = $model->profile->photos()->create([
                        'src' => $image->src,
                        'regist_datetime' => $model->post_date,
                    ]);
                }
                array_push($photo_ids, $photo->id);
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
    public function saved(Post $model)
    {
        // 写真リスト同期
        $this->sync($model);

        // モデルリフレッシュ
        $model->refresh();
    }
}
