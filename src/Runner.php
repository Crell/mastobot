<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Colorfield\Mastodon\MastodonAPI;
use Crell\Mastobot\Status\Status;
use Crell\Serde\Serde;
use Pimple\Container;

class Runner
{
    public function __construct(
        private readonly Container $app,
        private readonly MastodonAPI $api,
        private readonly Config $config,
        private readonly Serde $serde,
    ) {}

    public function run(State $state): void
    {
        foreach ($this->getStatusesToPost($state) as $status) {
            $params = $this->serde->serialize($status, 'array');
            $reply = $this->api->post('/statuses', $params);
        }
    }

    /**
     * @return iterable<Status>
     */
    protected function getStatusesToPost(State $state): iterable
    {
        foreach ($this->config->posters as $posterDef) {
            /** @var PostStrategy $poster */
            $poster = $this->app[$posterDef->poster()];
            yield from $poster->getStatuses($posterDef, $state);
        }
    }
}
