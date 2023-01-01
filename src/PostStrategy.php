<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Mastobot\Status\Status;

interface PostStrategy
{
    /**
     * Gets whatever status objects should be posted right now.
     *
     * @param PosterDef $def
     *   The strategy definition for this strategy.
     * @param State $state
     *   Warning: This is an in/out variable.  It will get modified!
     * @return Status[]
     */
    public function getStatuses(PosterDef $def, State $state): iterable;
}
