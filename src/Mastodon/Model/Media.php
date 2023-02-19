<?php

declare(strict_types=1);

namespace Crell\Mastobot\Mastodon\Model;

/**
 * @codeCoverageIgnore
 */
class Media
{
    public function __construct(
        public \SplFileInfo $file,
        public ?\SplFileInfo $thumbnail = null,
        public ?string $description = null,
        public ?Point $focus = null,
    ) {}
}
