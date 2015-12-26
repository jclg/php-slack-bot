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

## "Catch All" command

If you need to execute a command when an event occurs, you can set up a "catch all" command.

This special command will be triggered on all events and all other commands will be ignored.

```php
require 'vendor/autoload.php';
use PhpSlackBot\Bot;

// This special command executes on all events
class SuperCommand extends \PhpSlackBot\Command\BaseCommand {

    protected function configure() {
        // We don't have to configure a command name in this case
    }

    protected function execute($data, $context) {
        if ($data['type'] == 'message') {
            $channel = $this->getChannelNameFromChannelId($data['channel']);
            $username = $this->getUserNameFromUserId($data['user']);
            echo $username.' from '.($channel ? $channel : 'DIRECT MESSAGE').' : '.$data['text'].PHP_EOL;
        }
    }

}

$bot = new Bot();
$bot->setToken('TOKEN'); // Get your token here https://my.slack.com/services/new/bot
$bot->loadCatchAllCommand(new SuperCommand());
$bot->run();
```

## Incoming webhooks

The bot can also listen for incoming webhooks.

Commands are triggered from users messages inside Slack and webhooks are triggered from web post requests.

Custom webhooks can be loaded using the PhpSlackBot\Bot::loadWebhook method.

This is useful if you need to control the bot from an external service. For example, with IFTTT https://ifttt.com/maker

To enable webhooks, use the enableWebserver method before the run method.

You can also set a secret token to prevent unauthorized requests.


```php
$bot->enableWebserver(8080, 'secret'); // This will listen on port 8080
$bot->run();
```

Then, use the parameter "name" to trigger the corresponding webhook :

```
curl -X POST --data-urlencode 'auth=secret' --data-urlencode 'name=output' --data-urlencode 'payload={"type" : "message", "text": "This is a message", "channel": "#general"}' http://localhost:8080
```
