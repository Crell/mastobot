<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Colorfield\Mastodon\MastodonAPI;

require 'vendor/autoload.php';

$app = new MastobotApp();

/** @var Runner $runner */
$runner = $app[Runner::class];

/** @var StateLoader $loader */
$loader = $app[StateLoader::class];

$state = $loader->load();

$runner->run($state);
