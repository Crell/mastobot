<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Colorfield\Mastodon\MastodonAPI;
use Colorfield\Mastodon\MastodonOAuth;
use Crell\Serde\Serde;
use Crell\Serde\SerdeCommon;
use Pimple\Container;
use Psr\Clock\ClockInterface;

class MastobotApp extends Container
{
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this[Serde::class] = static fn (Container $c) => new SerdeCommon();

        $this[Config::class] = static function (Container $c) {
            /** @var Serde $serde */
            $serde = $c[Serde::class];

            return $serde->deserialize(file_get_contents(Config::ConfigFileName), from: 'json', to: Config::class);
        };

        $this[MastodonOAuth::class] = static function (Container $c) {
            /** @var Config $config */
            $config = $c[Config::class];
            $oAuth = new MastodonOAuth($config->appName, $config->appInstance);
            $oAuth->config->setClientId($config->clientId);
            $oAuth->config->setClientSecret($config->clientSecret);
            $oAuth->config->setBearer($config->bearerToken);
            return $oAuth;
        };

        $this[MastodonAPI::class] = static fn (Container $c)
            => new MastodonAPI($c[MastodonOAuth::class]->config);

        $this[ClockInterface::class] = static fn(Container $c) => new UtcClock();

        $this[Randomizer::class] = static fn (Container $c)
            => new Randomizer($c[Config::class], $c[ClockInterface::class]);

        $this[StateLoader::class] = static fn (Container $c)
            => new StateLoader($c[Config::class]->stateFile, $c[Serde::class]);

        $this[Runner::class] = static fn (Container $c)
            => new Runner($c[MastodonAPI::class], $c[Config::class], $c[Randomizer::class], $c[ClockInterface::class]);
    }
}
