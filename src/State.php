<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Serde\Attributes\DictionaryField;

class State
{
    #[DictionaryField]
    public array $randomizerTimestamps = [];
}
