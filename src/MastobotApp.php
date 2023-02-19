<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Mastobot\Clock\UtcClock;
use Crell\Mastobot\Sequence\Sequence;
use Crell\Mastobot\SingleRandomizer\SingleRandomizer;
use Crell\Mastobot\Status\StatusRepoFactory;
use Crell\Serde\Serde;
use Crell\Serde\SerdeCommon;
use Pimple\Container;
use Psr\Clock\ClockInterface;

/**
 * @codeCoverageIgnore
 */
class MastobotApp extends Container
{
    protected const AppRoot = __DIR__ . '/../';

    /**
     * @param array<string, mixed> $values
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this[Serde::class] = static fn (Container $c) => new SerdeCommon();

        $this[ConfigLoader::class] = static fn (Container $c)
            => new ConfigLoader($c[Serde::class], self::AppRoot);

        $this[Config::class] = static fn (Container $c)
            => $c[ConfigLoader::class]->load();

        $this[ConnectionFactory::class] = static fn (Container $c)
            => new ConnectionFactory($c[Config::class], $c[Serde::class]);

        $this[ClockInterface::class] = static fn(Container $c) => new UtcClock();

        $this[StateLoader::class] = static fn (Container $c)
            => new StateLoader($c[Config::class]->stateFile, $c[Serde::class]);

        $this[StatusRepoFactory::class] = static fn (Container $c)
            => new StatusRepoFactory($c[Serde::class], $c[Config::class]);

        $this[SingleRandomizer::class] = static fn (Container $c)
            => new SingleRandomizer($c[ClockInterface::class], $c[StatusRepoFactory::class]);

        $this[Sequence::class] = static fn (Container $c)
            => new Sequence($c[ClockInterface::class], $c[StatusRepoFactory::class]);

        $this[Runner::class] = static fn (Container $c)
            => new Runner(
                app: $c,
                connFactory: $c[ConnectionFactory::class],
                config: $c[Config::class],
            );
    }
}
