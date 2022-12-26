<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Colorfield\Mastodon\ConfigurationVO;
use Colorfield\Mastodon\MastodonAPI;
use Crell\Serde\SerdeCommon;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

class RunnerTest extends TestCase
{
    use ConfigMaker;

    /** @test */
    public function stuff(): void
    {
        $api = $this->mockMastodonAPI();

        $def = new BatchRandomizerDef(directory: 'data', minHours: 1, maxHours: 5);
        $config = $this->makeConfig(
            batchRandomizers: [$def],
        );

       $randomizer = $this->mockRandomizer(3, true);

        $clock = new FrozenClock(new \DateTimeImmutable('2022-12-25 12:00', new \DateTimeZone('UTC')));

        $r = new Runner($api, $config, $randomizer, $clock, new SerdeCommon());

        $r->run(new State());

        self::assertEquals(3, $api->postCount);
    }

    protected function mockMastodonAPI(): MastodonAPI
    {
        return new class extends MastodonAPI {
            public int $postCount = 0;

            public function __construct() {}

            public function post($endpoint, array $params = [])
            {
                $this->postCount++;
            }
        };
    }

    protected function mockRandomizer(int $numToots, bool $prevCompleted): BatchRandomizer
    {
        return new class($numToots, $prevCompleted) extends BatchRandomizer {
            public function __construct(public int $numToots, public bool $prevCompleted) {}

            public function previousBatchCompleted(BatchRandomizerDef $def, State $state): bool
            {
                return $this->prevCompleted;
            }

            public function makeToots(BatchRandomizerDef $def): \Generator
            {
                static $callCount = 1;
                for ($i = 0; $i < $this->numToots; ++$i) {
                    yield new Toot("Call " . $callCount++);
                }

            }
        };
    }
}