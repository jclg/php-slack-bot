<?php
namespace PhpSlackBot\Command;

abstract class BaseCommand {
    private $name;
    abstract protected function configure();
    abstract protected function execute($message, $context);

    public function executeCommand($message, $context) {
        return $this->execute($message, $context);
    }

    public function getName() {
        $this->configure();
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }
}