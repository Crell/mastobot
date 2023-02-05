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
        foreach ($status->media as $file) {
            $id = $this->postMedia($file);
            $status->mediaIds[] = $id;
        }

        $params = $this->serde->serialize($status, 'array');
        $reply = $this->api->post('/statuses', $params);
        // @todo add decoding of the response.
    }

    public function postMedia(\SplFileInfo $file)
    {
        $bearer = $this->api->config->getBearer();
        $file->getFilename();

        // Temporary hack at best.
        $cmd = "curl -H \"Authorization: Bearer {$bearer}\" " .
            '-X POST -H "Content-Type: multipart/form-data" https://phpc.social/api/v1/media ' .
            "--form file=@{$file}";

        $result = `$cmd`;
        $media1 = json_decode( $result );
    }
}
