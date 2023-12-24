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
        $log = new Logger('name');
        $log->pushHandler(new StreamHandler($file, Level::Warning));
    }

    public function put($value, $type)
    {
        if ($type === self::Warning) {
            $log->Warning($value);
        } elseif ($type === self::Error) {
            $log->error($value);
        }
    }
}
