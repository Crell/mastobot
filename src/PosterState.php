<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Mastobot\Sequence\SequenceState;
use Crell\Mastobot\SingleRandomizer\SingleRandomizerState;
use Crell\Serde\Attributes\StaticTypeMap;

#[StaticTypeMap('strategy', [
    'random' => SingleRandomizerState::class,
    'sequence' => SequenceState::class,
])]
interface PosterState
{

}
