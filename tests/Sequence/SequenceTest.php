<?php

declare(strict_types=1);

namespace Crell\Mastobot\Sequence;

use Crell\Mastobot\Clock\FrozenClock;
use Crell\Mastobot\FakeFilesystem;
use Crell\Mastobot\Mastodon\Model\Status;
use Crell\Mastobot\MockStatusRepo;
use Crell\Mastobot\PosterDef;
use Crell\Mastobot\State;
use Crell\Mastobot\Status\StatusRepoFactory;
use Crell\Mastobot\Status\StatusRepository;
use PHPUnit\Framework\TestCase;

class SequenceTest extends TestCase
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
    public function sequence(
        ?\DateTimeImmutable $nextPostTime,
        ?string $lastStatus,
        string $expectedStatus,
        string $expectedLastStatus,
        bool $expectEmpty = false,
    ): void {
        $now = new \DateTimeImmutable('2022-12-25 12:00', new \DateTimeZone('UTC'));
        $nowClock = new FrozenClock($now);

        $def = new SequenceDef(directory: $this->dataDir->url(), account: 'crell', minHours: 1, maxHours: 5);

        $mockRepo = new MockStatusRepo([
            'b.txt' => new Status('B'),
            'a.txt' => new Status('A'),
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
        $sequenceState = new SequenceState(nextPostTime: $nextPostTime, lastStatus: $lastStatus);
        $state->posters[$stateName] = $sequenceState;

        $s = new Sequence($nowClock, $repoFactory);

        $result = $s->getStatuses($stateName, $def, $state);

        if ($expectEmpty) {
            self::assertEmpty($result);
        } else {
            self::assertCount(1, $result);
            self::assertInstanceOf(Status::class, $result[0]);
            self::assertEquals($expectedStatus, $result[0]->status);
            self::assertEquals($expectedLastStatus, $sequenceState->lastStatus);

            $newNext = $sequenceState->nextPostTime;
            $seconds = $newNext->getTimestamp() - $now->getTimestamp();
            // assertGreaterThan et al are backwards, IMO.
            self::assertGreaterThanOrEqual($def->minSeconds(), $seconds);
            self::assertLessThanOrEqual($def->maxSeconds(), $seconds);
        }
    }

    public function exampleConfigurations(): iterable
    {
        yield 'empty state returns first status' => [
            'nextPostTime' => null,
            'lastStatus' => null,
            'expectedStatus' => 'A',
            'expectedLastStatus' => 'a.txt',
        ];
        yield 'with a last post, returns the next status' => [
            'nextPostTime' => null,
            'lastStatus' => 'a.txt',
            'expectedStatus' => 'B',
            'expectedLastStatus' => 'b.txt',
        ];
        yield 'if the last post was the last post, do nothing' => [
            'nextPostTime' => null,
            'lastStatus' => 'c.txt',
            'expectedStatus' => '',
            'expectedLastStatus' => '',
            'expectEmpty' => true,
        ];

        yield 'if we have reached the next post time, return the next status' => [
            // Next post is an hour in the past.
            'nextPostTime' =>  new \DateTimeImmutable('2022-12-25 11:00', new \DateTimeZone('UTC')),
            'lastStatus' => 'a.txt',
            'expectedStatus' => 'B',
            'expectedLastStatus' => 'b.txt',
        ];
        yield 'if we are exactly at the next post time, return the next status' => [
            // Next post is an hour in the past.
            'nextPostTime' =>  new \DateTimeImmutable('2022-12-25 12:00', new \DateTimeZone('UTC')),
            'lastStatus' => 'a.txt',
            'expectedStatus' => 'B',
            'expectedLastStatus' => 'b.txt',
        ];
        yield 'if we have not reached the next post time, do nothing' => [
            // Next post is a day in the future.
            'nextPostTime' =>  new \DateTimeImmutable('2022-12-26 12:00', new \DateTimeZone('UTC')),
            'lastStatus' => 'a.txt',
            'expectedStatus' => '',
            'expectedLastStatus' => '',
            'expectEmpty' => true,
        ];
    }
}
