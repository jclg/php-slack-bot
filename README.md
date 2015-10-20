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

    $bot = new Bot();
    $bot->setToken('TOKEN'); // Get your token here https://my.slack.com/services/new/bot
    $bot->run();
```
