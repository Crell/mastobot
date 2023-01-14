<?php

declare(strict_types=1);

namespace Crell\Mastobot;

class InvalidVisibility extends \InvalidArgumentException implements ErrorMessage
{
    public readonly string $invalidVisibility;

    public static function create(string $invalidVisibility): self
    {
        $new = new self();
        $new->invalidVisibility = $invalidVisibility;

        $visibilities = array_map(static fn (Visibility $case): string => $case->value, Visibility::cases());

        $legalVisibilities = \implode(', ', $visibilities);

        $message = 'The configuration file specified a visibility of "%s".  The only valid visibility settings are %s';

        $new->message = sprintf($message, $invalidVisibility, $legalVisibilities);
        return $new;
    }
}
