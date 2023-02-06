<?php

declare(strict_types=1);

namespace Crell\Mastobot\Mastodon\Model;

use Crell\Serde\Attributes\PostLoad;

class Point
{
    public function __construct(
        public float $x,
        public float $y,
    ) {}

    public function asString(): string
    {
        return "$this->x,$this->y";
    }

    #[PostLoad]
    private function validate(): void
    {
        if ($this->x < -1 || $this->x > 1 || $this->y < -1 || $this->y > 1) {
            throw new \OutOfBoundsException('x and y coordinates must be between -1 and 1.');
        }
    }
}
