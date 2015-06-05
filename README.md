# PHP Slack Bot

A simple bot user written in PHP using the Slack Real Time Messaging API https://api.slack.com/rtm

## Installation
With Composer

    composer require jclg/php-slack-bot

## Usage

    <?php
    require 'vendor/autoload.php';
    use PhpSlackBot\Bot;

    $bot = new Bot();
    $bot->setToken('TOKEN'); // Get your token here https://my.slack.com/services/new/bot
    $bot->run();
