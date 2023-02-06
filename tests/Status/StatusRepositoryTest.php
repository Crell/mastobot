<?php

declare(strict_types=1);

namespace Crell\Mastobot\Status;

use bovigo\vfs\vfsStream;
use Crell\Mastobot\FakeFilesystem;
use Crell\Mastobot\InvalidVisibility;
use Crell\Mastobot\Mastodon\Model\Media;
use Crell\Mastobot\Mastodon\Model\Status;
use Crell\Mastobot\Visibility;
use Crell\Serde\SerdeCommon;
use PHPUnit\Framework\TestCase;

class StatusRepositoryTest extends TestCase
{
    use FakeFilesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupFilesystem();
    }

    /** @test */
    public function name_list_is_complete_and_ordered(): void
    {
        $r = new StatusRepository(new SerdeCommon(), $this->dataDir->url(), []);

        $names = $r->nameList();
        $expectedList = array_keys($this->getStructure()['data']);
        sort($expectedList);

        self::assertEquals($expectedList, $names);
    }

    /** @test */
    public function invalid_visibility_throws_exception(): void
    {
        $this->expectException(InvalidVisibility::class);
        $this->expectExceptionMessage('The configuration file specified a visibility of "everyone".  The only valid visibility settings are public, unlisted, private, direct');
        $r = new StatusRepository(new SerdeCommon(), $this->dataDir->url(), ['visibility' => 'everyone']);
    }

    /**
     * @test
     * @dataProvider exampleStatusesByName()
     */
    public function loading_by_name(string $name, array $defaults, ?Status $expected): void
    {
        $r = new StatusRepository(new SerdeCommon(), $this->dataDir->url(), $defaults);

        $status = $r->load($name);

        self::assertEquals($expected, $status);
    }

    public function exampleStatusesByName(): iterable
    {
        yield 'Text directory with no defaults' => [
            'name' => 'a',
            'defaults' => [],
            'expected' => new Status('Testing A'),
        ];
        yield 'Text directory with visibility default' => [
            'name' => 'b',
            'defaults' => ['visibility' => Visibility::Unlisted],
            'expected' => new Status('Testing B', visibility: Visibility::Unlisted),
        ];
        yield 'Text directory with visibility default as a string, which it is in the config file' => [
            'name' => 'b',
            'defaults' => ['visibility' => 'unlisted'],
            'expected' => new Status('Testing B', visibility: Visibility::Unlisted),
        ];
        yield 'Text directory with visibility and language defaults' => [
            'name' => 'b',
            'defaults' => ['visibility' => Visibility::Unlisted, 'language' => 'en'],
            'expected' => new Status('Testing B', visibility: Visibility::Unlisted, language: 'en'),
        ];
        yield 'Text file with defaults' => [
            'name' => 'd.txt',
            'defaults' => ['visibility' => Visibility::Unlisted, 'language' => 'en'],
            'expected' => new Status('Testing D', visibility: Visibility::Unlisted, language: 'en'),
        ];
        yield 'JSON file with no defaults uses values from the file' => [
            'name' => 'e.json',
            'defaults' => [],
            'expected' => new Status('Testing E', visibility: Visibility::Public),
        ];
        yield 'JSON file with defaults preferences values from the file' => [
            'name' => 'e.json',
            'defaults' => ['visibility' => Visibility::Unlisted, 'language' => 'en'],
            'expected' => new Status('Testing E', visibility: Visibility::Public, language: 'en'),
        ];
        yield 'JSON directory with defaults preferences values from the file' => [
            'name' => 'f',
            'defaults' => ['visibility' => Visibility::Unlisted],
            'expected' => new Status('Testing F', visibility: Visibility::Unlisted, spoilerText: 'spoiler'),
        ];
        yield 'YAML file with no defaults uses values from the file' => [
            'name' => 'g.yaml',
            'defaults' => [],
            'expected' => new Status('Testing G', visibility: Visibility::Public),
        ];
        yield 'YAML file with defaults preferences values from the file' => [
            'name' => 'g.yaml',
            'defaults' => ['visibility' => Visibility::Unlisted, 'language' => 'en'],
            'expected' => new Status('Testing G', visibility: Visibility::Public, language: 'en'),
        ];
        yield 'YAML directory with defaults preferences values from the file' => [
            'name' => 'h',
            'defaults' => ['visibility' => Visibility::Unlisted],
            'expected' => new Status('Testing H', visibility: Visibility::Unlisted, spoilerText: 'spoiler'),
        ];
        yield 'Not-found name returns null' => [
            'name' => 'nope',
            'defaults' => [],
            'expected' => null,
        ];

        // Media
        $s = new Status('Testing images');
        $s->media[] = new Media(
            new \SplFileInfo(vfsStream::url('root/data/i/image.png')),
        );
        yield 'png image is included' => [
            'name' => 'i',
            'defaults' => [],
            'expected' => $s,
        ];

        $s = new Status('Testing images');
        $s->media[] = new Media(new \SplFileInfo(vfsStream::url('root/data/j/image.jpg')));
        yield 'jpg image is included' => [
            'name' => 'j',
            'defaults' => [],
            'expected' => $s,
        ];

        $s = new Status('Testing images');
        $s->media[] = new Media(
            new \SplFileInfo(vfsStream::url('root/data/k/image.jpg')),
            description: 'alt text',
        );
        yield 'jpg image with json metadata' => [
            'name' => 'k',
            'defaults' => [],
            'expected' => $s,
        ];

        $s = new Status('Testing images');
        $s->media[] = new Media(
            new \SplFileInfo(vfsStream::url('root/data/l/image.jpg')),
            description: 'alt text',
        );
        yield 'jpg image with yaml metadata' => [
            'name' => 'l',
            'defaults' => [],
            'expected' => $s,
        ];

    }

    /**
     * @test
     *
     * This is the best I could come up with for testing random. It asserts
     * that a status is returned each time, and that it's not always the same one.
     * That technically has an edge case where it will fail one time in 6^10 (as there's
     * 6 possible values in the test data and 10 iterations), but I don't have a better idea.
     */
    public function loading_random(): void
    {
        $r = new StatusRepository(new SerdeCommon(), $this->dataDir->url(), []);

        $statuses = [];
        for ($i = 0; $i< 10; $i++) {
            $statuses[$i] = $r->getRandom();
            self::assertInstanceOf(Status::class, $statuses[$i]);
        }

        $messages = array_map(static fn(Status $s): string => $s->status, $statuses);
        $unique = array_unique($messages);

        self::assertNotCount(1, $unique);
    }
}
