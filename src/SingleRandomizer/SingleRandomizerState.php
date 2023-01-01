<?php

declare(strict_types=1);

namespace Crell\Mastobot\SingleRandomizer;

use Crell\Mastobot\PosterState;

class SingleRandomizerState implements PosterState
{
    public function __construct(
        public ?\DateTimeImmutable $nextPostTime = null
    ) {}
}
