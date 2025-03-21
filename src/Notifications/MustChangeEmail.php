<?php

namespace Feeldee\Framework\Notifications;

trait MustChangeEmail
{
    /**
     * 新メールアドレスを保存します。
     *
     * @return bool 成功した場合true
     */
    public function saveNewEmail($new_email)
    {
        return $this->forceFill([
            'new_email' => $new_email,
        ])->save();
    }

    /**
     * 新メールアドレスをメールアドレスとして登録します。
     */
    public function confirmNewEmail()
    {
        return $this->forceFill([
            'email' => $this->new_email,
            'new_email' => null
        ])->save();
    }

    /**
     * 新メールアドレスを取得します。
     *
     * @return string 新メールアドレス
     */
    public function getNewEmailForConfirmation()
    {
        return $this->new_email;
    }
}
