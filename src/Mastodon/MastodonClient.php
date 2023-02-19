<?php

declare(strict_types=1);

namespace Crell\Mastobot\Mastodon;

use Crell\Mastobot\Mastodon\Model\Media;
use Crell\Mastobot\Mastodon\Model\MediaAttachment;
use Crell\Mastobot\Mastodon\Model\Status;
use Crell\Serde\Serde;
use function Crell\fp\prop;

/**
 * An RPC-style client for interacting with Mastodon using defined types.
 */
class MastodonClient
{

    public function __construct(
        private readonly MastodonAPI $api,
        private readonly Serde $serde,
    ) {}

    /**
     * @todo Decode the result and return it.
     */
    public function postStatus(Status $status): void
    {
        if ($status->media) {
            $attachments = array_map($this->postMedia(...), $status->media);
            $mediaIds = array_map(prop('id'), $attachments);
            $status->mediaIds = $mediaIds;
        }

        $params = $this->serde->serialize($status, 'array');
        $reply = $this->api->post('/statuses', $params);
        // @todo add decoding of the response.
    }

    public function postMedia(Media $media): MediaAttachment
    {
        $params = [];
        if ($media->description) {
            $params['description'] = $media->description;
        }
        if ($media->focus) {
            $params['focus'] = $media->focus->asString();
        }

        $result = $this->api->postImage('/media', file: $media->file, thumbnail: $media->thumbnail, params: $params);

        /** @var MediaAttachment $attachment */
        $attachment = $this->serde->deserialize($result, from: 'array', to: MediaAttachment::class);
        return $attachment;
    }
}
