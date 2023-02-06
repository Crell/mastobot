<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Mastobot\Mastodon\Model\Status;
use Crell\Mastobot\Status\StatusRepository;

class MockStatusRepo extends StatusRepository
{
    /**
     * @param Status[] $statuses
     */
    public function __construct(public array $statuses = []){}

    public function getRandom(): Status
    {
        return $this->statuses[array_rand($this->statuses)];
    }

    public function load(string $name): ?Status
    {
        return $this->statuses[$name] ?? null;
    }

    public function nameList(): array
    {
        $names = array_keys($this->statuses);
        sort($names);
        return $names;
    }
}
