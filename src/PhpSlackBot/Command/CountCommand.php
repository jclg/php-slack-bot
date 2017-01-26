<?php
namespace PhpSlackBot\Command;

class CountCommand extends BaseCommand {

    private $count = 0;

    protected function configure() {
        $this->setName('count');
    }

    protected function execute($message, $context) {
        $this->send($this->getCurrentChannel(), null, $this->count);
        $this->count++;
    }

}