<?php

namespace Tests\ValueObjects\Configs;

use Feeldee\Framework\ValueObjects\Casts\CollectionArray;
use Feeldee\Framework\ValueObjects\ValueObject;
use Illuminate\Support\Collection;

class PointConfig extends ValueObject
{
    protected $casts = [
        'point_types' => CollectionArray::class,
    ];

    public function __construct(
        public Collection $point_types = new Collection([]),
    ) {
        parent::__construct();
    }
}
