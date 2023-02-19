<?php

declare(strict_types=1);

namespace Crell\Mastobot\Mastodon\Model;

use Crell\Serde\Attributes\Field;

/**
 * @codeCoverageIgnore
 */
class MediaAttachment
{
    public function __construct(
        public string $id,
        public string $type,
        public string $url,
        #[Field(serializedName: 'preview_url')]
        public string $previewUrl,
        #[Field(serializedName: 'remote_url')]
        public ?string $removeUrl,
        #[Field(serializedName: 'text_url')]
        public ?string $textUrl,

        public ?MediaAttachmentMeta $meta = null,
        public ?string $description = null,
        public ?string $blurhash = null,
    ) {}
}
