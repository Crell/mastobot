<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use PHPUnit\Framework\TestCase;

class TootTest extends TestCase
{
    /**
     * @test
     * @dataProvider example_toots()
     */
    public function params_generate_correctly(Toot $toot, callable $test): void
    {
        $params = $toot->asParams();

        $test($params);
    }

    public function example_toots(): iterable
    {
        yield [
            'toot' => new Toot('test message'),
            'test' => function (array $params): void {
                self::assertSame('test message', $params['status']);
                self::assertSame('unlisted', $params['visibility']);
            },
        ];
        yield [
            'toot' => new Toot('test message', visibility: Visibility::Private, spoiler_text: 'spoiler'),
            'test' => function (array $params): void {
                self::assertSame('test message', $params['status']);
                self::assertSame('private', $params['visibility']);
                self::assertSame('spoiler', $params['spoiler_text']);
            },
        ];
    }

}
