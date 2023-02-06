<?php

declare(strict_types=1);

namespace Crell\Mastobot\Mastodon\Model;

class Dimensions
{
    public function __construct(
        public int $width,
        public int $height,
        public string $size,
        public float $aspect,
    ) {}
}