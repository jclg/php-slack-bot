<?php
namespace PhpSlackBot;

class Bot {

    private $params = array();
    private $context = array();
    private $wsUrl;
    private $commands = array();
    private $webserverPort = false;

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

    public function enableWebserver($port) {
        $this->webserverPort = 8080;
    }

    public function run() {
        if (!isset($this->params['token'])) {
            throw new \Exception('A token must be set. Please see https://my.slack.com/services/new/bot');
        }
        $this->loadInternalCommands();
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

            $command = $this->getCommand($data);
            if ($command instanceof Command\BaseCommand) {
                $command->setClient($client);
                $command->setChannel($data['channel']);
                $command->setUser($data['user']);
                $command->setContext($this->context);
                $command->executeCommand($data, $this->context);
            }
        });


        $client->open();

        /* Webserver */
        if ($this->webserverPort !== false) {
            $logger->notice("Listening on port ".$this->webserverPort);
            $socket = new \React\Socket\Server($loop);
            $http = new \React\Http\Server($socket);
            $http->on('request', function ($request, $response) use ($client) {
                $post = $request->getPost();
                if ($post['name'] == 'output') {
                    $hook = new \PhpSlackBot\Webhook\OutputWebhook();
                    $hook->setClient($client);
                    $hook->setContext($this->context);
                    $hook->executeWebhook(json_decode($post['payload'], true));
                }
                $response->writeHead(200, array('Content-Type' => 'text/plain'));
                $response->end("Ok\n");
            });
            $socket->listen(8080);
        }

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

    private function loadInternalCommands() {
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

    private function getCommand($data) {
        if (isset($data['text'])) {
            $argsOffset = 0;
            if (strpos($data['text'], '<@'.$this->context['self']['id'].'>') === 0) {
                $argsOffset = 1;
            }
            $args = array_values(array_filter(explode(' ', $data['text'])));
            if (isset($args[$argsOffset])) {
                foreach ($this->commands as $commandName => $availableCommand) {
                    if ($args[$argsOffset] == $commandName) {
                        return $this->commands[$commandName];
                    }
                }
            }
        }
        return null;
    }

}
