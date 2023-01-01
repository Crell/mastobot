<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamContent;
use bovigo\vfs\vfsStreamDirectory;

trait FakeFilesystem
{
    private vfsStreamDirectory $root;

    private vfsStreamContent $dataDir;

    protected function setupFilesystem(): void
    {
        $this->root = vfsStream::setup('root', null, $this->getStructure());
        $this->dataDir = $this->root->getChild('data');
    }

    protected function getStructure(): array
    {
        return [
            'data' => [
                'a' => [
                    'status.txt'    => 'Testing A',
                ],
                'b' => [
                    'status.txt'    => 'Testing B',
                ],
                'c' => [
                    'status.txt'    => 'Testing C',
                ],
                'd.txt' => 'Testing D',
                'e.json' => '{"status": "Testing E", "visibility": "public"}',
                'f' => [
                    'status.json' => '{"status": "Testing F", "spoiler_text": "spoiler"}',
                ]
            ],
        ];
    }
}
