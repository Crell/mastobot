<?php

declare(strict_types=1);

namespace Crell\Mastobot\Sequence;

use Crell\Mastobot\PosterState;

class SequenceState implements PosterState
{
    public function __construct(
        public ?\DateTimeImmutable $nextPostTime = null,
        public ?string $lastStatus = null,
    ) {}
}
