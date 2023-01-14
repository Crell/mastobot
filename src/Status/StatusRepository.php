<?php

declare(strict_types=1);

namespace Crell\Mastobot\Status;

use Crell\Mastobot\InvalidVisibility;
use Crell\Mastobot\Visibility;
use Crell\Serde\Serde;

class StatusRepository
{
    /**
     * @param array<string, mixed> $defaults
     */
    public function __construct(
        protected readonly Serde $serde,
        protected readonly string $directory,
        protected array $defaults,
    ) {
        if (isset($this->defaults['visibility']) && is_string($this->defaults['visibility'])) {
            $visibility = Visibility::tryFrom($this->defaults['visibility']);
            if (!$visibility) {
                throw InvalidVisibility::create($this->defaults['visibility']);
            }
            $this->defaults['visibility'] = $visibility;
        }
    }

    public function getRandom(): Status
    {
        $list = $this->fileList();
        $files = ($list instanceof \Traversable) ? iterator_to_array($list) : $list;
        $file = array_rand($files);
        return $this->loadStatus($files[$file]);
    }

    public function load(string $name): ?Status
    {
        $file = new \SplFileInfo($this->directory . '/' . $name);

        return $this->loadStatus($file);
    }

    protected function loadStatus(\SplFileInfo $record): ?Status
    {
        // Allow just plain text files as tweets, with no directory.
        if ($record->isFile() && $record->getExtension() === 'txt') {
            $status = file_get_contents((string)$record);
            return new Status($status, ...$this->defaults);
        }

        // Allow just JSON files as tweets, with no directory.
        if ($record->isFile() && $record->getExtension() === 'json') {
            $status = file_get_contents((string)$record);
            /** @var Status $toot */
            $toot = $this->serde->deserialize($status, from: 'json', to: Status::class);
            foreach ($this->defaults as $k => $v) {
                $toot->$k ??= $v;
            }
            return $toot;
        }

        // Directory support is mostly for later, once we want to allow
        // for attached media.  If you're not doing that, you probably don't
        // need to bother with directories.

        // Allow a directory with either JSON or text.
        if ($record->isDir()) {
            $textStatus = "$record/status.txt";
            if (file_exists($textStatus)) {
                $status = file_get_contents($textStatus);
                return new Status($status, ...$this->defaults);
            }

            $jsonStatus = "$record/status.json";
            if (file_exists($jsonStatus)) {
                $status = file_get_contents($jsonStatus);
                /** @var Status $toot */
                $toot = $this->serde->deserialize($status, from: 'json', to: Status::class);
                foreach ($this->defaults as $k => $v) {
                    $toot->$k ??= $v;
                }
                return $toot;
            }

            // @todo Add support for attaching media.
        }

        // If no toot could be loaded from here.
        return null;
    }

    /**
     * @return iterable<\SplFileInfo>
     */
    protected function fileList(): iterable
    {
        /** @var \SplFileInfo[] $postList */
        $postList = new \FilesystemIterator($this->directory,\FilesystemIterator::SKIP_DOTS);
        return $postList;
    }

    /**
     * @return string[]
     */
    public function nameList(): array
    {
        $ret = [];
        /** @var \SplFileInfo $item */
        foreach ($this->fileList() as $item) {
            $ret[] = $item->getBasename();
        }

        return $ret;
    }
}
