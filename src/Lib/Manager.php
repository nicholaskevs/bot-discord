<?php

namespace DiscordBot\Lib;

use Discord\Parts\Channel\Message;
use Medoo\Medoo;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

Class Manager
{
	private static function dbConnect() {
		return new Medoo([
			'type'		=> 'mysql',
			'host'		=> DB_HOST,
			'username'	=> DB_USERNAME,
			'password'	=> DB_PASSWORD,
			'database'	=> DB_DBNAME
		]);
	}
	
	public static function getLogger() {
		$logger = new Logger('Logger');
		if(ENV_DEV) {
			$logger->pushHandler(new StreamHandler('php://stdout'));
		} else {
			$logger->pushHandler(new RotatingFileHandler('logs/discordbot.log', 7, Logger::INFO));
		}
		
		return $logger;
	}
	
	public static function saveMessage(Message $message, Bool $edit = false) {
		$conn = self::dbConnect();
		
		$channels = $conn->select('channels', '*', ['discord_id'=>$message->channel_id]);
		
		if(empty($channels)) return false;
		
		$conn->insert('messages', [
			'channel_id'		=> $channels[0]['id'],
			'discord_id'		=> $message->id,
			'author'			=> $message->author->username,
			'content'			=> $message->content,
			'type'				=> $message->type,
			'flags'				=> $message->flags,
			'timestamp'			=> $message->timestamp->getTimestamp(),
			'edited_timestamp'	=> ($edit ? $message->edited_timestamp->getTimestamp() : null)
		]);
		$message_id = $conn->id();
		
		if($message->embeds->count()) {
			foreach($message->embeds as $embed) {
				$conn->insert('embeds', [
					'message_id'	=> $message_id,
					'url'			=> $embed->url,
					'author'		=> $embed->author->name,
					'title'			=> $embed->title,
					'description'	=> $embed->description,
					'footer'		=> $embed->footer->text,
					'image'			=> $embed->image->url,
					'video'			=> $embed->video->url,
					'timestamp'		=> $embed->timestamp ? $embed->timestamp->getTimestamp() : null
				]);
				$embed_id = $conn->id();
				
				if($embed->fields->count()) {
					foreach($embed->fields as $embedField) {
						$conn->insert('embed_fields', [
							'embed_id'	=> $embed_id,
							'name'		=> $embedField->name,
							'value'		=> $embedField->value
						]);
					}
				}
			}
		}
		
		return true;
	}
}
