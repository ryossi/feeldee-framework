<?php

namespace Feeldee\Framework\Observers;

use Feeldee\Framework\Models\Journal;
use PHPHtmlParser\Dom;

class PostPhotoShareObserver
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
                $photo = $model->profile->photos()->ofSrc($image->src)->first();
                if ($photo === null) {
                    $photo = $model->profile->photos()->create([
                        'src' => $image->src,
                        'posted_at' => $model->posted_at,
                    ]);
                }
                array_push($photo_ids, $photo->id);
            }
        }
        $model->photos()->sync($photo_ids);
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
}
