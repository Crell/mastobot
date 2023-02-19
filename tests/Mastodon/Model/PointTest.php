<?php

declare(strict_types=1);

namespace Crell\Mastobot\Mastodon\Model;

use PHPUnit\Framework\TestCase;

class PointTest extends TestCase
{

    /**
     * @test
     */
    public function stringify_is_correct(): void
    {
        $p = new Point(0.1, 0.2);

        self::assertEquals('0.1,0.2', $p->asString());
    }

    /**
     * @test
     * @dataProvider exampleBadPoints()
     * @covers \Crell\Mastobot\Mastodon\Model\Point::validate
     */
    public function out_of_bounds_throws(float $x, float $y): void
    {
        $this->expectException(\OutOfBoundsException::class);

        $p = new Point($x, $y);

        $rClass = new \ReflectionClass($p);
        $rMethod = $rClass->getMethod('validate');
        $rMethod->invoke($p);
    }

    /**
     * @test
     * @dataProvider examplePoints()
     * @covers \Crell\Mastobot\Mastodon\Model\Point::validate
     */
    public function in_bounds_validates(float $x, float $y): void
    {
        $p = new Point($x, $y);

        $rClass = new \ReflectionClass($p);
        $rMethod = $rClass->getMethod('validate');
        $rMethod->invoke($p);

        // So this test isn't considered risky.
        self::assertTrue(true);
    }

    public static function examplePoints(): iterable
    {
        yield [-0.2, 0];
        yield [0.2, 0];
        yield [0, -0.2];
        yield [0, 0.2];
    }

    public static function exampleBadPoints(): iterable
    {
        yield [-2, 0];
        yield [2, 0];
        yield [0, -2];
        yield [0, 2];
    }
}
