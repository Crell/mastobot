<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Mastobot\Clock\FrozenClock;
use Crell\Mastobot\Mastodon\Model\Status;
use Crell\Mastobot\Sequence\Sequence;
use Crell\Mastobot\Sequence\SequenceDef;
use Crell\Mastobot\Status\StatusRepoFactory;
use Crell\Mastobot\Status\StatusRepository;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

class RunnerTest extends TestCase
{
    use ConfigMaker;

    /** @test */
    public function stuff(): void
    {
        $def = new SequenceDef(directory: 'data', account: 'crell', minHours: 1, maxHours: 5);
        $config = $this->makeConfig(
            posters: ['name' => $def],
        );

        $clock = new FrozenClock(new \DateTimeImmutable('2022-12-25 12:00', new \DateTimeZone('UTC')));

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

        $container = new Container();
        $container[Sequence::class] = new Sequence($clock, $repoFactory);

        $r = new Runner($container, new MockConnectionFactory(), $config);

        $r->run(new State());

        self::assertEquals(1, MockConnectionFactory::$postCount);
    }


}
