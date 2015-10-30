<?php
namespace PhpSlackBot\Command;

class PokerPlanningCommand extends BaseCommand {

    private $count = 0;
    private $initiator;
    private $scores = array();
    private $status = 'free';

    protected function configure() {
        $this->setName('pokerplanning');
    }

    protected function execute($message, $context) {
        $args = $this->getArgs($message);
        $command = isset($args[2]) ? $args[2] : '';

        switch ($command) {
        case 'start':
            $this->start($args);
            break;
        case 'status':
            $this->status();
            break;
        case 'vote':
            $this->vote($args);
            break;
        case 'end':
            $this->end();
            break;
        default:
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(),
                        'No comprendo. Use "pokerplanning start" or "pokerplanning status"');
        }
    }

    private function start($args) {
        if ($this->status == 'free') {
            $this->subject = isset($args[3]) ? $args[3] : null;
            $this->status = 'running';
            $this->initiator = $this->getCurrentUser();
            $this->scores = array();
            $this->send($this->getCurrentChannel(), null,
                        'Poker planning sessions start by '.$this->initiator."\n".
                        'Please vote'.(!is_null($this->subject) ? ' for '.$this->subject : ''));
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'Use "pokerplanning end" to end the session');
        }
        else {
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'A poker session is still active');
        }
    }

    private function status() {
        $message = 'Current status : '.$this->status;
        if ($this->status == 'running') {
            $message .= "\n".'Initiator : '.$this->initiator;
        }
        $this->send($this->getCurrentChannel(), null, $message);
        if ($this->status == 'running') {
            if (empty($this->scores)) {
                $this->send($this->getCurrentChannel(), null, 'No one has voted yet');
            }
            else {
                $message = '';
                foreach ($this->scores as $user => $score) {
                    $message .= $user.' has voted'."\n";
                }
                $this->send($this->getCurrentChannel(), null, $message);
            }
        }
    }

    private function vote($args) {
        if ($this->status == 'running') {
            $score = isset($args[3]) ? $args[3] : -1;
            $sequence = $this->getSequence();
            if (!in_array($score, $sequence)) {
                $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'Use "pokerplanning vote [number]". Choose [number] between '.implode(', ',$sequence));
            }
            else {
                $this->scores[$this->getCurrentUser()] = (int) $score;
                $this->send($this->getCurrentChannel(), $this->getCurrentUser(),
                            'Thank you! Your vote ('.$score.') has been recorded You can still change your vote until the end of the session');
            }
        }
        else {
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'There is no poker session. You can start one with "pokerplanning start"');
        }
    }

    private function end() {
        if ($this->status == 'running') {
            if ($this->getCurrentUser() == $this->initiator) {
                $message = 'Ending session'."\n".'Results : '."\n";
                if (empty($this->scores)) {
                    $message .= 'No vote !';
                }
                else {
                    foreach ($this->scores as $user => $score) {
                        $message .= $user.' => '.$score."\n";
                    }
                    $message .= '------------------'."\n";
                    $message .= 'Average score : '.(array_sum($this->scores) / count($this->scores));
                }
                $this->send($this->getCurrentChannel(), null, $message);
                $this->status == 'free';
            }
            else {
                $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'Only '.$this->initiator.' can end the session');
            }
        }
        else {
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'There is no poker session. You can start one with "pokerplanning start"');
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