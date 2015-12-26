<?php
namespace PhpSlackBot\Command;

class PokerPlanningCommand extends BaseCommand {

    private $count = 0;
    private $initiator;
    private $scores = array();
    private $status = 'free';

    protected function configure() {
        $this->setName('pokerp');
    }

    protected function execute($message, $context) {
        $args = $this->getArgs($message);
        $command = isset($args[1]) ? $args[1] : '';

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
                        'No comprendo. Use "'.$this->getName().' start" or "'.$this->getName().' status"');
        }
    }

    private function start($args) {
        if ($this->status == 'free') {
            $this->subject = isset($args[2]) ? $args[2] : null;
            if (!is_null($this->subject)) {
                $this->subject = str_replace(array('<', '>'), '', $this->subject);
            }
            $this->status = 'running';
            $this->initiator = $this->getCurrentUser();
            $this->scores = array();
            $this->send($this->getCurrentChannel(), null,
                        'Poker planning sessions start by '.$this->getUserNameFromUserId($this->initiator)."\n".
                        'Please vote'.(!is_null($this->subject) ? ' for '.$this->subject : ''));
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'Use "'.$this->getName().' end" to end the session');
        }
        else {
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'A poker session is still active');
        }
    }

    private function status() {
        $message = 'Current status : '.$this->status;
        if ($this->status == 'running') {
            $message .= "\n".'Initiator : '.$this->getUserNameFromUserId($this->initiator);
        }
        $this->send($this->getCurrentChannel(), null, $message);
        if ($this->status == 'running') {
            if (empty($this->scores)) {
                $this->send($this->getCurrentChannel(), null, 'No one has voted yet');
            }
            else {
                $message = '';
                foreach ($this->scores as $user => $score) {
                    $message .= $this->getUserNameFromUserId($user).' has voted'."\n";
                }
                $this->send($this->getCurrentChannel(), null, $message);
            }
        }
    }

    private function vote($args) {
        if ($this->status == 'running') {
            $score = isset($args[2]) ? $args[2] : -1;
            $sequence = $this->getSequence();
            if (!in_array($score, $sequence)) {
                $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'Use "'.$this->getName().' vote [number]". Choose [number] between '.implode(', ',$sequence));
            }
            else {
                $this->scores[$this->getCurrentUser()] = (int) $score;
                $this->send($this->getCurrentChannel(), $this->getCurrentUser(),
                            'Thank you! Your vote ('.$score.') has been recorded You can still change your vote until the end of the session');
            }
        }
        else {
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'There is no poker session. You can start one with "'.$this->getName().' start"');
        }
    }

    private function end() {
        if ($this->status == 'running') {
            if ($this->getCurrentUser() == $this->initiator) {
                $message = 'Ending session'.(!is_null($this->subject) ? ' for '.$this->subject : '')."\n".'Results : '."\n";
                if (empty($this->scores)) {
                    $message .= 'No vote !';
                }
                else {
                    foreach ($this->scores as $user => $score) {
                        $message .= $this->getUserNameFromUserId($user).' => '.$score."\n";
                    }
                    $message .= '------------------'."\n";
                    $message .= 'Average score : '.$this->getAverageScore()."\n";
                    $message .= 'Median score : '.$this->getMedianScore();
                }
                $this->send($this->getCurrentChannel(), null, $message);
                $this->status = 'free';
            }
            else {
                $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'Only '.$this->getUserNameFromUserId($this->initiator).' can end the session');
            }
        }
        else {
            $this->send($this->getCurrentChannel(), $this->getCurrentUser(), 'There is no poker session. You can start one with "'.$this->getName().' start"');
        }
    }

    private function getArgs($message) {
        $args = array();
        if (isset($message['text'])) {
            $args = array_values(array_filter(explode(' ', $message['text'])));
        }
        $commandName = $this->getName();
        // Remove args which are before the command name
        $finalArgs = array();
        $remove = true;
        foreach ($args as $arg) {
            if ($commandName == $arg) {
                $remove = false;
            }
            if (!$remove) {
                $finalArgs[] = $arg;
            }
        }
        return $finalArgs;
    }

    private function getAverageScore() {
        return array_sum($this->scores) / count($this->scores);
    }

    private function getMedianScore() {
        $arr = $this->scores;
        sort($arr);
        $count = count($arr);
        $middleval = floor(($count-1)/2);
        if($count % 2) {
            $median = $arr[$middleval];
        }
        else {
            $low = $arr[$middleval];
            $high = $arr[$middleval+1];
            $median = (($low+$high)/2);
        }
        return $median;
    }

    private function getSequence() {
        return array(0, 1, 2, 3, 5, 8, 13, 20, 40, 100);
    }

}