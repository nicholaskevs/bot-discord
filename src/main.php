<?php

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/config/cons.php';

use Discord\Discord;

$bot = new Discord([
	'token' => BOT_TOKEN
]);

$bot->on('ready', function (Discord $bot) {
	echo 'Bot ready', PHP_EOL;
});

$bot->run();
