<?php

namespace Feeldee;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

class MyLog
{
    private $log = null;
    public const Warning = 1;
    public const Error = 2;

    public function __construct($file)
    {
        $this->log = new Logger('name');
        $this->log->pushHandler(new StreamHandler($file, Level::Warning));
    }

    public function put($value, $type)
    {
        if ($type === self::Warning) {
            $this->log->Warning($value);
        } elseif ($type === self::Error) {
            $this->log->error($value);
        }
    }
}
