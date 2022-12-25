<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Colorfield\Mastodon\MastodonAPI;
use Colorfield\Mastodon\MastodonOAuth;
use Crell\Serde\Serde;
use Crell\Serde\SerdeCommon;
use Pimple\Container;

class MastobotApp extends Container
{
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this[Serde::class] = static fn (Container $c) => new SerdeCommon();

        $this[Config::class] = static function (Container $c) {
            /** @var Serde $serde */
            $serde = $c[Serde::class];

            return $serde->deserialize(file_get_contents('mastobot.json'), from: 'json', to: Config::class);
        };

        $this['app.name'] = static fn(Container $c) => $c['config']['app.name'] ?? throw new \InvalidArgumentException('No app.name specified.');
        $this['app.instance'] = static fn(Container $c) => $c['config']['app.instance'] ?? throw new \InvalidArgumentException('No app.instance specified.');
        $this['client_id'] = static fn(Container $c) => $c['config']['client_id'] ?? throw new \InvalidArgumentException('No client_id specified.');
        $this['client_secret'] = static fn(Container $c) => $c['config']['client_secret'] ?? throw new \InvalidArgumentException('No client_secret specified.');
        $this['token'] = static fn(Container $c) => $c['config']['token'] ?? throw new \InvalidArgumentException('No token specified.');

        $this[MastodonOAuth::class] = static function (Container $c) {
            /** @var Config $config */
            $config = $c[Config::class];
            $oAuth = new MastodonOAuth($config->appName, $config->appInstance);
            $oAuth->config->setClientId($config->clientId);
            $oAuth->config->setClientSecret($config->clientSecret);
            $oAuth->config->setBearer($config->bearerToken);
            return $oAuth;
        };

        $this[MastodonAPI::class] = static function (Container $c) {
            return new MastodonAPI($c[MastodonOAuth::class]->config);
        };
    }
}