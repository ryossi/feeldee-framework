<?php

namespace Feeldee\Framework\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Lang;

class ChangeEmail extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject(Lang::get('ChangeEmailSubject'))
            ->line(Lang::get('ChangeEmailMessage'))
            ->action(Lang::get('ChangeEmailAction'), $url)
            ->line(Lang::get('EmailFooterMessage'));
    }

    /**
     * 署名付きのURLを生成します。
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'account.email.change.confirm',
            Carbon::now()->addMinutes(Config::get('feeldee.account.confirmation.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->new_email),
            ]
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
