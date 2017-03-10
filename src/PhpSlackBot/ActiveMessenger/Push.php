<?php
namespace PhpSlackBot\ActiveMessenger;

use PhpSlackBot\Base;

class Push extends Base {

	public function sendMessage($channel, $username, $message) {
//		$channelId = $this->getChannelIdFromChannelName($channel);
//		if(!$channelId) {
//			// Did not find the channel in list of channels. Try Direct messages

		// See the first character
		if(strpos($channel, '@') === 0) {
			// User's name was requested
			$userId = $this->getUserIdFromUserName($channel);
			$channelId = $this->getImIdFromUserId($userId);
		} elseif(strpos($channel, '#') === 0) {
			// Channel requested
			$channelId = $this->getChannelIdFromChannelName($channel);
		} else {
			// Neither user not channel requested
			// NOTE: We assume it to be a channel name
			$channelId = $this->getChannelIdFromChannelName($channel);
		}


			$usernameToSend = $this->getUserIdFromUserName($username);
			if(!$usernameToSend)
				$usernameToSend = null;
//		}
		if($channelId)
        $this->send($channelId, $usernameToSend, $message);
		else
			echo "\n\n SAKINAKA \n\n";
    }

    protected function configure() {
	    // TODO: Implement configure() method.
    }

    public function execute($message, $context) {
	    // TODO: Implement execute() method.
    }
}