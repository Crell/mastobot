<?php

declare(strict_types=1);

namespace Crell\Mastobot\Sequence;

use Crell\Mastobot\PosterDef;

class SequenceDef implements PosterDef
{
    public function __construct(
        public readonly string $directory,
        public readonly int $minHours,
        public readonly int $maxHours,
    ) {}

    public function poster(): string
    {
        return Sequence::class;
    }

    public function directory(): string
    {
        return $this->directory;
    }

    public function minSeconds(): int
    {
        return $this->minHours * 60 * 60;
    }

    public function maxSeconds(): int
    {
        return $this->maxHours * 60 * 60;
    }

    public function getRandomizedGap(): \DateInterval
    {
        $gapSeconds = \random_int($this->minSeconds(), $this->maxSeconds());
        return new \DateInterval("PT{$gapSeconds}S");
    }
}
