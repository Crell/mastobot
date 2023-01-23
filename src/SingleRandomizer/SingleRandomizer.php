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
    public function getStatuses(string $defName, PosterDef $def, State $state): array
    {
        /** @var SingleRandomizerState $posterState */
        $posterState = $state->posters[$defName] ?? new SingleRandomizerState();

        $now = $this->clock->now();
        $nextPostTime = $posterState->nextPostTime ?? null;

        // Bail early if it's not time to post anything yet.
        if ($nextPostTime && $now < $nextPostTime) {
            return [];
        }

        $repo = $this->repoFactory->getRepository($def);
        $status = $repo->getRandom();

        $posterState->nextPostTime = $now->add($def->getRandomizedGap());
        $state->posters[$defName] = $posterState;

        return [$status];
    }
}
