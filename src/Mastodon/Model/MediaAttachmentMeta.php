<?php

declare(strict_types=1);

namespace Crell\Mastobot\Mastodon\Model;

/**
 * @codeCoverageIgnore
 */
class MediaAttachmentMeta
{
    public function __construct(
        public Point $focus,
        public Dimensions $original,
        public Dimensions $small,
    ) {}
}
