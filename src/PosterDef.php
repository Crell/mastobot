<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Mastobot\Sequence\SequenceDef;
use Crell\Mastobot\SingleRandomizer\SingleRandomizerDef;
use Crell\Serde\Attributes\StaticTypeMap;

#[StaticTypeMap('strategy', [
    'random' => SingleRandomizerDef::class,
    'sequence' => SequenceDef::class,
])]
interface PosterDef
{
    /**
     * Gets the class name of the corresponding poster service.
     *
     * In practice this will map to a service name in the container.
     * Yes, that's clunky, but I'm not really sure how else to do it.
     *
     * @return class-string
     *   The class name of the corresponding poster service.
     */
    public function poster(): string;

    /**
     * The directory this poster should pull data from.
     *
     * @return string
     */
    public function directory(): string;

    /**
     * The account name this poster should use.
     *
     * @return string
     */
    public function account(): string;
}
