<?php

declare(strict_types=1);

namespace Crell\Mastobot;


/**
 * Value Object of a Toot.
 *
 * Always use the constructor with named arguments, as the order of parameters is not guaranteed.
 *
 * @todo Add Media
 * @todo Add Polls
 * @todo Add Scheduling
 */
class Toot
{
    /**
     * @param string $status
     * @param string|null $replyTo
     * @param bool $sensitive
     *   Whether or not attached media is sensitive.
     * @param Visibility $visibility
     * @param string|null $spoiler_text
     * @param string|null $language
     *   ISO 639 language code for this status.
     */
    public function __construct(
        public string $status,
        public ?string $replyTo = null,
        public bool $sensitive = false,
        public Visibility $visibility = Visibility::Unlisted,
        public ?string $spoiler_text = null,
        public ?string $language = null,
    ) {
    }

    public function asParams(): array
    {
        $ret = [];
        $ret['visibility'] = $this->visibility->value;

        foreach (['status', 'replyTo', 'sensitive', 'spoiler_text', 'language'] as $key) {
            if (!is_null($this->$key)) {
                $ret[$key] = $this->$key;
            }
        }
        return $ret;
    }
}