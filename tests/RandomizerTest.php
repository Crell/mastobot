<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class RandomizerTest extends TestCase
{
    use ConfigMaker;

    private vfsStreamDirectory $root;

    public function setUp(): void
    {
        parent::setUp();
        $this->root = vfsStream::setup();

        //$this->configFile = vfsStream::newFile(Config::ConfigFileName);

        $dataDir = vfsStream::newDirectory('data');
        $dataDir->at($this->root);
        $tweetA = vfsStream::newDirectory('a');
        $tweetA->at($this->root);
        $tweetB = vfsStream::newDirectory('b');
        $tweetB->at($this->root);
        $tweetC = vfsStream::newDirectory('c');
        $tweetC->at($this->root);

        $status = vfsStream::newFile('status.txt')->withContent('Testing A');
        $status->at($tweetA);
        $status = vfsStream::newFile('status.txt')->withContent('Testing B');
        $status->at($tweetB);
        $status = vfsStream::newFile('status.txt')->withContent('Testing C');
        $status->at($tweetC);
    }

    /** @test */
    public function randomizer_timestamp_in_past_is_complete(): void
    {
        $now = new FrozenClock(new \DateTimeImmutable('2022-12-25 12:00', new \DateTimeZone('UTC')));
        $finished = (new \DateTimeImmutable('2020-01-01'))->format('U');

        $c = $this->makeConfig(
            randomizers: [new RandomizerDef(directory: 'data', minHours: 1, maxHours: 5)],
        );

        $r = new Randomizer($c, $now);

        $s = new State();
        $s->randomizerTimestamps['data'] = $finished;

        self::assertTrue($r->previousBatchCompleted($c->randomizers[0], $s));
    }

    /** @test */
    public function randomizer_timestamp_in_future_is_not_complete(): void
    {
        $now = new FrozenClock(new \DateTimeImmutable('2022-12-25 12:00', new \DateTimeZone('UTC')));
        $finished = (new \DateTimeImmutable('2022-12-25 12:05'))->format('U');

        $c = $this->makeConfig(
            randomizers: [new RandomizerDef(directory: 'data', minHours: 1, maxHours: 5)],
        );

        $r = new Randomizer($c, $now);

        $s = new State();
        $s->randomizerTimestamps['data'] = $finished;

        self::assertFalse($r->previousBatchCompleted($c->randomizers[0], $s));
    }

    /** @test */
    public function validate_randomizer(): void
    {
        $def = new RandomizerDef(directory: 'data', minHours: 1, maxHours: 5);

        $now = new FrozenClock(new \DateTimeImmutable('2022-12-25 12:00', new \DateTimeZone('UTC')));
        $c = $this->makeConfig(
            randomizers: [$def],
        );

        $r = new Randomizer($c, $now);

        $s = new State();

        /** @var Toot[] $toots */
        $toots = iterator_to_array($r->makeToots($def));

        self::assertIsArray($toots);
        self::assertCount(3, $toots);

        self::assertSame(Visibility::Unlisted, $toots[0]->visibility);
        self::assertSame(Visibility::Unlisted, $toots[1]->visibility);
        self::assertSame(Visibility::Unlisted, $toots[2]->visibility);

        // Pair each message with the one right after it.
        $pairs = array_map(null, $toots, [null, ...$toots]);
        $pairs = array_slice($pairs, 1, count($pairs) - 2);

        // Now assert that each pair is within the proper time range from each other.
        foreach ($pairs as $pair) {
            $seconds = $this->asSeconds($pair[1]->scheduledAt->diff($pair[0]->scheduledAt));
            // assertGreaterThan et al are backwards, IMO.
            self::assertGreaterThanOrEqual($def->minSeconds(), $seconds);
            self::assertLessThanOrEqual($def->maxSeconds(), $seconds);
        }
    }

    /**
     * Converts a DateInterval into seconds, for easier math.
     *
     * Onl works up to days; anything beyond that is Hard(tm), so ignored.
     *
     * Note the result may be negative.
     */
    protected function asSeconds(\DateInterval $interval): int
    {
        return ($interval->s
            + $interval->i * 60
            + $interval->h * 60 * 60
            + $interval->d * 60 * 60 * 24)
            * ($interval->invert ? -1 : 1 );
    }


}