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
     * @var array<string, int>
     *     Path of the directory to the unix timestamp of when the last batch will complete.
     */
    #[DictionaryField(arrayType: 'int', keyType: KeyType::String)]
    public array $randomizerTimestamps = [];

    #[Field(exclude: true)]
    private StateLoader $loader;

    /**
     *
     * @internal
     *
     * @param string $filename
     */
    public function setSaveFile(string $filename, Serde $serde): void
    {
        $this->filename = $filename;
        $this->serde = $serde;
    }

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
