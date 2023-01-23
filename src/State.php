<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Serde\Attributes\DictionaryField;
use Crell\Serde\KeyType;

class State
{
    /**
     * @var array<string, PosterState>
     *     Array of poster names to the poster state for that poster.
     */
    #[DictionaryField(arrayType: PosterState::class, keyType: KeyType::String)]
    public array $posters = [];

}
