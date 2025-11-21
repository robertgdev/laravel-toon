<?php

namespace Datasets;

use RobertGDev\LaravelToon\Concerns\ToonFormat;
use RobertGDev\LaravelToon\Contracts\Toonable;

class UserDTO implements Toonable
{
    use ToonFormat;

    public function __construct(
        public string $name,
        public int $age,
        public array $roles,
    ) {}
}