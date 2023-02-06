<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Mastobot\Mastodon\Model\Status;

interface PostStrategy
{
    /**
     * Gets whatever status objects should be posted right now.
     *
     * @param string $defName
     *   The name of the poster definition. Used to find the state.
     * @param PosterDef $def
     *   The strategy definition for this strategy.
     * @param State $state
     *   Warning: This is an in/out variable.  It will get modified!
     * @return Status[]
     */
    public function getStatuses(string $defName, PosterDef $def, State $state): iterable;
}
