# bot-discord

Simple Discord bot to save message content to database and forward it to other channel using 2 bot instances

## How to use

### Requirement

- PHP >=8.0
- MySQL
- For windows, add php folder path to system variables `path`, follow this [guide](https://www.computerhope.com/issues/ch000549.htm)

### How to install

1. Download [here](https://github.com/nicholaskevs/bot-discord/archive/refs/heads/master.zip)
2. Extract
3. Install dependency with `composer`
4. Go to `config` folder
5. Change `cons.php-template` into `cons.php`
6. Fill in `cons.php` with your data
7. Run all sql files in `schema` folder
8. Run `php discordbot.php` or open `discordbot.bat` for windows
