<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Mastobot\Status\Status;

class MockConnectionFactory extends ConnectionFactory
{
    public static int $postCount = 0;

    public function __construct() {}

    public function getConnection(string $name): MastodonClient
    {
        return new class extends MastodonClient {

            public function __construct() {}

            public function postStatus(Status $status)
            {
                MockConnectionFactory::$postCount++;
            }


            public function post(mixed $endpoint, array $params = []): void
            {

            }
        };
    }
}
