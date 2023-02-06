<?php

declare(strict_types=1);

namespace Crell\Mastobot\Status;

use Crell\Mastobot\InvalidVisibility;
use Crell\Mastobot\Mastodon\Model\Media;
use Crell\Mastobot\Mastodon\Model\Status;
use Crell\Mastobot\Visibility;
use Crell\Serde\Serde;
use SplFileInfo;
use function Crell\fp\indexBy;

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
            $this->defaults['visibility'] = Visibility::tryFrom($this->defaults['visibility']) ?? throw InvalidVisibility::create($this->defaults['visibility']);
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

    protected function loadTextStatus(\SplFileInfo $file): Status
    {
        $status = file_get_contents((string)$file);
        return new Status($status, ...$this->defaults);
    }

    protected function loadStatusViaSerde(SplFileInfo $file, string $format): Status
    {
        $json = file_get_contents((string)$file);
        /** @var Status $status */
        $status = $this->serde->deserialize($json, from: $format, to: Status::class);
        foreach ($this->defaults as $k => $v) {
            $status->$k ??= $v;
        }
        return $status;
    }

    /**
     * Loads a status object from the repository.
     */
    protected function loadStatus(\SplFileInfo $record): ?Status
    {
        if ($record->isFile()) {
            return match ($record->getExtension()) {
                'txt' => $this->loadTextStatus($record),
                'json' => $this->loadStatusViaSerde($record, 'json'),
                'yaml' => $this->loadStatusViaSerde($record, 'yaml'),
                default => null,
            };
        }

        // Directory support is mostly for later, once we want to allow
        // for attached media.  If you're not doing that, you probably don't
        // need to bother with directories.

        // Allow a directory with either JSON or text.
        if ($record->isDir()) {
            $textStatus = "$record/status.txt";
            $jsonStatus = "$record/status.json";
            $yamlStatus = "$record/status.yaml";
            $status = match (true) {
                file_exists($textStatus) => $this->loadTextStatus(new SplFileInfo($textStatus)),
                file_exists($jsonStatus) => $this->loadStatusViaSerde(new SplFileInfo($jsonStatus), 'json'),
                file_exists($yamlStatus) => $this->loadStatusViaSerde(new SplFileInfo($yamlStatus), 'yaml'),
                default => null,
            };

            // If there was no status file found, just stop.
            if (!isset($status)) {
                return null;
            }

            return $this->attachMedia($status, $record);
        }

        // If no status could be loaded from here.
        return null;
    }

    /**
     * Attach any relevant media to a Status.
     *
     * For now we only support image-based media, as posting anything else
     * requires asynchronous interaction with the server, which is much more involved.
     */
    protected function attachMedia(Status $status, SplFileInfo $record): Status
    {
        $isImage = static fn (\SplFileInfo $file): bool
           => in_array($file->getExtension(), ['png', 'jpeg', 'jpg', 'gif', 'webp'], true);

        // Now check for images to attach.
        $files = iterator_to_array(new \FilesystemIterator($record->getPath() . '/' . $record->getFilename(),\FilesystemIterator::SKIP_DOTS));
        $files = indexBy(fn(SplFileInfo $file) => $file->getFilename())($files);

        $images = array_filter($files, $isImage);

        // Force lexical order.
        ksort($images);

        /** @var SplFileInfo $image */
        foreach ($images as $image) {
            $fileBase = $image->getBasename($image->getExtension());
            $jsonFile = $fileBase . 'json';
            $yamlFile = $fileBase . 'yaml';
            $status->media[] = match (true) {
                isset($files[$jsonFile]) => $this->loadMediaViaSerde($files[$jsonFile], $image,'json'),
                isset($files[$yamlFile]) => $this->loadMediaViaSerde($files[$yamlFile], $image,'yaml'),
                default => new Media(file: $image),
            };
            // @todo Thumbnails
        }
        return $status;
    }

    protected function loadMediaViaSerde(SplFileInfo $mediaDataFile, SplFileInfo $imageFile, string $format): Media
    {
        $json = file_get_contents((string)$mediaDataFile);
        /** @var Media $media */
        $media = $this->serde->deserialize($json, from: $format, to: Media::class);
        $media->file = $imageFile;
        return $media;
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

        sort($ret);

        return $ret;
    }
}
