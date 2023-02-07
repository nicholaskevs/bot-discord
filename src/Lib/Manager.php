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
			'content'		=> $message->content
		]);
		
	}
}
