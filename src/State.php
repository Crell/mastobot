<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Serde\Attributes\DictionaryField;
use Crell\Serde\Attributes\Field;
use Crell\Serde\KeyType;
use Crell\Serde\Serde;

class State
{
    /**
     * @var array<string, PosterState>
     *     Array of directory names to the poster state for that directory.
     */
    #[DictionaryField(arrayType: PosterState::class, keyType: KeyType::String)]
    public array $posters = [];

}
