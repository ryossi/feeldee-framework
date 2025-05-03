<?php

namespace Tests\ValueObjects\Configs;

use Feeldee\Framework\ValueObjects\ValueObject;

class CustomConfig1 extends ValueObject
{
    const TYPE = 'custom_config_1';

    protected $fillable = ['value1', 'value2'];

    public function __construct(
        public mixed $value1 = null,
        public mixed $value2 = null,
    ) {
        parent::__construct(self::TYPE);
    }
}
