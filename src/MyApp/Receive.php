<?php
/*
 * Parsing and sending messages to connections are all handled here
 * 
 */
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;


function handleMessage(ConnectionInterface $conn, $msg, &$world) {
	try {
		$msgObj = json_decode($msg, true);
		switch ($msgObj['cmd']) {
			case "register":
				 doRegister($conn, $msgObj['data'], $world);
			break;
			case "move":
				doMove($conn, $msgObj['data'], $world);
			break;
			case "say":
				doSay($conn, $msgObj['data'], $world);
			break;
			case "yell":
				doYell($conn, $msgObj['data'], $world);
			break;
			case "tell":
				doTell($conn, $msgObj['data'], $world);
			break;
		}
	}
	catch(Exception $e) {
		echo $e->getMessage();
	}
}

function doSay(ConnectionInterface $conn, $data, &$world) {
	$msg = $data['message'];
	$curX = $data['curX'];
	$curY = $data['curY'];
	$sendData = array("cmd"=>"serverMessage", "data"=>array());
	//get the room 
	$room = $world->getRoom($curY, $curX);
	$people = $room->getContents();
	//first get name of this connection
	$connectedPerson = null;
	$others = array();
	foreach($people as $person) {
		$resId = $person->getConnectionId();
		if ($resId == $conn->resourceId) {
			$connectedPerson = $person;			
		} 
		else {
			$others[] = $person;
		}
	}
	//send 'you said' to connection
	$connectedPersonConn = $connectedPerson->getConnection();
	$sendData["data"]["chatMessage"] = "You said: $msg";
	$connectedPersonConn->send(json_encode($sendData));	
	//iterate over others
	$connectedPersonName = $connectedPerson->getName();
	$sendData["data"]["chatMessage"] = "$connectedPersonName said: $msg";
	foreach($others as $other) {
		$otherConn = $other->getConnection();
		$otherConn->send(json_encode($sendData));
	}
}

function doYell(ConnectionInterface $conn, $data, &$world) {
	$msg = $data["message"];
	$worldStructure = $world->getWorld();
	$connPersonName = $world->getPersonName($conn->resourceId);
	$sendData = array("cmd"=>"serverMessage", "data"=>array());
	foreach($worldStructure as $rowRooms) {
		foreach($rowRooms as $room) {
			$roomConns = $room->getPeopleConnection();
			if ($roomConns) {
				foreach($roomConns as $roomConn) {
					if ($roomConn->resourceId == $conn->resourceId) {
						$sendData["data"]["chatMessage"] = "You yelled: $msg";
						$conn->send(json_encode($sendData));
					}
					else {
						//send the yell to everyone
						$sendData["data"]["chatMessage"] = "$connPersonName yelled: $msg";
						$roomConn->send(json_encode($sendData));
					}
				}
			} 
		}
	}
}

function doTell(ConnectionInterface $conn, $data, &$world) {
	$receiver = $data['receiver'];
	$message = $data['message'];
	$sendData = array("cmd"=>"serverMessage", "data"=>array());
	$connName = $world->getPersonName($conn->resourceId);
	if ($world->isNameExists($receiver)) {
		//send message to this person
		$receiverConnId = $world->getPersonConnectionId($receiver);
		$worldStructure = $world->getWorld();
		foreach($worldStructure as $rowRooms) {
			foreach($rowRooms as $room) {
				$receiverObj = $room->getPersonGivenConnId($receiverConnId);
				if ($receiverObj) {
					//we got a match, send message and break
					//send message to the receiver
					$receiverConn = $receiverObj->getConnection();
					$sendData["data"]["chatMessage"] = "$connName whispers: $message";
					$receiverConn->send(json_encode($sendData));
					//send message to connection
					$sendData["data"]["chatMessage"] = "You whispered to $receiver: $message";
					$conn->send(json_encode($sendData));
				}
			}
		}
	}
	else {
		//This person does not exist
		$sendData["data"]["chatMessage"] = "Person: $receiver, is not in this world.";
		$conn->send(json_encode($sendData));
	}
}

function doRegister(ConnectionInterface $conn, $data, &$world) {
	require_once dirname(__FILE__).'/Person.class.php';
	$name = $data['name'];
	if ($world->isNameExists($name)) {
		$sendData = array("cmd"=>"putRoom", 
				"data"=>array("status"=>"FAIL", "message"=>"The name already exists, please pick a new one."));
		$conn->send(json_encode($sendData));
	}
	else {
		
		$noRoom = true;
		$validX = 0;
		$validY = 0;
		//we check for a transparent room
		$worldSize = $world->getSize();
		while($noRoom) {
			//generate random initial xPos and yPos
			$randX = rand(0, $worldSize-1);
			$randY = rand(0, $worldSize-1);
			//$curRoom = $world[$randY][$randX];
			$curRoom = $world->getRoom($randY, $randX);
			if ($curRoom->getType() == "transparent") {
				$noRoom = false;
				$validX = $randX;
				$validY = $randY;
			}
		}
		
		//create a person class
		$personObj = new Person($name, $conn, $validX, $validY);
		
		//put in room
		$room = $world->getRoom($validY, $validX);
		$room->add($personObj);
		//put the name in the world mapping
		$world->addPersonName($conn->resourceId, $name);
		$pplNames = getPeople($room);
		//send the room location
		$sendData = array("cmd"=>"putRoom", "data"=>array("xPos"=>$validX, "yPos"=>$validY, "worldSize"=>$worldSize, 
				"chatMessage"=> "Welcome $name! The following people are in your room: ".$pplNames, "status"=>"OK"));
		$conn->send(json_encode($sendData));
		//send message to everyone in the room that someone joined
		$roomConns = $room->getPeopleConnection();
		sendGroupMessage($roomConns, "$name has joined this room.");
	}
}

function doMove(ConnectionInterface $conn, $data, &$world) {
	$sendData = array("cmd"=>"move", "data"=>array());
	try {
		$direction = $data['direction'];
		$curX = $data['curX'];
		$curY = $data['curY'];
		//$worldSize = count($world);
		$worldSize = $world->getSize();
		//look for this connection in the appropriate place 
		$room = $world->getRoom($curY, $curX);
		$personObj = $room->removeByConnection($conn);
		//get the new offset
		$newX = intval($curX);
		$newY = intval($curY);
		if ($direction == "north") {
			$newY--;
		}
		else if ($direction == "south") {
			$newY++;
		}
		else if ($direction == "east") {
			$newX++;
		}
		else if ($direction == "west") {
			$newX--;
		}
		else {
			//put back the person
			$room->add($personObj);
			//throw exception
			throw new \Exception("Invalid direction given: ".$direction);
		}
		if (($newX > $worldSize-1) || ($newY > $worldSize-1) || ($newX < 0) || ($newY < 0)) {
			//put back the person
			$room->add($personObj);
			throw new \Exception("You cannot move outside the world.");
		}
		//see if this new room is transparent
		$isValidMove = false;
		//$newRoom = &$world[$newY][$newX];
		$newRoom = $world->getRoom($newY, $newX);
		if ($newRoom->getType() == "transparent") {
			$personName = $personObj->getName();
			//tell ppl in old room someone left
			$oldRoomConns = $room->getPeopleConnection();
			sendGroupMessage($oldRoomConns, $personName." has left the room");
			//tell ppl in new room someone joined
			$newRoomConns = $newRoom->getPeopleConnection();
			sendGroupMessage($newRoomConns, $personName." has joined the room");
			//add the personObj
			$newRoom->add($personObj);
			//send move commmand to UI
			$sendData["data"]["xPos"] = $newX;
			$sendData["data"]["yPos"] = $newY;
			$sendData["data"]["status"] = "OK";
			$pplNames = getPeople($newRoom);
			$sendData["data"]["chatMessage"] = "You moved $direction. The people here are: $pplNames";
		}
		else {
			//put back the person
			$room->add($personObj);
			//throw exception
			throw new \Exception("Room is solid, you cannot go through");
		}
	}
	catch(\Exception $e) {
		//send move error
		$sendData["data"]["status"] = "FAIL";
		$sendData["data"]["message"] = $e->getMessage();
	}
	$conn->send(json_encode($sendData));
}

/*
 * Helper Functions
 */
function sendNameCommand(ConnectionInterface $conn) {
	$message = array("cmd"=>"getUser", "data"=>"");
	$conn->send(json_encode($message));
}

function userDisconnected(ConnectionInterface $conn, $clients, &$world) {
	$connPersonName = $world->getPersonName($conn->resourceId);
	sendGroupMessage($clients, "$connPersonName has disconnected from the world.");
}

function getPeople(MUDRoom $room) {
	$pplNames;
	$people = $room->getPeopleNames();
	if ($people) {
		$pplNames = $people;
	}
	else {
		$pplNames = "None";
	}
	return $pplNames;
}

function sendGroupMessage($conns, $msg) {
	$sendData = array("cmd"=>"serverMessage", "data"=>array("chatMessage"=>$msg));
	if ($conns) {
		foreach($conns as $conn) {
			$conn->send(json_encode($sendData));
		}
	}
}

?>