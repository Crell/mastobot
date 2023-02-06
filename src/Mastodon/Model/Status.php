<?php

declare(strict_types=1);

namespace Crell\Mastobot\Mastodon\Model;


use Crell\Mastobot\Visibility;
use Crell\Serde\Attributes\Field;
use Crell\Serde\Attributes\SequenceField;
use Crell\Serde\Renaming\Cases;

/**
 * Value Object of a Status.
 *
 * Always use the constructor with named arguments, as the order of parameters is not guaranteed.
 *
 * @todo Add Polls
 */
class Status
{
    /**
     * @var string[]
     */
    #[Field(serializedName: 'media_ids')]
    #[SequenceField]
    public array $mediaIds = [];

    /**
     * @param array<Media> $media
     */
    #[Field(exclude: true)]
    public array $media = [];

    /**
     * Note that the order of arguments is *not* guaranteed, except that "status" comes first.
     *
     * Use named arguments with this constructor.
     *
     * @param string $status
     * @param string|null $replyTo
     *   ID of the toot this post is in reply to, if any.
     * @param bool $sensitive
     *   Whether or not attached media is sensitive.
     * @param Visibility|null $visibility
     *   In practice this will default to Unlisted if not specified, which is good practice for bots.
     *   That is enforced elsewhere.
     * @param string|null $spoilerText
     *   Also known as a Content Warning. The API calls it spoiler_text, for whatever reason.
     * @param string|null $language
     *   ISO 639 language code for this status.
     * @param \DateTimeImmutable|null $scheduledAt
     *   ISO 8601 Datetime at which to schedule a status.
     *   Must be at least 5 minutes in the future.
     */
    public function __construct(
        public string $status,
        #[Field(serializedName: 'in_reply_to_id')]
        public ?string $replyTo = null,
        public bool $sensitive = false,
        public ?Visibility $visibility = null,
        #[Field(renameWith: Cases::snake_case)]
        public ?string $spoilerText = null,
        public ?string $language = null,
        #[Field(renameWith: Cases::snake_case)]
        public ?\DateTimeImmutable $scheduledAt = null,
    ) {}
}
