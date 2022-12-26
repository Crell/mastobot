<?php

declare(strict_types=1);

namespace Crell\Mastobot;
use Crell\Serde\Attributes\Field;
use Crell\Serde\Attributes\PostLoad;
use Crell\Serde\Attributes\SequenceField;
use Crell\Serde\Renaming\Cases;

class Config
{
    public const ConfigFileName = 'mastobot.json';

    #[Field(serializedName: 'app.name')]
    public readonly string $appName;

    #[Field(serializedName: 'app.instance')]
    public readonly string $appInstance;

    #[Field(renameWith: Cases::snake_case)]
    public readonly string $clientId;

    #[Field(renameWith: Cases::snake_case)]
    public readonly string $clientSecret;

    #[Field(serializedName: 'token')]
    public readonly string $bearerToken;

    public readonly string $stateFile;

    /**
     * @var RandomizerDef[]
     */
    #[SequenceField(arrayType: RandomizerDef::class)]
    public readonly array $randomizers;

    #[PostLoad]
    private function validate(): void
    {
        // @todo I'm not sure why setting this with a default in a Field attribute isn't working.
        $this->stateFile ??= 'mastobot_state.json';

        match(true) {
            empty($this->appName) => throw new \InvalidArgumentException('The app.name must be specified in mastobot.json.'),
            empty($this->appInstance) => throw new \InvalidArgumentException('The app.instance must be specified in mastobot.json.'),
            empty($this->clientId) => throw new \InvalidArgumentException('The client_id must be specified in mastobot.json.'),
            empty($this->clientSecret) => throw new \InvalidArgumentException('The client_secret must be specified in mastobot.json.'),
            empty($this->bearerToken) => throw new \InvalidArgumentException('The token must be specified in mastobot.json.'),
            default => null,
        };
    }
}