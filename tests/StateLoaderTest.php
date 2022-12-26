<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use bovigo\vfs\vfsStream;
use bovigo\vfs\vfsStreamDirectory;
use Crell\Serde\SerdeCommon;
use PHPUnit\Framework\TestCase;

class StateLoaderTest extends TestCase
{
    private vfsStreamDirectory $root;

    public function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup();
    }

    /** @test */
    public function missing_state_file_returns_new_state(): void
    {
        $stateFile = vfsStream::newFile('filename.json')->chmod(0644);

        // We have to do this silly song and dance to get the right VFS url, but still
        // have the file "not exist".
        $stateFile->at($this->root);
        $uri = $stateFile->url();
        $this->root->removeChild('filename.json');

        $l = new StateLoader($uri, new SerdeCommon());

        $state = $l->load();

        self::assertEquals([], $state->randomizerTimestamps);

        // Force the destructor to run.
        unset($state);

        // Verify the state file was saved out to disk.
        self::assertTrue($this->root->hasChild('filename.json'));
    }

    /** @test */
    public function empty_state_file_returns_existing_state(): void
    {
        $stateFile = vfsStream::newFile('filename.json')
            ->chmod(0644)
            ->withContent('{}');
        $stateFile->at($this->root);

        $l = new StateLoader($stateFile->url(), new SerdeCommon());

        $state = $l->load();

        self::assertEquals([], $state->randomizerTimestamps);

        // Force the destructor to run.
        unset($state);

        // Verify the state file was saved out to disk.
        self::assertTrue($this->root->hasChild('filename.json'));
    }

    /** @test */
    public function populated_state_file_returns_existing_state(): void
    {
        $stateFile = vfsStream::newFile('filename.json')
            ->chmod(0644)
            ->withContent('{"randomizerTimestamps":{"data":"1672077035"}}');
        $stateFile->at($this->root);

        $l = new StateLoader($stateFile->url(), new SerdeCommon());

        $state = $l->load();

        self::assertEquals(1672077035, $state->randomizerTimestamps['data']);

        // Force the destructor to run.
        unset($state);

        // Verify the state file was saved out to disk.
        self::assertTrue($this->root->hasChild('filename.json'));
    }

    /** @test */
    public function updated_state_object_writes_new_data_to_disk(): void
    {
        $stateFile = vfsStream::newFile('filename.json')
            ->chmod(0644)
            ->withContent('{"randomizerTimestamps":{"data":"1672077035"}}');
        $stateFile->at($this->root);

        $l = new StateLoader($stateFile->url(), new SerdeCommon());

        $state = $l->load();

        self::assertEquals(1672077035, $state->randomizerTimestamps['data']);

        // Add new data.
        $state->randomizerTimestamps['new'] = 12345;

        // Force the destructor to run.
        unset($state);

        // Verify the state file was saved out to disk.
        self::assertTrue($this->root->hasChild('filename.json'));
        $json = \json_decode(file_get_contents($stateFile->url()), true, 512, JSON_THROW_ON_ERROR);
        self::assertEquals(12345, $json['randomizerTimestamps']['new']);
    }
}
