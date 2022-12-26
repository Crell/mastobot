<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Serde\Attributes\DictionaryField;
use Crell\Serde\Attributes\Field;
use Crell\Serde\Serde;

class State
{
    #[DictionaryField]
    public array $randomizerTimestamps = [];

    #[Field(exclude: true)]
    private string $filename = '';

    #[Field(exclude: true)]
    private Serde $serde;

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

    public function __destruct()
    {
        $serialized = $this->serde->serialize($this, format: 'json');
        file_put_contents($this->filename, $serialized);
    }

}