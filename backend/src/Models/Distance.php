<?php

namespace App\Models;

class Distance
{
    public function __construct(
        public int $id,
        public string $lineName,
        public int $parentId,
        public int $childId,
        public float $distance
    ) {}
}
