<?php

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/config/cons.php';

use DiscordBot\Lib\Manager;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

$bot = new Discord([
	'token' => BOT_TOKEN
]);

$bot->on('ready', function (Discord $bot) {
	
	$bot->on(Event::MESSAGE_CREATE, function (Message $message) {
		if($channel = Manager::getChannel($message->channel_id)) {
			Manager::saveMessage($channel['id'], $message);
		}
	});
});

$bot->run();