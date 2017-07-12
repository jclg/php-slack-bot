<?php
namespace PhpSlackBot\ActiveMessenger;

use PhpSlackBot\Base;

class Push extends Base {
	/**
	 * @param string $channelOrUsername  Channel name (not Channel ID assigned by Slack) to which message is to be sent.
	 *                                   Usernames can also be passed.
	 *                                   Channel names should have the '#' prefix. Usernames MUST have the '@' prefix
	 * @param string $usernameForMention If a user is to be mentioned in a message.
	 * @param string $message            Actual message to be sent
	 *
	 * @throws \Exception
	 */
	public function sendMessage($channelOrUsername, $usernameForMention, $message) {
		// if the channelOrUsername is set to null, then do not send the message
		if(!$channelOrUsername) {
			return;
		}

		// See the first character
		if(strpos($channelOrUsername, '@') === 0) {
			// User's name was requested
			$userId = $this->getUserIdFromUserName($channelOrUsername);
			$channelId = $this->getImIdFromUserId($userId);
		} elseif(strpos($channelOrUsername, '#') === 0) {
			// Channel requested
			$channelId = $this->getChannelIdFromChannelName($channelOrUsername);
		} else {
			// Neither user not channel requested
			// NOTE: We assume it to be a channel name
			$channelId = $this->getChannelIdFromChannelName($channelOrUsername);
		}

		$usernameToSend = $this->getUserIdFromUserName($usernameForMention);
		if(!$usernameToSend) {
			$usernameToSend = null;
		}

		if ($channelId) {
			$this->send($channelId, $usernameToSend, $message);
		} else {
			throw new \Exception('Cannot resolve channel ID to to send the message');
		}
    }

	/**
	 * This method is defined here only to satisfy the requirements for extending an abstract class
	 */
    protected function configure() {}

	/**
	 * This method is defined here only to satisfy the requirements for extending an abstract class
	 */
    public function execute($message, $context) {}
}
