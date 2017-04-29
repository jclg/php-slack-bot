<?php
namespace PhpSlackBot;

class Bot {

    private $params = array();
    private $context = array();
    private $wsUrl;
    private $commands = array();
    private $webhooks = array();
    private $webserverPort = null;
    private $webserverAuthentificationToken = null;
    private $catchAllCommands = array();
    private $pushNotifiers = array();
    private $activeMessenger = null;

    public function setToken($token) {
        $this->params = array('token' => $token);
    }

    public function loadCommand($command) {
        if ($command instanceof Command\BaseCommand) {
            $this->commands[$command->getName()] = $command;
        }
        else {
            throw new \Exception('Command must implement PhpSlackBot\Command\BaseCommand');
        }
    }

    public function loadWebhook($webhook) {
        if ($webhook instanceof Webhook\BaseWebhook) {
            $this->webhooks[$webhook->getName()] = $webhook;
        }
        else {
            throw new \Exception('Webhook must implement PhpSlackBot\Webhook\BaseWebhook');
        }
    }

    public function loadCatchAllCommand($command) {
        if ($command instanceof Command\BaseCommand) {
            $this->catchAllCommands[] = $command;
        }
        else {
            throw new \Exception('Command must implement PhpSlackBot\Command\BaseCommand');
        }
    }

    public function enableWebserver($port, $authentificationToken = null) {
        $this->webserverPort = $port;
        $this->authentificationToken = $authentificationToken;
    }

    public function loadPushNotifier($method, $repeatInterval = null) {
    	if(is_callable($method)) {
		    $this->pushNotifiers[] = ['interval' => (int)$repeatInterval, 'method' => $method];
	    } else {
		    throw new \Exception('Closure passed as push notifier is not callable.');
	    }
    }

    public function run() {
        if (!isset($this->params['token'])) {
            throw new \Exception('A token must be set. Please see https://my.slack.com/services/new/bot');
        }
        $this->init();
        $logger = new \Zend\Log\Logger();
        $writer = new \Zend\Log\Writer\Stream("php://output");
        $logger->addWriter($writer);

        $loop = \React\EventLoop\Factory::create();
        $client = new \Devristo\Phpws\Client\WebSocket($this->wsUrl, $loop, $logger);

        $client->on("request", function($headers) use ($logger){
                $logger->notice("Request object created!");
        });

        $client->on("handshake", function() use ($logger) {
                $logger->notice("Handshake received!");
        });

        $client->on("connect", function() use ($logger, $client){
                $logger->notice("Connected!");
        });

        $client->on("message", function($message) use ($client, $logger){
            $data = $message->getData();
            $logger->notice("Got message: ".$data);
            $data = json_decode($data, true);

            if (count($this->catchAllCommands)) {
              foreach ($this->catchAllCommands as $command) {
                $command->setClient($client);
                $command->setContext($this->context);
                $command->executeCommand($data, $this->context);
              }
            }
            $command = $this->getCommand($data);
            if ($command instanceof Command\BaseCommand) {
                $command->setClient($client);
                if (isset($data['channel'])) {
                    $command->setChannel($data['channel']);
                }
                if (isset($data['user'])) {
                    $command->setUser($data['user']);
                }
                $command->setContext($this->context);
                $command->executeCommand($data, $this->context);
            }
        });

        /* Webserver */
        if (null !== $this->webserverPort) {
            $logger->notice("Listening on port ".$this->webserverPort);
            $socket = new \React\Socket\Server($loop);
            $http = new \React\Http\Server($socket);
            $http->on('request', function ($request, $response) use ($client) {
                $request->on('data', function($data) use ($client, $request, $response) {
                    parse_str($data, $post);
                    if ($this->authentificationToken === null || ($this->authentificationToken !== null &&
                                                                  isset($post['auth']) &&
                                                                  $post['auth'] === $this->authentificationToken)) {
                        if (isset($post['name']) && is_string($post['name']) && isset($this->webhooks[$post['name']])) {
                            $hook = $this->webhooks[$post['name']];
                            $hook->setClient($client);
                            $hook->setContext($this->context);
                            $hook->executeWebhook(json_decode($post['payload'], true), $this->context);
                            $response->writeHead(200, array('Content-Type' => 'text/plain'));
                            $response->end("Ok\n");
                        }
                        else {
                            $response->writeHead(404, array('Content-Type' => 'text/plain'));
                            $response->end("No webhook found\n");
                        }
                    }
                    else {
                        $response->writeHead(403, array('Content-Type' => 'text/plain'));
                        $response->end("");
                    }
                });
            });
            $socket->listen($this->webserverPort);
        }

        /* Notifiers */

        if(!$this->activeMessenger) {
        	$this->activeMessenger = new ActiveMessenger\Push();
        	$this->activeMessenger->setContext($this->context);
        	$this->activeMessenger->setClient($client);
        }
	    foreach ($this->pushNotifiers as $notifierArray) {
	    	if($notifierArray['interval'] != 0) {
	    		$loop->addPeriodicTimer($notifierArray['interval'], function () use ($notifierArray) {
	    			if($this->activeMessenger instanceof ActiveMessenger\Push) {
					    $resultArray = call_user_func($notifierArray['method']);
					    $this->activeMessenger->sendMessage($resultArray['channel'], $resultArray['username'], $resultArray['message']);
				    }
			    });
		    } else {
			    $loop->addTimer(10, function () use ($notifierArray) {
				    if($this->activeMessenger instanceof ActiveMessenger\Push) {
					    $resultArray = call_user_func($notifierArray['method']);
					    $this->activeMessenger->sendMessage($resultArray['channel'], $resultArray['username'], $resultArray['message']);
				    }
			    });
		    }
        }

	    $client->open();

        $loop->run();
    }

    private function init() {
        $url = 'https://slack.com/api/rtm.start';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($this->params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($ch);
        if ($body === false) {
            throw new \Exception('Error when requesting '.$url.' '.curl_error($ch));
        }
        curl_close($ch);
        $response = json_decode($body, true);
        if (is_null($response)) {
            throw new \Exception('Error when decoding body ('.$body.').');
        }
        $this->context = $response;
        if (isset($response['error'])) {
            throw new \Exception($response['error']);
        }
        $this->wsUrl = $response['url'];
    }

    public function loadInternalCommands() {
        $commands = array(
                          new \PhpSlackBot\Command\PingPongCommand,
                          new \PhpSlackBot\Command\CountCommand,
                          new \PhpSlackBot\Command\DateCommand,
                          new \PhpSlackBot\Command\PokerPlanningCommand,
                          );
        foreach ($commands as $command) {
            if (!isset($this->commands[$command->getName()])) {
                $this->commands[$command->getName()] = $command;
            }
        }
    }

    public function loadInternalWebhooks() {
        $webhooks = array(
                          new \PhpSlackBot\Webhook\OutputWebhook,
                          );
        foreach ($webhooks as $webhook) {
            if (!isset($this->webhooks[$webhook->getName()])) {
                $this->webhooks[$webhook->getName()] = $webhook;
            }
        }
    }

    private function getCommand($data) {
        if (empty($data['text'])) {
            return null;
        }

        // Check if bot is mentioned
        $botMention = false;
        if (strpos($data['text'], '<@'.$this->context['self']['id'].'>') !== false) {
            $botMention = true;
        }

        $find = '/^'.preg_quote('<@'.$this->context['self']['id'].'>', '/').'[ ]*/';
        $text = preg_replace($find, '', $data['text']);

        if (empty($text)) {
            return null;
        }

        foreach ($this->commands as $commandName => $availableCommand) {
            $find = '/^'.preg_quote($commandName).'/';
            if (preg_match($find, $text) &&
                (!$availableCommand->getMentionOnly() || $botMention)) {
                return $availableCommand;
            }
        }

        return null;
    }

}
