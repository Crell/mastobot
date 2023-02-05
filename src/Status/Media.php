<?php

declare(strict_types=1);

namespace Crell\Mastobot\Status;

class Media
{
    // @todo Figure out attaching thumbnails.
    public function __construct(
        public \SplFileInfo $file,
        public ?\SplFileInfo $thumbnail = null,
        public ?string $description = null,
        public ?Point $focus = null,
    ) {}
}
