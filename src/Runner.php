<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Colorfield\Mastodon\MastodonAPI;
use Crell\Serde\Serde;
use Psr\Clock\ClockInterface;

class Runner
{
    public function __construct(
        private readonly MastodonAPI $api,
        private readonly Config $config,
        private readonly BatchRandomizer $randomizer,
        private readonly ClockInterface $clock,
        private readonly Serde $serde,
    ) {}

    public function run(State $state): void
    {
        $this->enqueueRandomizedBatches($state);
    }

    protected function enqueueRandomizedBatches(State $state): void
    {
        foreach ($this->config->batchRandomizers as $def) {
            if ($this->randomizer->previousBatchCompleted($def, $state)) {
                foreach ($this->randomizer->makeToots($def) as $toot) {
                    $params = $this->serde->serialize($toot, 'array');
                    $reply = $this->api->post('/statuses', $params);
                }
            }
            $state->batchRandomizerTimestamps[$def->directory] = $this->clock->now()->format('U');
        }
    }
}
