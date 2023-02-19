<?php

declare(strict_types=1);

namespace Crell\Mastobot;

trait ConfigMaker
{
    protected function makeConfig(mixed ...$args): Config
    {
        // Add in junk required fields.
        $args += [
            'appName' => 'appname',
            'accounts' => [
                'crell' => new AccountDef(
                    appInstance: 'an.instance',
                    clientId:  'abc',
                    clientSecret: 'def',
                    bearerToken: 'ghi',
                )
            ],
        ];

        return new Config(...$args);
    }
}
