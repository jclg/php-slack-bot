<?php
namespace PhpSlackBot\Command;

abstract class BaseCommand extends \PhpSlackBot\Base {

    public function executeCommand($message, $context) {
        return $this->execute($message, $context);
    }

}
