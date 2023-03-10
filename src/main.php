<?php

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/config/cons.php';

use DiscordBot\Lib\Bot;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;
use Discord\WebSockets\Intents;

$chatter = new Discord([
	'token'		=> BOT_TOKEN_CHATTER,
	'intents'	=> Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT,
	'logger'	=> Bot::createLogger('ChatterBot')
]);

$chatter->on('ready', function (Discord $chatter) {
	
	$listener = new Discord([
		'token'		=> BOT_TOKEN_LISTENER,
		'intents'	=> Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT,
		'logger'	=> Bot::createLogger('ListenerBot'),
		'loop'		=> $chatter->getLoop()
	]);
	
	$listener->on('ready', function (Discord $listener) use ($chatter) {
		
		$messageHandler = function (Message $message, Discord $listener) use ($chatter) {
			if($message->author && $message->author->id != $listener->id && $message->author->id != $chatter->id) {
				if($forward = Bot::processMessage($message)) {
					$newMessage = Bot::buildMessage($forward['message_id'], $chatter);
					$chatter->getChannel($forward['discord_id'])->sendMessage($newMessage);
				}
			}
		};
		
		Bot::updateChannels($listener);
		
		$listener->on(Event::MESSAGE_CREATE, $messageHandler);
		$listener->on(Event::MESSAGE_UPDATE, $messageHandler);
	});
	
	$listener->run();
});

$chatter->run();
