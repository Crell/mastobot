<?php

declare(strict_types=1);

namespace Crell\Mastobot\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

class FrozenClock implements ClockInterface
{
    public function __construct(private readonly \DateTimeImmutable $now) {}

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}
