<?php

declare(strict_types=1);

namespace Crell\Mastobot\Status;

use Crell\Mastobot\Config;
use Crell\Mastobot\PosterDef;
use Crell\Serde\Serde;

class StatusRepoFactory
{
    public function __construct(
        protected readonly Serde $serde,
        protected readonly Config $config,
    ) {}

    public function getRepository(PosterDef $def): StatusRepository
    {
        return new StatusRepository($this->serde, $def->directory(), $this->config->defaults);
    }
}
