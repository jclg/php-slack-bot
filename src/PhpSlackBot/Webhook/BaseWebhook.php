<?php
namespace PhpSlackBot\Webhook;

abstract class BaseWebhook extends \PhpSlackBot\Base {

    public function executeWebhook($payload, $context) {
        return $this->execute($payload, $context);
    }

}
