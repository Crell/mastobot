<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use bovigo\vfs\content\LargeFileContent;
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
                'b' => [
                    'status.txt'    => 'Testing B',
                ],
                'a' => [
                    'status.txt'    => 'Testing A',
                ],
                'c' => [
                    'status.txt'    => 'Testing C',
                ],
                'd.txt' => 'Testing D',
                'e.json' => '{"status": "Testing E", "visibility": "public"}',
                'f' => [
                    'status.json' => '{"status": "Testing F", "spoiler_text": "spoiler"}',
                ],
                'g.yaml' => "status: Testing G\nvisibility: public ",
                'h' => [
                    'status.yaml' => "status: Testing H\nspoiler_text: spoiler"
                ],
                'i' => [
                    'status.txt' => 'Testing images',
                    'image.png' => vfsStream::newFile('image.png')
                        ->withContent(LargeFileContent::withKilobytes(10)),
                ],
                'j' => [
                    'status.txt' => 'Testing images',
                    'image.jpg' => vfsStream::newFile('image.jpg')
                        ->withContent(LargeFileContent::withKilobytes(10)),
                ],
                'k' => [
                    'status.txt' => 'Testing images',
                    'image.json' => '{"description": "alt text"}',
                    'image.jpg' => vfsStream::newFile('image.jpg')
                        ->withContent(LargeFileContent::withKilobytes(10)),
                ],
                'l' => [
                    'status.txt' => 'Testing images',
                    'image.yaml' => 'description: alt text',
                    'image.jpg' => vfsStream::newFile('image.jpg')
                        ->withContent(LargeFileContent::withKilobytes(10)),
                ],
            ],
        ];
    }
}
