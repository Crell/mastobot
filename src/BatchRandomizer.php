<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Serde\Serde;
use Psr\Clock\ClockInterface;

class BatchRandomizer
{
    public function __construct(
        protected readonly Config $config,
        protected readonly ClockInterface $clock,
        protected readonly Serde $serde,
    ) {}

    public function previousBatchCompleted(BatchRandomizerDef $def, State $state): bool
    {
        // The timestamp at which the previous batch should be done.
        // This is a unix timestamp.
        $previousBatchEnd = $state->batchRandomizerTimestamps[$def->directory] ?? 0;

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
     * @param BatchRandomizerDef $def
     * @return iterable<Toot>
     */
    public function makeToots(BatchRandomizerDef $def): \Generator
    {
        /** @var \SplFileInfo[] $postList */
        $postList = iterator_to_array(new \FilesystemIterator($def->directory,\FilesystemIterator::SKIP_DOTS));
        shuffle($postList);

        $now = $this->clock->now();
        $postTime = $now->add($this->getRandomizedGap($def));

        foreach ($postList as $record) {
            $toot = $this->loadToot($record);
            // Error handling is just silent ignore for now.
            if (!$toot) {
                continue;
            }
            $toot->scheduledAt = $postTime;
            $postTime = $postTime->add($this->getRandomizedGap($def));
            yield $record->getFilename() => $toot;
        }
    }

    protected function loadToot(\SplFileInfo $record): ?Toot
    {
        // Allow just plain text files as tweets, with no directory.
        if (!$record->isDir() && $record->getExtension() === 'txt') {
            $status = file_get_contents((string)$record);
            return new Toot($status, visibility: $this->config->defaultVisibility);
        }

        // Allow just JSON files as tweets, with no directory.
        if (!$record->isDir() && $record->getExtension() === 'json') {
            $status = file_get_contents((string)$record);
            /** @var Toot $toot */
            $toot = $this->serde->deserialize($status, from: 'json', to: Toot::class);
            $toot->visibility ??= $this->config->defaultVisibility;
            return $toot;
        }

        // Directory support is mostly for later, once we want to allow
        // for attached media.  If you're not doing that, you probably don't
        // need to bother with directories.

        // Allow a directory with either JSON or text.
        if ($record->isDir()) {
            $textStatus = "$record/status.txt";
            if (file_exists($textStatus)) {
                $status = file_get_contents($textStatus);
                return new Toot($status, visibility: $this->config->defaultVisibility);
            }

            $jsonStatus = "$record/status.json";
            if (file_exists($jsonStatus)) {
                $status = file_get_contents($jsonStatus);
                /** @var Toot $toot */
                $toot = $this->serde->deserialize($status, from: 'json', to: Toot::class);
                $toot->visibility ??= $this->config->defaultVisibility;
                return $toot;
            }

            // @todo Add support for attaching media.
        }

        // If no toot could be loaded from here.
        return null;
    }

    protected function getRandomizedGap(BatchRandomizerDef $def): \DateInterval
    {
        $gapSeconds = \random_int($def->minSeconds(), $def->maxSeconds());

        return new \DateInterval("PT{$gapSeconds}S");
    }
}
