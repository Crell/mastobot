<?php

declare(strict_types=1);

namespace Crell\Mastobot;

class BatchRandomizerDef
{
    public function __construct(
        public readonly string $directory,
        public readonly int $minHours,
        public readonly int $maxHours,
    ) {}

    public function minSeconds(): int
    {
        return $this->minHours * 60 * 60;
    }

    public function maxSeconds(): int
    {
        return $this->maxHours * 60 * 60;
    }
}
