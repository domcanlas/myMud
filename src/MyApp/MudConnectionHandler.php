<?php
/*
 * The file that handles the websocket connections
 */
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
require_once dirname(__FILE__).'/Receive.php';
require_once dirname(__FILE__).'/World.class.php';

class MudConnectionHandler implements MessageComponentInterface {
    public $world;
    public $clients;

    public function __construct() {
    	$this->world = new World(4);
        $this->clients = new \SplObjectStorage; //storage of all connections
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        if (!$this->clients->contains($conn)) {
	        $this->clients->attach($conn);
			sendNameCommand($conn);
        }
		//send name command
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
    	//call handler
    	handleMessage($from, $msg, $this->world);
    }

    public function onClose(ConnectionInterface $conn) {
    	//tell world user disconnected
    	userDisconnected($conn, $this->clients, $this->world);
        //detach it from the world as well
		$this->world->removeUserFromWorld($conn);
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
		
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
    
}

?>