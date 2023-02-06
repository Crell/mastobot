<?php

declare(strict_types=1);

namespace Crell\Mastobot\SingleRandomizer;

use Crell\Mastobot\Clock\FrozenClock;
use Crell\Mastobot\FakeFilesystem;
use Crell\Mastobot\Mastodon\Model\Status;
use Crell\Mastobot\MockStatusRepo;
use Crell\Mastobot\PosterDef;
use Crell\Mastobot\State;
use Crell\Mastobot\Status\StatusRepoFactory;
use Crell\Mastobot\Status\StatusRepository;
use PHPUnit\Framework\TestCase;

class SingleRandomizerTest extends TestCase
{
    use FakeFilesystem;

    public function setUp(): void
    {
        parent::setUp();
        $this->setupFilesystem();
    }

    /**
     * @test
     * @dataProvider exampleConfigurations()
     */
    public function singleRandom(
        ?\DateTimeImmutable $nextPostTime,
        bool $expectEmpty = false,
    ): void {
        $now = new \DateTimeImmutable('2022-12-25 12:00', new \DateTimeZone('UTC'));
        $nowClock = new FrozenClock($now);

        $def = new SingleRandomizerDef(directory: $this->dataDir->url(), account: 'crell', minHours: 1, maxHours: 5);

        $mockRepo = new MockStatusRepo([
            'a.txt' => new Status('A'),
            'b.txt' => new Status('B'),
            'c.txt' => new Status('C'),
        ]);

        $repoFactory = new class($mockRepo) extends StatusRepoFactory {
            public function __construct(public StatusRepository $repo) {}

            public function getRepository(PosterDef $def): StatusRepository
            {
                return $this->repo;
            }
        };

        $state = new State();
        $stateName = 'name';
        $randomState = new SingleRandomizerState(nextPostTime: $nextPostTime);
        $state->posters[$stateName] = $randomState;

        $s = new SingleRandomizer($nowClock, $repoFactory);

        $result = $s->getStatuses($stateName, $def, $state);

        if ($expectEmpty) {
            self::assertCount(0, $result);
            self::assertEquals($nextPostTime, $randomState->nextPostTime);
        } else {
            self::assertCount(1, $result);
            self::assertInstanceOf(Status::class, $result[0]);
            $newNext = $randomState->nextPostTime;
            $seconds = $newNext->getTimestamp() - $now->getTimestamp();
            // assertGreaterThan et al are backwards, IMO.
            self::assertGreaterThanOrEqual($def->minSeconds(), $seconds);
            self::assertLessThanOrEqual($def->maxSeconds(), $seconds);
        }
    }

    public function exampleConfigurations(): iterable
    {
        yield 'if we have reached the next post time, return the next status' => [
            // Next post is an hour in the past.
            'nextPostTime' =>  new \DateTimeImmutable('2022-12-25 11:00', new \DateTimeZone('UTC')),
        ];
        yield 'if we have not reached the next post time, do nothing' => [
            // Next post is a day in the future.
            'nextPostTime' =>  new \DateTimeImmutable('2022-12-26 12:00', new \DateTimeZone('UTC')),
            'expectEmpty' => true,
        ];
        yield 'if we are exactly at the next post time, return the next status' => [
            // Next post is a day in the future.
            'nextPostTime' =>  new \DateTimeImmutable('2022-12-25 12:00', new \DateTimeZone('UTC')),
        ];
    }
}
