<?php

declare(strict_types=1);

namespace Crell\Mastobot;

require 'vendor/autoload.php';

try {
    $app = new MastobotApp();

    /** @var Runner $runner */
    $runner = $app[Runner::class];

    /** @var StateLoader $loader */
    $loader = $app[StateLoader::class];

    $state = $loader->load();

    $runner->run($state);
} catch (ErrorMessage $e) {
    print $e->getMessage() . PHP_EOL;
    exit(1);
}
