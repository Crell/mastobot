<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Colorfield\Mastodon\MastodonAPI;
use Psr\Clock\ClockInterface;

class Runner
{
    public function __construct(
        private readonly MastodonAPI $api,
        private readonly Config $config,
        private readonly Randomizer $randomizer,
        private readonly ClockInterface $clock,
    ) {}

    public function run(State $state): void
    {
        $this->enqueueRandomizedBatches($state);
    }

    protected function enqueueRandomizedBatches(State $state): void
    {
        foreach ($this->config->randomizers as $def) {
            if ($this->randomizer->previousBatchCompleted($def, $state)) {
                foreach ($this->randomizer->makeToots($def, $state) as $toot) {
                    //var_dump($toot);
                    $params = $toot->asParams();
                    //var_dump($params);
                    //$reply = $this->api->post('/statuses', $params);
                    //var_dump($reply);
                }
            }
            $state->randomizerTimestamps[$def->directory] = $this->clock->now()->format('U');
        }
    }
}