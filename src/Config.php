<?php

declare(strict_types=1);

namespace Crell\Mastobot;
use Crell\Serde\Attributes\DictionaryField;
use Crell\Serde\Attributes\Field;
use Crell\Serde\Attributes\PostLoad;
use Crell\Serde\KeyType;

class Config
{
    public const ConfigFileName = 'mastobot.yaml';

    /**
     * @param string $appName
     * @param string $appInstance
     * @param string $clientId
     * @param string $clientSecret
     * @param string $bearerToken
     * @param ?string $stateFile
     * @param array<string, mixed> $defaults
     * @param PosterDef[] $posters
     */
    public function __construct(
        #[Field(serializedName: 'app_name')]
        public readonly string $appName,

        #[DictionaryField(arrayType: AccountDef::class, keyType: KeyType::String)]
        public readonly array $accounts = [],

        #[Field(serializedName: 'status_file')]
        public readonly ?string $stateFile = 'mastobot_state.json',

        #[DictionaryField]
        public readonly array $defaults = ['visibility' => Visibility::Unlisted],

        #[DictionaryField(arrayType: PosterDef::class, keyType: KeyType::String)]
        public readonly array $posters = [],
    ) {}

    #[PostLoad]
    private function validate(): void
    {
        match(true) {
            empty($this->appName) => throw new \InvalidArgumentException('The app.name must be specified in mastobot.yaml.'),
            default => null,
        };
    }
}
