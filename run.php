<?php

declare(strict_types=1);

namespace Crell\Mastobot;

require 'vendor/autoload.php';

// Force the working directory to the project root, so that
// relative paths behave as we expect.
chdir(__DIR__);

try {
    $app = new MastobotApp();

    /** @var Runner $runner */
    $runner = $app[Runner::class];

    /** @var StateLoader $loader */
    $loader = $app[StateLoader::class];

    $state = $loader->load();

    $runner->run($state);

    $loader->save($state);
} catch (ErrorMessage $e) {
    print $e->getMessage() . PHP_EOL;
    exit(1);
}
