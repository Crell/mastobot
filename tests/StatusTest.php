<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Mastobot\Mastodon\Model\Status;
use Crell\Serde\SerdeCommon;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    /**
     * @test
     * @dataProvider example_statuses()
     */
    public function params_generate_correctly(Status $toot, callable $test): void
    {
        $serde = new SerdeCommon();

        $array = $serde->serialize($toot, format: 'array');
        $test($array);
    }

    public function example_statuses(): iterable
    {
        yield [
            'toot' => new Status('test message'),
            'test' => function (array $params): void {
                self::assertSame('test message', $params['status']);
                self::assertArrayNotHasKey('visibility', $params);
                self::assertArrayNotHasKey('scheduled_at', $params);
            },
        ];
        yield [
            'toot' => new Status('test message', visibility: Visibility::Private, spoilerText: 'spoiler'),
            'test' => function (array $params): void {
                self::assertSame('test message', $params['status']);
                self::assertSame('private', $params['visibility']);
                self::assertSame('spoiler', $params['spoiler_text']);
                self::assertArrayNotHasKey('scheduled_at', $params);
            },
        ];
        yield [
            'toot' => new Status('test message', scheduledAt: new \DateTimeImmutable('2030-07-04 12:00:00', new \DateTimeZone('UTC'))),
            'test' => function (array $params): void {
                self::assertSame('test message', $params['status']);
                self::assertArrayNotHasKey('spoiler_text', $params);
                self::assertSame('2030-07-04T12:00:00.000+00:00', $params['scheduled_at']);
            },
        ];
    }

}
