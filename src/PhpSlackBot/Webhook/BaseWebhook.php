<?php
namespace PhpSlackBot\Webhook;

abstract class BaseWebhook {
    private $name;
    private $client;
    private $user;
    private $context;
    abstract protected function configure();
    abstract protected function execute($payload);

    public function executeWebhook($payload) {
        return $this->execute($payload);
    }

    public function getName() {
        $this->configure();
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setClient($client) {
        $this->client = $client;
    }

    public function getClient() {
        return $this->client;
    }

    public function setChannel($channel) {
        $this->channel = $channel;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getCurrentUser() {
        return $this->user;
    }

    public function setContext($context) {
        $this->context = $context;
    }

    public function getCurrentContext() {
        return $this->context;
    }

    public function getCurrentChannel() {
        return $this->channel;
    }

    protected function send($channel, $username, $message) {
        $response = array(
                          'id' => time(),
                          'type' => 'message',
                          'channel' => $this->channel,
                          'text' => (!is_null($username) ? '<@'.$username.'> ' : '').$message
                          );
        $this->client->send(json_encode($response));
    }

    protected function getUsernameFromUserId($userId) {
        $username = 'unknown';
        foreach ($this->context['users'] as $user) {
            if ($user['id'] == $userId) {
                $username = $user['name'];
            }
        }
        return $username;
    }

    protected function getChannelIdFromChannelName($channelName) {
        $channelName = str_replace('#', '', $channelName);
        foreach ($this->context['channels'] as $channel) {
            if ($channel['name'] == $channelName) {
                return $channel['id'];
            }
        }
        return false;
    }
}