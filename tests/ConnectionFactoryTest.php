<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Serde\SerdeCommon;
use PHPUnit\Framework\TestCase;

class ConnectionFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function missing_account_throws_exception(): void
    {
        $this->expectException(MissingAccountDefinition::class);

        $f = new ConnectionFactory(new Config(appName: 'Test'), new SerdeCommon());

        $f->getConnection('fake');
    }
}
