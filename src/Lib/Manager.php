<?php

namespace DiscordBot\Lib;

use Discord\Parts\Channel\Message;
use Medoo\Medoo;

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
	
	public static function getChannel(String $discord_id) {
		$conn = self::dbConnect();
		$channels = $conn->select('channels', '*', ['discord_id'=>$discord_id]);
		
		return empty($channels) ? false : $channels[0];
	}
	
	public static function saveMessage(Int $channel_id, Message $message) {
		$conn = self::dbConnect();
		$conn->insert('messages', [
			'channel_id'	=> $channel_id,
			'discord_id'	=> $message->id,
			'author'		=> $message->author->username,
			'content'		=> $message->content,
			'type'			=> $message->type,
			'flags'			=> $message->flags,
			'timestamp'		=> $message->timestamp->toDateTimeString()
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
					'timestamp'		=> $embed->timestamp->toDateTimeString()
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
	}
}
