<?php

declare(strict_types=1);

namespace Crell\Mastobot\Mastodon\Model;

class Media
{
    public function __construct(
        public \SplFileInfo $file,
        public ?\SplFileInfo $thumbnail = null,
        public ?string $description = null,
        public ?Point $focus = null,
    ) {}
}
