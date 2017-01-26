<?php
namespace PhpSlackBot\Command;

class DateCommand extends BaseCommand {

    protected function configure() {
        $this->setName('date');
    }

    protected function execute($message, $context) {
        $this->send($this->getCurrentChannel(), null, date("D M j G:i:s T Y"));
    }

}