<?php
namespace PhpSlackBot\Command;

class PingPongCommand extends BaseCommand {

    protected function configure() {
        $this->setName('ping');
    }

    protected function execute($message, $context) {
        $response = array(
                          'id' => time(),
                          'type' => 'message',
                          'channel' => $message['channel'],
                          'text' => '<@'.$message['user'].'> Pong'
                          );
        return $response;
    }

}