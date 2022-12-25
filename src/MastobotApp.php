<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Colorfield\Mastodon\MastodonAPI;
use Colorfield\Mastodon\MastodonOAuth;
use Pimple\Container;

class MastobotApp extends Container
{
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this['config'] = static function (Container $c) {
            return json_decode(file_get_contents('mastobot.json'), true, 512, JSON_THROW_ON_ERROR);
        };

        $this['app.name'] = static fn(Container $c) => $c['config']['app.name'] ?? throw new \InvalidArgumentException('No app.name specified.');
        $this['app.instance'] = static fn(Container $c) => $c['config']['app.instance'] ?? throw new \InvalidArgumentException('No app.instance specified.');
        $this['client_id'] = static fn(Container $c) => $c['config']['client_id'] ?? throw new \InvalidArgumentException('No client_id specified.');
        $this['client_secret'] = static fn(Container $c) => $c['config']['client_secret'] ?? throw new \InvalidArgumentException('No client_secret specified.');
        $this['token'] = static fn(Container $c) => $c['config']['token'] ?? throw new \InvalidArgumentException('No token specified.');

        $this[MastodonOAuth::class] = static function (Container $c) {
            $oAuth = new MastodonOAuth($c['app.name'], $c['app.instance']);
            $oAuth->config->setClientId($c['client_id']);
            $oAuth->config->setClientSecret($c['client_secret']);
            $oAuth->config->setBearer($c['token']);
            return $oAuth;
        };

        $this[MastodonAPI::class] = static function (Container $c) {
            return new MastodonAPI($c[MastodonOAuth::class]->config);
        };
    }
}