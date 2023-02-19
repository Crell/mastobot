<?php

declare(strict_types=1);

namespace Crell\Mastobot\Clock;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

/**
 * @codeCoverageIgnore
 */
class UtcClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}
