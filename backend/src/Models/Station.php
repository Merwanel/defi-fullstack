<?php

namespace App\Models;

class Station
{
    public function __construct(
        public int $id,
        public string $shortName,
        public string $longName
    ) {}
}
