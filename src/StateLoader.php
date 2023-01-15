<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Serde\Serde;

class StateLoader
{
    public function __construct(
        private readonly string $stateFile,
        private readonly Serde $serde,
    ) {}

    public function load(): State
    {
        if (file_exists($this->stateFile)) {
            $state = file_get_contents($this->stateFile);
            /** @var State $state */
            $state = $this->serde->deserialize($state, from: 'json', to: State::class);
        } else {
            $state = new State();
        }

        return $state;
    }

    public function save(State $state): void
    {
        $serialized = $this->serde->serialize($state, format: 'json');
        file_put_contents($this->stateFile, $serialized);
    }
}
