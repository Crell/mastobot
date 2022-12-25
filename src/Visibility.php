<?php

declare(strict_types=1);

namespace Crell\Mastobot;

enum Visibility: string
{
    case Public = 'public';
    case Unlisted = 'unlisted';
    case Private = 'private';
    case Direct = 'direct';
}