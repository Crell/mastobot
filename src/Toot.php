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
     *   ID of the toot this post is in reply to, if any.
     * @param bool $sensitive
     *   Whether or not attached media is sensitive.
     * @param Visibility $visibility
     *   Defaults to "Unlisted", which is generally polite for bots.
     * @param string|null $spoiler_text
     *   Also known as a Content Warning. The API calls it spoiler_text, for whatever reason.
     * @param string|null $language
     *   ISO 639 language code for this status.
     * @param \DateTimeImmutable|null $scheduledAt
     *   ISO 8601 Datetime at which to schedule a status.
     *   Must be at least 5 minutes in the future.
     */
    public function __construct(
        public string $status,
        public ?string $replyTo = null,
        public bool $sensitive = false,
        public Visibility $visibility = Visibility::Unlisted,
        public ?string $spoiler_text = null,
        public ?string $language = null,
        public ?\DateTimeImmutable $scheduledAt = null,
    ) {}

    /**
     *
     *
     * @return array<string, mixed>
     */
    public function asParams(): array
    {
        $ret = [];
        $ret['visibility'] = $this->visibility->value;

        if (!is_null($this->replyTo)) {
            $ret['in_reply_to_id'] = $this->replyTo;
        }

        if (!is_null($this->scheduledAt)) {
            $ret['scheduled_at'] = $this->scheduledAt->format('c');
        }

        foreach (['status', 'sensitive', 'spoiler_text', 'language'] as $key) {
            if (!is_null($this->$key)) {
                $ret[$key] = $this->$key;
            }
        }
        return $ret;
    }
}