<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamContent;
use bovigo\vfs\vfsStreamDirectory;
use Crell\Serde\SerdeCommon;
use PHPUnit\Framework\TestCase;

class RandomizerTest extends TestCase
{
    use ConfigMaker;

    private vfsStreamDirectory $root;

    private vfsStreamContent $dataDir;

    public function setUp(): void
    {
        parent::setUp();

        $structure = [
            'data' => [
                'a' => [
                    'status.txt'    => 'Testing A',
                ],
                'b' => [
                    'status.txt'    => 'Testing B',
                ],
                'c' => [
                    'status.txt'    => 'Testing C',
                ],
                'd.txt' => 'Testing D',
                'e.json' => '{"status": "Testing E", "visibility": "public"}',
                'f' => [
                    'status.json' => '{"status": "Testing F", "spoiler_text": "spoiler"}',
                ]
            ],
        ];
        $this->root = vfsStream::setup('root', null, $structure);
        $this->dataDir = $this->root->getChild('data');
    }

    /** @test */
    public function randomizer_timestamp_in_past_is_complete(): void
    {
        $now = new FrozenClock(new \DateTimeImmutable('2022-12-25 12:00', new \DateTimeZone('UTC')));
        $finished = (new \DateTimeImmutable('2020-01-01'))->format('U');

        $c = $this->makeConfig(
            randomizers: [new RandomizerDef(directory: $this->dataDir->url(), minHours: 1, maxHours: 5)],
        );

        $r = new Randomizer($c, $now, new SerdeCommon());

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
            randomizers: [new RandomizerDef(directory: $this->dataDir->url(), minHours: 1, maxHours: 5)],
        );

        $r = new Randomizer($c, $now, new SerdeCommon());

        $s = new State();
        $s->randomizerTimestamps[$this->dataDir->url()] = $finished;

        self::assertFalse($r->previousBatchCompleted($c->randomizers[0], $s));
    }

    /** @test */
    public function validate_randomizer(): void
    {
        $def = new RandomizerDef(directory: $this->dataDir->url(), minHours: 1, maxHours: 5);

        $now = new FrozenClock(new \DateTimeImmutable('2022-12-25 12:00', new \DateTimeZone('UTC')));
        $c = $this->makeConfig(
            randomizers: [$def],
        );

        $r = new Randomizer($c, $now, new SerdeCommon());

        /** @var Toot[] $toots */
        $toots = iterator_to_array($r->makeToots($def));

        self::assertIsArray($toots);
        self::assertCount(6, $toots);

        self::assertSame(Visibility::Unlisted, $toots['a']->visibility);
        self::assertSame(Visibility::Unlisted, $toots['b']->visibility);
        self::assertSame(Visibility::Unlisted, $toots['c']->visibility);
        self::assertSame(Visibility::Unlisted, $toots['d.txt']->visibility);
        self::assertSame(Visibility::Public, $toots['e.json']->visibility);
        self::assertSame(Visibility::Unlisted, $toots['f']->visibility);

        self::assertSame('spoiler', $toots['f']->spoilerText);

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