<?php

declare(strict_types=1);

namespace Crell\Mastobot;

class MissingAccountDefinition extends \InvalidArgumentException implements ErrorMessage
{
    public readonly string $invalidAccount;

    public static function create(string $invalidAccount): self
    {
        $new = new self();
        $new->invalidAccount = $invalidAccount;

        $message = 'The configuration file specifies an account of %s, but that account is not defined.';

        $new->message = sprintf($message, $invalidAccount);
        return $new;
    }
}
