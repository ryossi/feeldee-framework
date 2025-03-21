<?php

namespace Feeldee\Framework\Models;

use App\Notifications\ChangeEmail;
use App\Notifications\MustChangeEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * ユーザをあらわすモデル
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, MustChangeEmail;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'new_email',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * プロフィールリストを取得します。
     */
    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }

    /**
     * プロフィールを取得します。
     */
    public function profile()
    {
        return $this->hasOne(Profile::class)->oldestOfMany();
    }

    /**
     * メディアボックスを取得します。
     */
    public function mediaBox()
    {
        return $this->hasOne(MediaBox::class);
    }

    /**
     * メールチャンネルに対する通知をルートする
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array|string
     */
    public function routeNotificationForMail($notification)
    {
        if ($notification instanceof ChangeEmail) {
            // メールアドレス変更通知の場合、新メールアドレスへ送信
            return $this->new_email;
        } else {
            return $this->email;
        }
    }
}
