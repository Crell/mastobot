<?php

declare(strict_types=1);

namespace Crell\Mastobot\Sequence;

use Crell\Mastobot\PosterDef;
use Crell\Mastobot\PostStrategy;
use Crell\Mastobot\State;
use Crell\Mastobot\Status\StatusRepoFactory;
use Psr\Clock\ClockInterface;

class Sequence implements PostStrategy
{
    public function __construct(
        protected readonly ClockInterface $clock,
        protected readonly StatusRepoFactory $repoFactory,
    ) {}

    /**
     * @param SequenceDef $def
     */
    public function getStatuses(string $defName, PosterDef $def, State $state): array
    {
        /** @var SequenceState $posterState */
        $posterState = $state->posters[$defName] ?? new SequenceState();

        $now = $this->clock->now();
        $nextPostTime = $posterState->nextPostTime ?? null;

        // Bail early if it's not time to post anything yet.
        if ($nextPostTime && $now < $nextPostTime) {
            return [];
        }

        $repo = $this->repoFactory->getRepository($def);
        $names = $repo->nameList();
        $index = array_search($posterState->lastStatus, $names, true);

        // Default to the first status if there was no last one.
        // If the next status runs off the end of the list, use null.
        $nextStatusName = $index === false
            ? $names[0]
            : ($names[$index + 1] ?? null);

        // If we've already posted everything, just stop.
        if (!$nextStatusName) {
            return [];
        }

        $status = $repo->load($nextStatusName);

        // Update the state now that we've posted (or will in a moment).
        $posterState->nextPostTime = $now->add($def->getRandomizedGap());
        $posterState->lastStatus = $nextStatusName;
        $state->posters[$defName] = $posterState;

        return [$status];
    }
}
