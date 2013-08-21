<?php
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use MyApp\MudConnectionHandler;

require dirname(__DIR__) . '/vendor/autoload.php';

 $server = IoServer::factory(
        new WsServer(
            new MudConnectionHandler()
        )
      , 8080
    );

$server->run();

?>