<?php

declare(strict_types=1);

namespace Crell\Mastobot;

class RandomizerDef
{
    public function __construct(
        public readonly string $directory,
        public readonly int $minTime,
        public readonly int $maxTime,
    ) {}


}
