<?php

namespace Tests\ValueObjects\Configs;

use Feeldee\Framework\ValueObjects\ValueObject;

class CustomConfig extends ValueObject
{
    protected $fillable = ['value1', 'value2'];

    protected $excludes = ['excluded'];

    public function __construct(
        public mixed $value1 = null,
        public mixed $value2 = null,
        public string $excluded = '',
    ) {
        parent::__construct();
    }
}
