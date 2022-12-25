<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Colorfield\Mastodon\MastodonAPI;

require 'vendor/autoload.php';

date_default_timezone_set('America/Chicago');

$app = new MastobotApp();

$api = $app[MastodonAPI::class];

$toot = new Toot('@Crell Testing', visibility: Visibility::Direct);

$reply = $api->post('/statuses', $toot->asParams());

var_dump($reply);
