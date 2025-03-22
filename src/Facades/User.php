<?php

namespace Feeldee\Framework\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Feeldee\Framework\Facades\User
 *
 * @method static ?Profile profile(string $nickname = null)
 */
class User extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Feeldee\Framework\Services\UserService::class;
    }
}
