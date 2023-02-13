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
	'logger'	=> Manager::createLogger()
]);

$bot->on('ready', function (Discord $bot) {
	
	Manager::updateChannels($bot);
	
	$bot->on(Event::MESSAGE_CREATE, function (Message $message, Discord $bot) {
		if($message->author->id != $bot->id) {
			if($forward = Manager::processMessage($message)) {
				$newMessage = Manager::buildMessage($forward['message_id'], $bot);
				$bot->getChannel($forward['discord_id'])->sendMessage($newMessage);
			}
		}
	});
	
	$bot->on(Event::MESSAGE_UPDATE, function (Message $message, Discord $bot) {
		if($message->author->id != $bot->id) {
			if($forward = Manager::processMessage($message)) {
				$newMessage = Manager::buildMessage($forward['message_id'], $bot);
				$bot->getChannel($forward['discord_id'])->sendMessage($newMessage);
			}
		}
	});
});

$bot->run();
