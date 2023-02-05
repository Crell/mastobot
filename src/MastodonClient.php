<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Mastobot\Status\Status;
use Crell\Serde\Serde;

/**
 * An RPC-style client for interacting with Mastodon using defined types.
 */
class MastodonClient
{

    public function __construct(
        private readonly MastodonAPI $api,
        private readonly Serde $serde,
    ) {}

    public function postStatus(Status $status)
    {
        $params = $this->serde->serialize($status, 'array');
        $reply = $this->api->post('/statuses', $params);
        // @todo add decoding of the response.
    }
}
