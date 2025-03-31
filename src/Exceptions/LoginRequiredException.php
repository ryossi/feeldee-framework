<?php

namespace Feeldee\Framework\Exceptions;

use Feeldee\Framework\Exceptions\ApplicationException;

/**
 * ログイン必須例外
 *
 * @package Feeldee\Framework\Exceptions
 */
class LoginRequiredException extends ApplicationException
{
    protected $code = 801;
    protected $message = 'Login is required to access this resource.';

    function __construct()
    {
        parent::__construct($this->message, $this->code, [], true);
    }
}
