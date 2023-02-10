<?php

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/config/cons.php';

use DiscordBot\Lib\Manager;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;

$bot = new Discord([
	'token'		=> BOT_TOKEN,
	'intents'	=> Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT,
	'logger'	=> Manager::getLogger()
]);

$bot->on('ready', function (Discord $bot) {
	
	$bot->on(Event::MESSAGE_CREATE, function (Message $message) {
		Manager::saveMessage($message);
	});
	
	$bot->on(Event::MESSAGE_UPDATE, function (Message $message) {
		Manager::saveMessage($message, true);
	});
});

$bot->run();
