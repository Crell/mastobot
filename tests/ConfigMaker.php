<?php

declare(strict_types=1);

namespace Crell\Mastobot;

trait ConfigMaker
{
    protected function makeConfig(...$args): Config
    {
        $args += [
            'appName' => 'appname',
            'appInstance' => 'an.instance',
            'clientId' => 'abc',
            'clientSecret' => 'def',
            'bearerToken' => 'ghi',
        ];

        return new Config(...$args);
    }
}