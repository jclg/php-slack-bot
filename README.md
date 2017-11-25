# PHP Slack Bot

A simple bot user written in PHP using the Slack Real Time Messaging API https://api.slack.com/rtm

## Installation
With Composer

    composer require jclg/php-slack-bot

## Usage

Create a php file called `bot.php` with the following content

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
$bot->loadInternalCommands(); // This loads example commands
$bot->run();
```

Then run `php bot.php` from the command line (terminal).

## Example commands

Example commands are located in `src/PhpSlackBot/Command/` and can be loaded with `$bot->loadInternalCommands();`

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

This special command will be triggered on all events.

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
$bot->loadInternalWebhooks(); // Load the internal "output" webhook
$bot->enableWebserver(8080, 'secret'); // This will listen on port 8080
$bot->run();
```

Then, use the parameter "name" to trigger the corresponding webhook :

```
curl -X POST --data-urlencode 'auth=secret' --data-urlencode 'name=output' --data-urlencode 'payload={"type" : "message", "text": "This is a message", "channel": "#general"}' http://localhost:8080
```

## Active Messaging

The example provided in the *usage section* above sets the bot to be reactive. The reactive bot is not capable of sending messages to any user on its own. It must be given an input to get a response from. In other words, the bot can only **react** to inputs given to it. 

*Active Messaging* means that as a developer, you would be able to **send messages to your users without them sending a message to the bot first**. It is useful when you have to notify your users about something e.g. a bot which can check a user's birthday and wish them, or tell them weather outside every 2 hours without them having to type in a command everytime. There can be many other uses.

You can use active messaging this way: 

```php
$bot = new Bot();
$bot->setToken('TOKEN'); // Get your token here https://my.slack.com/services/new/bot
$bot->loadInternalCommands(); // This loads example commands
$bot->loadPushNotifier(function () {
	return [
		'channel' => '#general',
		'username' => '@slacker',
		'message' => "Happy Birthday!! Make sure you have fun today. :-)"
	];
});

$bot->loadPushNotifier(function () {
	return [
		'channel' => '@slacker',
		'username' => null,
		'message' => "Current UNIX timestamp is: " . time()
	];
}, 1800);
$bot->run();

```

In the example above, we have set two active messages. 

**First message** will send the following message in the '#general' channel: 

```
@slacker Happy Birthday!! Make sure you have fun today. :-)
```

It will be triggered only **once**, 10 seconds after launching the script. Slack servers normally establish the connection by then.

**Second message** is a periodic message. It will be sent to the user having the username *slacker* in the team. It won't mention anyone and will be repeated every 30 minutes (1800 seconds). The message should appear as: 

```
Current UNIX timestamp is: 1489145707
Current UNIX timestamp is: 1489147507
```

That should happen within 1 hour of launching. 

*NOTE*: The first message would appear 30 minutes after launching.

The function you add using `loadPushNotifier` must return an array containing the following keys:

- **channel**: Name of the channel or user to whom the message is to be sent. Channel names should have the `#` prefix while usernames must have the `@` prefix. If you do not set a prefix, the name is assumed to be a channel name. Using prefixes is recommended here.
- **username**: Any username to be mentioned ahead of the message. You can specify the name without the `@` prefix here, though you might want to use the prefix to maintain uniformity within the code.
- **message**: The actual message to be sent.

