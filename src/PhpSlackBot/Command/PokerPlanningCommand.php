<?php
namespace PhpSlackBot\Command;

class PokerPlanningCommand extends BaseCommand {

    protected function configure() {
        $this->setName('pokerplanning');
    }

    protected function execute($message, $context) {
        $args = $this->getArgs($message);
        $command = isset($args[2]) ? $args[2] : '';

        if ($command == 'start') {
            if (count($args) < 4) {
                $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'Please specify at least one user');
            }
            else  {
                $users = array_slice($args, 3);
                var_dump($context);
                $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'Starting Poker Planning with '.implode(' ', $users));
                foreach ($users as $user) {
                    $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'Please choose a number between '.implode(' ', $this->getSequence()));
                }
            }
        }
        else if ($command == 'status') {
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'Status');
        }
        else {
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'No comprendo. Use "pokerplanning start" or "pokerplanning status"');
        }
    }

    private function getArgs($message) {
        $args = array();
        if (isset($message['text'])) {
            $args = array_values(array_filter(explode(' ', $message['text'])));
        }
        return $args;
    }

    private function getSequence() {
        return array(0, 1, 2, 3, 5, 8, 13, 20);
    }

}