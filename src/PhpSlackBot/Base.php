<?php
namespace PhpSlackBot;

abstract class Base {
    private $name;
    private $client;
    private $user;
    private $context;
    private $mentionOnly = false;
    private $channel;
    abstract protected function configure();
    abstract protected function execute($message, $context);

    public function getName() {
        $this->configure();
        return $this->name;
    }

    public function getClient() {
        return $this->client;
    }

    public function getMentionOnly() {
        return $this->mentionOnly;
    }

    public function setMentionOnly($mentionOnly) {
        $this->mentionOnly = $mentionOnly;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setClient($client) {
        $this->client = $client;
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

    protected function send($channel, $username, $message, $parent_thread = null) {
        $response = array(
                          'id' => time(),
                          'type' => 'message',
                          'channel' => $channel,
                          'text' => (!is_null($username) ? '<@'.$username.'> ' : '').$message
                          );
        if ($parent_thread) {
            $response['thread_ts'] = $parent_thread;
        }
        $this->client->send(json_encode($response));
    }

    protected function getUserNameFromUserId($userId) {
        $username = 'unknown';
        foreach ($this->context['users'] as $user) {
            if ($user['id'] == $userId) {
                $username = $user['name'];
            }
        }
        return $username;
    }

	protected function getUserIdFromUserName($userName) {
		$userId = '';
		$userName = str_replace('@', '', $userName);
		foreach ($this->context['users'] as $user) {
			if ($user['name'] == $userName) {
				$userId = $user['id'];
			}
		}
		return $userId;
	}

    protected function getChannelIdFromChannelName($channelName) {
        $channelName = str_replace('#', '', $channelName);
        foreach ($this->context['channels'] as $channel) {
            if ($channel['name'] == $channelName) {
                return $channel['id'];
            }
        }
        foreach ($this->context['groups'] as $group) {
            if ($group['name'] == $channelName) {
                return $group['id'];
            }
        }
        return false;
    }

    protected function getChannelNameFromChannelId($channelId) {
        foreach ($this->context['channels'] as $channel) {
            if ($channel['id'] == $channelId) {
                return $channel['name'];
            }
        }
        foreach ($this->context['groups'] as $group) {
            if ($group['id'] == $channelId) {
                return $group['name'];
            }
        }
        return false;
    }

    protected function getImIdFromUserId($userId) {
        foreach ($this->context['ims'] as $im) {
            if ($im['user'] == $userId) {
                return $im['id'];
            }
        }
        return false;
    }

}
