<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Psr\Clock\ClockInterface;

class Randomizer
{
    public function __construct(
        protected readonly Config $config,
        protected readonly ClockInterface $clock,
    ) {}

    public function previousBatchCompleted(RandomizerDef $def, State $state): bool
    {
        // The timestamp at which the previous batch should be done.
        // This is a unix timestamp.
        $previousBatchEnd = $state->randomizerTimestamps[$def->directory] ?? 0;

        $now = $this->clock->now()->getTimestamp();

        // The batch is done if it's past the time that we expected it to be done.
        // This does assume that everything behaves correctly...
        return $now > $previousBatchEnd;
    }

    /**
     * Enqueues all posts in the specified randomizer set.
     *
     * All posts will be randomly ordered, with a random time gap
     * between them specified by the min/max time of the randomizer set.
     * There will also be a gap before the first post is scheduled for.
     *
     * So 5 posts, set to be between 1 and 3 hours apart, could be
     * anywhere from 6 to 16 hours to get through the whole cycle.
     *
     * @param RandomizerDef $def
     * @param State $state
     * @return iterable<Toot>
     */
    public function makeToots(RandomizerDef $def, State $state): iterable
    {
        /** @var \SplFileInfo[] $postDirs */
        $postDirs = iterator_to_array(new \FilesystemIterator($def->directory,\FilesystemIterator::SKIP_DOTS));
        shuffle($postDirs);

        $now = $this->clock->now();
        $postTime = $now->add($this->getRandomizedGap($def));

        foreach ($postDirs as $dir) {
            $toot = $this->loadToot($dir);
            // Error handling is just silent ignore for now.
            if (!$toot) {
                continue;
            }
            $toot->scheduledAt = $postTime;
            $postTime = $postTime->add($this->getRandomizedGap($def));
            yield $toot;
        }
    }

    protected function loadToot(\SplFileInfo $dir): ?Toot
    {
        // @todo Support plain files, not in directories.

        // __toString is black magic.
        $textStatus = "$dir/status.txt";

        if (file_exists($textStatus)) {
            $status = file_get_contents($textStatus);
            return new Toot($status);
        }

        // @todo Make this actually work, using Serde.
        /*
        $jsonStatus = "$dir/status.json";
        if (file_exists($jsonStatus)) {
            $status = file_get_contents($jsonStatus);
            return new Toot($status);
        }
        */

        // @todo Add support for attaching media.

        // If no toot could be loaded from here.
        return null;
    }

    protected function getRandomizedGap(RandomizerDef $def): \DateInterval
    {
        $gapSeconds = \random_int($def->minSeconds(), $def->maxSeconds());

        return new \DateInterval("PT{$gapSeconds}S");
    }
}
