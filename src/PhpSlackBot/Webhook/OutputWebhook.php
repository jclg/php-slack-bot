<?php
namespace PhpSlackBot\Webhook;

class OutputWebhook extends BaseWebhook {

    protected function configure() {
        $this->setName('output');
    }

    protected function execute($payload, $context) {
        $payload['channel'] = $this->getChannelIdFromChannelName($payload['channel']);
        $this->getClient()->send(json_encode($payload));
    }

}