<?php

namespace Feeldee\Framework\Console\Commands;

use Feeldee\Framework\Models\Photo;
use Illuminate\Console\Command;

class RefreshPhotoTypeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feeldee:refresh-photo-type {mode? : 写真タイプが未設定のもののみ更新する場合はnullOnly}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '最新の写真タイプマッピングコンフィグレーション設定値に従って写真タイプを一律更新します。';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('写真タイプ一括更新開始');

        $mapping = config('feeldee.photo_types', []);
        if (empty($mapping)) {
            $this->warn('設定 feeldee.photo_types が空です。処理を終了します。');
            return 0;
        }

        // 引数を真偽値に変換
        $nullOnly = filter_var($this->argument('mode') === 'nullOnly', FILTER_VALIDATE_BOOLEAN);

        $updatedCount = 0;

        // マッピングごとの更新
        foreach ($mapping as $type => $pattern) {
            // パターンが空ならスキップ
            if (!is_string($pattern) || $pattern === '') {
                $this->warn("マッピング {$type} のパターンが空のためスキップします。");
                continue;
            }

            $count = 0;
            // まずは DB の REGEXP を使ってできるだけまとめて更新する
            try {
                $query = Photo::whereRaw('src REGEXP ?', [$pattern]);
                if ($nullOnly) {
                    $query->whereNull('photo_type');
                }
                $count = $query->update(['photo_type' => $type]);
            } catch (\Throwable $e) {
                // DB 側で正規表現が使えない等の可能性があるためフォールバック
                $this->warn("DBでREGEXP更新できませんでした。フォールバックで個別更新します。({$type})");
                $q = Photo::query();
                if ($nullOnly) {
                    $q->whereNull('photo_type');
                }
                // cursor で回して preg_match を使う
                foreach ($q->cursor() as $photo) {
                    if ($photo->src !== null && @preg_match($pattern, $photo->src)) {
                        $photo->photo_type = $type;
                        $photo->save();
                        $count++;
                    }
                }
            }

            $updatedCount += $count;
            $this->info("マッピング: {$type} => {$pattern} : 更新件数 {$count}");
        }

        // マッピングに一致しないものは NULL にする（nullOnly が true の場合は既に NULL のもののみ処理する挙動のためスキップ）
        if (!$nullOnly) {
            // 有効なパターンを収集
            $validPatterns = [];
            foreach ($mapping as $p) {
                if (is_string($p) && $p !== '') {
                    $validPatterns[] = $p;
                }
            }

            if (!empty($validPatterns)) {
                $clearedCount = 0;
                // DB 側でまとめて NOT REGEXP 更新を試みる
                try {
                    // パターンを OR で結合して「どれにもマッチしない」ものを検索
                    $combined = implode('|', array_map(function ($pat) {
                        return "($pat)";
                    }, $validPatterns));
                    $q = Photo::whereRaw('src NOT REGEXP ?', [$combined]);
                    // nullOnly が false のためここでは whereNull は付けない
                    $clearedCount = $q->update(['photo_type' => null]);
                } catch (\Throwable $e) {
                    $this->warn("DBでNOT REGEXP更新できませんでした。フォールバックで個別更新します。");
                    $q = Photo::query();
                    foreach ($q->cursor() as $photo) {
                        $matched = false;
                        if ($photo->src !== null) {
                            foreach ($validPatterns as $pat) {
                                if (@preg_match($pat, $photo->src)) {
                                    $matched = true;
                                    break;
                                }
                            }
                        }
                        if (!$matched) {
                            if ($photo->photo_type !== null) {
                                $photo->photo_type = null;
                                $photo->save();
                                $clearedCount++;
                            }
                        }
                    }
                }

                $updatedCount += $clearedCount;
                $this->info("非マッピングのものをNULLに設定: 更新件数 {$clearedCount}");
            } else {
                $this->info('有効なマッピングパターンが存在しないため、非マッピングのもののクリア処理は行いません。');
            }
        } else {
            $this->info('nullOnly=true のため、非マッピングのものを NULL にする処理はスキップします。');
        }

        $this->info("写真タイプ一括更新終了。更新件数: {$updatedCount} 件");
        return 0;
    }
}
