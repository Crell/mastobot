<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Serde\Serde;

class ConfigLoader
{
    public function __construct(
        private readonly Serde $serde,
        private readonly string $configPath,
    ) {}

    public function load(): Config
    {
        /** @var Config $config */
        $config = $this->serde->deserialize(
            \file_get_contents($this->configPath . Config::ConfigFileName),
            from: 'yaml',
            to: Config::class,
        );
        return $config;
    }
}
