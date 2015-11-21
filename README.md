# PHP Slack Bot

A simple bot user written in PHP using the Slack Real Time Messaging API https://api.slack.com/rtm

## Installation
With Composer


Create a new composer.json file and add the following

    {
        "minimum-stability" : "dev",
        "require": {
            "jclg/php-slack-bot": "dev-master"
        }
    }

Then run

    composer install

## Usage

```php
require 'vendor/autoload.php';
use PhpSlackBot\Bot;

// Custom command
class MyCommand extends \PhpSlackBot\Command\BaseCommand {

    protected function configure() {
        $this->setName('mycommand');
    }

    protected function execute($message, $context) {
        $this->send($this->getCurrentChannel(), null, 'Hello !');
    }

}

$bot = new Bot();
$bot->setToken('TOKEN'); // Get your token here https://my.slack.com/services/new/bot
$bot->loadCommand(new MyCommand());
$bot->run();
```

## Example commands

Example commands are located in `src/PhpSlackBot/Command/`

##### Ping Pong Command

Type `ping` in a channel and the bot should answer "Pong" to you.

##### Count Command

Type `count` several times in a channel and the bot should answer with 1 then 2...

##### Date Command

Type `date` in a channel and the current date.

##### Planning Poker Command

https://en.wikipedia.org/wiki/Planning_poker

Type `pokerp start` in a public channel with your team in order to start a planning poker session.

Direct message the bot with `pokerp vote number`. The bot will record your vote.

Type `pokerp status` to see the current status of the session (who has voted).

Type `pokerp end` in a public channel and the bot will output each vote.

## Load your own commands

You can load your own commands by implementing the \PhpSlackBot\Command\BaseCommand.

Then call PhpSlackBot\Bot::loadCommand method for each command you have to load.
