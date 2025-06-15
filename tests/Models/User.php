<?php

namespace Tests\Models;

use Feeldee\Framework\Models\HasProfile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * ユーザをあらわすモデル
 */
class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    use  HasFactory, HasProfile;
}
