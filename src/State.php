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

    #[Field(exclude: true)]
    private StateLoader $loader;

    public function setLoader(StateLoader $loader): void
    {
        $this->loader = $loader;
    }

    public function __destruct()
    {
        // In tests we generally don't set the filename, so it doesn't try writing back.
        if (isset($this->loader)) {
            $this->loader->save($this);
        }
    }

}
