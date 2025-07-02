<?php

namespace Tests\ValueObjects\Configs;

use Feeldee\Framework\Models\Config;
use Feeldee\Framework\ValueObjects\ValueObject;

class CustomConfigWithModel extends ValueObject
{
    protected $fillable = ['value1', 'value2'];

    public function __construct(
        Config|null $model,
        public mixed $value1 = null,
        public mixed $value2 = null,
    ) {
        parent::__construct($model);
    }

    public function getProfileNickname(): string
    {
        return $this->model->profile->nickname;
    }
}
