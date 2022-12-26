<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Serde\Serde;

class StateLoader
{
    public function __construct(
        private readonly Config $config,
        private readonly Serde $serde,
    ) {}

    public function load(): State
    {
        if (file_exists($this->config->stateFile)) {
            $state = file_get_contents($this->config->stateFile);
            /** @var State $state */
            $state = $this->serde->deserialize($state, from: 'json', to: State::class);
        } else {
            $state = new State();
        }

        $state->setSaveFile($this->config->stateFile, $this->serde);

        return $state;
    }
}
