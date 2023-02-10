
DROP TABLE IF EXISTS `channels`;
CREATE TABLE `channels` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`discord_id` VARCHAR(25) NOT NULL,
	`name` VARCHAR(100) NULL,
	`type` TINYINT(2) NOT NULL,
	PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`channel_id` INT(11) UNSIGNED NOT NULL,
	`discord_id` VARCHAR(25) NOT NULL,
	`author` VARCHAR(100) NOT NULL,
	`content` VARCHAR(2000) NOT NULL,
	`type` TINYINT(2) NOT NULL,
	`flags` TINYINT(2) NULL,
	`timestamp` INT(11) NOT NULL,
	`edited_timestamp` INT(11) NULL,
	`forwarded_on` DATETIME NULL,
	PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `embeds`;
CREATE TABLE `embeds` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`message_id` INT(11) UNSIGNED NOT NULL,
	`url` VARCHAR(250) NULL,
	`author` VARCHAR(256) NULL,
	`title` VARCHAR(256) NULL,
	`description` VARCHAR(4096) NULL,
	`footer` VARCHAR(2048) NULL,
	`image` VARCHAR(250) NULL,
	`video` VARCHAR(250) NULL,
	`timestamp` INT(11) NULL,
	PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `embed_fields`;
CREATE TABLE `embed_fields` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`embed_id` INT(11) UNSIGNED NOT NULL,
	`name` VARCHAR(256) NOT NULL,
	`value` VARCHAR(1024) NOT NULL,
	PRIMARY KEY (`id`)
);
