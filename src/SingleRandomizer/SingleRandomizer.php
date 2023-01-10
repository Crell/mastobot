<?php

declare(strict_types=1);

namespace Crell\Mastobot\SingleRandomizer;

use Crell\Mastobot\PosterDef;
use Crell\Mastobot\PostStrategy;
use Crell\Mastobot\State;
use Crell\Mastobot\Status\StatusRepoFactory;
use Psr\Clock\ClockInterface;

class SingleRandomizer implements PostStrategy
{
    public function __construct(
        protected readonly ClockInterface $clock,
        protected readonly StatusRepoFactory $repoFactory,
    ) {}

    /**
     * @param SingleRandomizerDef $def
     */
    public function getStatuses(PosterDef $def, State $state): array
    {
        /** @var SingleRandomizerState $posterState */
        $posterState = $state->posters[$def->directory()] ?? new SingleRandomizerState();

        $now = $this->clock->now();
        $nextPostTime = $posterState->nextPostTime ?? $now;

        if ($now <= $nextPostTime) {
            return [];
        }

        $repo = $this->repoFactory->getRepository($def);
        $status = $repo->getRandom();

        $posterState->nextPostTime = $now->add($def->getRandomizedGap());
        $state->posters[$def->directory()] = $posterState;

        return [$status];
    }
}
