<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Crell\Serde\Attributes\Field;
use Crell\Serde\Attributes\PostLoad;
use Crell\Serde\Renaming\Cases;

class AccountDef
{
    public function __construct(
        #[Field(serializedName: 'app_instance')]
        public readonly string $appInstance,

        #[Field(renameWith: Cases::snake_case)]
        public readonly string $clientId,

        #[Field(renameWith: Cases::snake_case)]
        public readonly string $clientSecret,

        #[Field(serializedName: 'token')]
        public readonly string $bearerToken,
    ) {}

    #[PostLoad]
    private function validate(): void
    {
        match(true) {
            empty($this->appInstance) => throw new \InvalidArgumentException('The app_instance must be specified in mastobot.yaml.'),
            empty($this->clientId) => throw new \InvalidArgumentException('The client_id must be specified in mastobot.yaml.'),
            empty($this->clientSecret) => throw new \InvalidArgumentException('The client_secret must be specified in mastobot.yaml.'),
            empty($this->bearerToken) => throw new \InvalidArgumentException('The token must be specified in mastobot.yaml.'),
            default => null,
        };
    }
}
