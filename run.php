<?php

declare(strict_types=1);

namespace Crell\Mastobot;

use Colorfield\Mastodon\MastodonAPI;

require 'vendor/autoload.php';

$app = new MastobotApp();

$api = $app[MastodonAPI::class];

/** @var Config $config */
$config = $app[Config::class];

/** @var State $state */
$state = $app[State::class];

/** @var Randomizer $randomizer */
$randomizer = $app[Randomizer::class];

foreach ($config->randomizers as $def) {
    if ($randomizer->previousBatchCompleted($def, $state)) {
        foreach ($randomizer->makeToots($def, $state) as $toot) {
            //var_dump($toot);
            $params = $toot->asParams();
            //var_dump($params);
            $reply = $api->post('/statuses', $params);
            //var_dump($reply);
        }
    }
}



$toot = new Toot('@Crell Testing', visibility: Visibility::Direct);

//var_dump($app[Config::class]);

//$reply = $api->post('/statuses', $toot->asParams());
//var_dump($reply);
