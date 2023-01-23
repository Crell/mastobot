<?php

declare(strict_types=1);

namespace Crell\Mastobot\Status;

use Crell\Mastobot\ConfigMaker;
use Crell\Mastobot\FakeFilesystem;
use Crell\Mastobot\Sequence\SequenceDef;
use Crell\Mastobot\Visibility;
use Crell\Serde\SerdeCommon;
use PHPUnit\Framework\TestCase;

class StatusRepoFactoryTest extends TestCase
{
    use ConfigMaker;
    use FakeFilesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupFilesystem();
    }

    /** @test */
    public function missing_defaults_use_default_defaults(): void
    {
        $def = new SequenceDef(directory: $this->dataDir->url(), account: 'crell', minHours: 1, maxHours: 5);
        $config = $this->makeConfig(
            posters: [$def],
        );

        $f = new StatusRepoFactory(new SerdeCommon(), $config);

        $repo = $f->getRepository($def);

        $rRepo = new \ReflectionObject($repo);
        $rDefaults = $rRepo->getProperty('defaults');
        $repoDefaults = $rDefaults->getValue($repo);

        self::assertEquals(['visibility' => Visibility::Unlisted], $repoDefaults);
    }

    /**
     * @test
     * @dataProvider exampleStatusDefaults
     */
    public function defaults_passthrough(array $defaults): void
    {
        $def = new SequenceDef(directory: $this->dataDir->url(), account: 'crell', minHours: 1, maxHours: 5);
        $config = $this->makeConfig(
            posters: [$def],
            defaults: $defaults,
        );

        $f = new StatusRepoFactory(new SerdeCommon(), $config);

        $repo = $f->getRepository($def);

        $rRepo = new \ReflectionObject($repo);
        $rDefaults = $rRepo->getProperty('defaults');
        $repoDefaults = $rDefaults->getValue($repo);

        self::assertEquals($defaults, $repoDefaults);
    }

    public function exampleStatusDefaults(): iterable
    {
        yield 'Specifying the same as the default defaults works' => [
            'defaults' => ['visibility' => Visibility::Unlisted],
        ];
        yield 'Specifying extra defaults works' => [
            'defaults' => ['visibility' => Visibility::Unlisted, 'language' => 'fr'],
        ];
        yield 'Specifying no defaults explicitly overrides built-in defaults' => [
            'defaults' => [],
        ];
    }

}
