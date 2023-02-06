<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Pimple\Container;

class Runner
{
    public function __construct(
        private readonly Container $app,
        private readonly ConnectionFactory $connFactory,
        private readonly Config $config,
    ) {}

    public function run(State $state): void
    {
        foreach ($this->config->posters as $defName => $posterDef) {
            $conn = $this->connFactory->getConnection($posterDef->account());
            /** @var PostStrategy $poster */
            $poster = $this->app[$posterDef->poster()];
            foreach ($poster->getStatuses($defName, $posterDef, $state) as $status) {
                $conn->postStatus($status);
            }
        }
    }
}
