<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Serde\Serde;
use Pimple\Container;

class Runner
{
    public function __construct(
        private readonly Container $app,
        private readonly ConnectionFactory $connFactory,
        private readonly Config $config,
        private readonly Serde $serde,
    ) {}

    public function run(State $state): void
    {
        foreach ($this->config->posters as $defName => $posterDef) {
            $conn = $this->connFactory->getConnection($posterDef->account());
            /** @var PostStrategy $poster */
            $poster = $this->app[$posterDef->poster()];
            foreach ($poster->getStatuses($defName, $posterDef, $state) as $status) {
                $params = $this->serde->serialize($status, 'array');
                $reply = $conn->post('/statuses', $params);
            }
        }
    }
}
