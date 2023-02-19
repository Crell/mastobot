<?php

declare(strict_types=1);

namespace Crell\Mastobot\Mastodon;

use Crell\Mastobot\Mastodon\Model\Status;
use Crell\Serde\SerdeCommon;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertSame;

class MastodonClientTest extends TestCase
{
    /**
     * @test
     */
    public function status_message_becomes_correct_params(): void
    {
        $api = new class extends MastodonAPI {
            public function __construct() {}

            public function post(string $endpoint, array $params = []): array
            {
                assertArrayNotHasKey('language', $params);
                assertSame('test', $params['status']);
                assertSame('spoiler', $params['spoiler_text']);
                return [];
            }
        };

        $c = new MastodonClient($api, new SerdeCommon());

        $c->postStatus(new Status(status: 'test', spoilerText: 'spoiler'));
    }
}
