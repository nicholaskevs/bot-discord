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
	
	public static function saveMessage(Message $message) {
		$conn = self::dbConnect();
		$conn->insert('messages', ['content'=>$message->content]);
	}
}
