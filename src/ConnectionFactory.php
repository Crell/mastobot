<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Colorfield\Mastodon\MastodonAPI;
use Colorfield\Mastodon\MastodonOAuth;

class ConnectionFactory
{
    /** @var array<string, MastodonAPI> */
    private array $connections = [];

    public function __construct(
        private readonly Config $config,
    ) {}

    public function getConnection(string $name): MastodonAPI
    {
        return $this->connections[$name] ?? $this->makeConnection($name);
    }

    protected function makeConnection(string $name): MastodonAPI
    {
        $appName = $this->config->appName;
        $def = $this->config->accounts[$name] ?? throw MissingAccountDefinition::create($name);

        $oAuth = $this->createOAuth($appName, $def);

        return new MastodonAPI($oAuth->config);
    }

    private function createOAuth(string $appName, AccountDef $config): MastodonOAuth
    {
        $oAuth = new MastodonOAuth($appName, $config->appInstance);
        $oAuth->config->setClientId($config->clientId);
        $oAuth->config->setClientSecret($config->clientSecret);
        $oAuth->config->setBearer($config->bearerToken);
        return $oAuth;
    }
}
