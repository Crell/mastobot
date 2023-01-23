<?php

declare(strict_types=1);

namespace Crell\Mastobot;

trait PosterTrait
{
    public function directory(): string
    {
        return $this->directory;
    }

    public function account(): string
    {
        return $this->account;
    }
}
