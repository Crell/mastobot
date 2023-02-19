<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamDirectory;
use Crell\Serde\SerdeCommon;
use PHPUnit\Framework\TestCase;

class ConfigLoaderTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setupFilesystem(array $filesystem): void
    {
        $this->root = vfsStream::setup('root', null, $filesystem);
    }

    /**
     * @test
     * @dataProvider exampleBadConfigFiles()
     */
    public function missing_config_information(string $file, string $exception, ?string $message = null): void
    {
        $this->expectException($exception);

        if ($message) {
            $this->expectExceptionMessage($message);
        }

        $this->setupFilesystem([
            'mastobot.yaml' => $file
        ]);

        $path = $this->root->url() . '/';

        $l = new ConfigLoader(new SerdeCommon(), $path);

        $l->load();
    }

    public static function exampleBadConfigFiles(): iterable
    {
        yield 'missing instance' => [
            'file' =><<<END
app_name: beep
accounts:
  me:
    client_id: id
    client_secret: secret
    token: token
END,
            'exception' => \InvalidArgumentException::class,
        ];
        yield 'missing id' => [
            'file' =><<<END
app_name: beep
accounts:
  me:
    app_instance: phpc.social
    client_secret: secret
    token: token
END,
            'exception' => \InvalidArgumentException::class,
        ];
        yield 'missing secret' => [
            'file' =><<<END
app_name: beep
accounts:
  me:
    app_instance: phpc.social
    client_id: id
    token: token
END,
            'exception' => \InvalidArgumentException::class,
        ];
        yield 'missing token' => [
            'file' =><<<END
app_name: beep
accounts:
  me:
    app_instance: phpc.social
    client_id: id
    client_secret: secret
END,
            'exception' => \InvalidArgumentException::class,
        ];

        yield 'missing app_name' => [
            'file' =><<<END
END,
            'exception' => \InvalidArgumentException::class,
        ];

        yield 'missing accounts' => [
            'file' =><<<END
app_name: beep
END,
            'exception' => \InvalidArgumentException::class,
        ];

    }
}
