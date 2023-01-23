<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Colorfield\Mastodon\MastodonAPI;

class MockConnectionFactory extends ConnectionFactory
{
    public static int $postCount = 0;

    public function __construct() {}

    public function getConnection(string $name): MastodonAPI
    {
        return new class extends MastodonAPI {

            public function __construct() {}

            public function post(mixed $endpoint, array $params = []): void
            {
                MockConnectionFactory::$postCount++;
            }
        };
    }
}
