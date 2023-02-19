<?php

declare(strict_types=1);

namespace Crell\Mastobot;


use Colorfield\Mastodon\MastodonOAuth;
use Crell\Mastobot\Mastodon\MastodonAPI;
use Crell\Mastobot\Mastodon\MastodonClient;
use Crell\Serde\Serde;

class ConnectionFactory
{
    /** @var array<string, MastodonAPI> */
    private array $connections = [];

    public function __construct(
        private readonly Config $config,
        private readonly Serde $serde,
    ) {}

    public function getConnection(string $name): MastodonClient
    {
        return $this->connections[$name] ?? $this->makeConnection($name);
    }

    protected function makeConnection(string $name): MastodonClient
    {
        $appName = $this->config->appName;
        $def = $this->config->accounts[$name] ?? throw MissingAccountDefinition::create($name);

        $oAuth = $this->createOAuth($appName, $def);

        return new MastodonClient(new MastodonAPI($oAuth->config), $this->serde);
    }

    /**
     * @codeCoverageIgnore
     */
    private function createOAuth(string $appName, AccountDef $config): MastodonOAuth
    {
        $oAuth = new MastodonOAuth($appName, $config->appInstance);
        $oAuth->config->setClientId($config->clientId);
        $oAuth->config->setClientSecret($config->clientSecret);
        $oAuth->config->setBearer($config->bearerToken);
        return $oAuth;
    }
}
