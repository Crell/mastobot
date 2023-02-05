<?php
declare(strict_types=1);

namespace Crell\Mastobot;

enum HttpMethod: string
{
    case Get = 'get';
    case Post = 'post';
    case Put = 'put';
}
