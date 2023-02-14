<?php

namespace DiscordBot\Lib;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\Parts\Embed\Embed;
use Medoo\Medoo;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

Class Bot
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
	
	public static function createLogger() {
		$logger = new Logger('Logger');
		if(ENV_DEV) {
			$logger->pushHandler(new StreamHandler('php://stdout'));
		} else {
			$logger->pushHandler(new RotatingFileHandler('logs/discordbot.log', 7, Logger::INFO));
		}
		
		return $logger;
	}
	
	public static function updateChannels(Discord $bot) {
		$db = self::dbConnect();
		
		$channels = $db->select('channels', '*');
		$updated = 0;
		
		foreach($channels as $channel) {
			if($update = $bot->getChannel($channel['discord_id'])) {
				$db->update('channels', [
					'name'	=> $update->name,
					'topic'	=> $update->topic,
					'type'	=> $update->type,
					'flags'	=> $update->flags,
					'nsfw'	=> ($update->nsfw ? true : false)
				], [
					'id' => $channel['id']
				]);
				$updated++;
			}
		}
		
		unset($db);
		$bot->getLogger()->info('channel updated', ['totalChannel'=>count($channels), 'updated'=>$updated]);
	}
	
	public static function processMessage(Message $message) {
		$db = self::dbConnect();
		
		$channel = $db->get('forwarder', [
			'[>]channels (from)'	=> [
				'fromChannel_id'=>'id'
			],
			'[>]channels (to)'		=> [
				'toChannel_id' => 'id'
			]
		], [
			'from.id',
			'to.discord_id'
		], [
			'from.discord_id' => $message->channel_id
		]);
		
		if(is_null($channel)) {
			unset($db);
			return false;
		}
		
		if($db->has('users', ['discord_id'=>$message->author->id, 'ignore'=>true])) {
			unset($db);
			return false;
		}
		
		if($user_id = $db->get('users', 'id', ['discord_id'=>$message->author->id])) {
			$db->update('users', [
				'username'		=> $message->author->username,
				'discriminator'	=> $message->author->discriminator,
				'avatar'		=> $message->author->avatar,
				'banner'		=> $message->author->banner
			], [
				'id' => $user_id
			]);
		} else {
			$db->insert('users', [
				'discord_id'	=> $message->author->id,
				'username'		=> $message->author->username,
				'discriminator'	=> $message->author->discriminator,
				'avatar'		=> $message->author->avatar,
				'banner'		=> $message->author->banner,
				'bot'			=> ($message->author->bot ? true : false),
				'webhook'		=> ($message->webhook_id ? true : false),
				'ignore'		=> false
			]);
			$user_id = $db->id();
		}
		
		$db->insert('messages', [
			'channel_id'		=> $channel['id'],
			'user_id'			=> $user_id,
			'discord_id'		=> $message->id,
			'content'			=> $message->content,
			'type'				=> $message->type,
			'flags'				=> $message->flags,
			'timestamp'			=> $message->timestamp->getTimestamp(),
			'edited_timestamp'	=> ($message->edited_timestamp ? $message->edited_timestamp->getTimestamp() : null)
		]);
		$message_id = $db->id();
		
		foreach($message->embeds as $embed) {
			if($embed->type == Embed::TYPE_RICH) {
				$db->insert('embeds', [
					'message_id'	=> $message_id,
					'url'			=> $embed->url,
					'author'		=> $embed->author->name,
					'title'			=> $embed->title,
					'description'	=> $embed->description,
					'footer'		=> $embed->footer->text,
					'image'			=> $embed->image->url,
					'timestamp'		=> ($embed->timestamp ? $embed->timestamp->getTimestamp() : null)
				]);
				$embed_id = $db->id();
				
				foreach($embed->fields as $embedField) {
					$db->insert('embed_fields', [
						'embed_id'	=> $embed_id,
						'name'		=> $embedField->name,
						'value'		=> $embedField->value
					]);
				}
			}
		}
		
		foreach($message->attachments as $attachment) {
			$db->insert('attachments', [
				'message_id'	=> $message_id,
				'discord_id'	=> $attachment->id,
				'url'			=> $attachment->url,
				'content_type'	=> $attachment->content_type,
				'filename'		=> $attachment->filename,
				'description'	=> $attachment->description,
				'size'			=> $attachment->size
			]);
		}
		
		unset($db);
		return [
			'message_id'	=> $message_id,
			'discord_id'	=> $channel['discord_id']
		];
	}
	
	public static function buildMessage(Int $message_id, Discord $bot) {
		$db = self::dbConnect();
		
		$message = $db->get('messages', '*', ['id'=>$message_id]);
		$attachments = $db->select('attachments', '*', ['message_id'=>$message_id]);
		$embeds = $db->select('embeds', '*', ['message_id'=>$message_id]);
		
		$newMessage = new MessageBuilder();
		$newMessage->setContent($message['edited_timestamp'] ? '(Edited) '.$message['content'] : $message['content']);
		
		if($attachments) {
			foreach($attachments as $attachment) {
				$newMessage->addFileFromContent($attachment['filename'], $attachment['url']);
			}
		}
		
		if($embeds) {
			foreach($embeds as $embed) {
				$newEmbed = new Embed($bot);
				
				if($embed['author']) $newEmbed->setAuthor($embed['author']);
				if($embed['url']) $newEmbed->setURL($embed['url']);
				if($embed['title']) $newEmbed->setTitle($embed['title']);
				if($embed['description']) $newEmbed->setDescription($embed['description']);
				if($embed['footer']) $newEmbed->setFooter($embed['footer']);
				if($embed['image']) $newEmbed->setImage($embed['image']);
				if($embed['timestamp']) $newEmbed->setTimestamp($embed['timestamp']);
				
				if($fields = $db->select('embed_fields', '*', ['embed_id'=>$embed['id']])) {
					foreach($fields as $field) {
						$newEmbed->addFieldValues($field['name'], $field['value']);
					}
				}
				
				$newMessage->addEmbed($newEmbed);
			}
		}
		
		unset($db);
		return $newMessage;
	}
}
