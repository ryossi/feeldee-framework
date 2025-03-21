<?php

namespace Feeldee\Framework\Services;

use Feeldee\Framework\Models\Track;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class TrackingService
{
    const KEY = 'feeldee_track_uid';

    /**
     * トラッキングを開始します。
     */
    public function start(): void
    {
        if (config('feeldee.tracking.enable')) {
            $track = Track::find(request()->cookie(self::KEY));
            if (!$track) {
                // 追跡情報が存在しない場合

                // 追跡情報を新規作成
                $userAgent = request()->header('User-Agent');
                $ip_address = request()->ip();
                $track = Track::create([
                    'ip_address' => $ip_address,
                    'user_agent' => $userAgent,
                    'user_id' => Auth::id()
                ]);
                Cookie::queue(self::KEY, $track->uid, config('feeldee.tracking.lifetime'));
            } else {
                if (config('feeldee.tracking.continuation', false)) {
                    // 追跡自動延長
                    Cookie::queue(self::KEY, $track->uid, config('feeldee.tracking.lifetime'));
                }
            }

            // セッションにUIDを一時保存
            session()->flash(self::KEY, $track->uid);
        }
    }

    /**
     * UIDを返却します。
     */
    public function uid(): ?string
    {
        return config('feeldee.tracking.enable') ? session(self::KEY) : null;
    }
}
