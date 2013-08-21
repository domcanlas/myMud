<?php
/*
 * A room class
* An object that holds a set of users
*/

namespace MyApp;
use Ratchet\ConnectionInterface;

class World {
	private $size;
	//A mapping of resourceId and name
	private $connPersonNameMapping = array();
	//An array of mudrooms
	private $world = array();
	
	public function __construct($size) {
		$this->size = $size;
		require_once dirname(__FILE__).'/MUDRoom.class.php';
		$container = array();
		for($i = 0; $i < $size; $i++) {
			for($j = 0; $j < $size; $j++) {
				//we store the connections in this storage
				//each storage can have multiple connections
				$randNum = rand(0, 3);
				//randomize roomtype 1/4 chance of being solid
				$roomType = ($randNum == 3) ? "solid" : "transparent";
				$container[$i][$j] = new MUDRoom($roomType);
			}
		}
		$this->world = $container;
	}
	
	public function getSize() {
		return $this->size;
	}
	
	public function getWorld() {
		return $this->world;
	}
	
	public function getConnNameMapping() {
		return $this->connPersonNameMapping();
	}
	
	/*
	 * Match a connection id to a name
	 * here key is the resourceId of connection
	 */
	public function addPersonName($key, $value) {
		$this->connPersonNameMapping[$key] = $value;
	}
	
	/*
	 * here key is the resourceId of connection 
	 */
	public function getPersonName($key) {
		return $this->connPersonNameMapping[$key];
	}
	
	/*
	 * Remove a mapping of connection id and name
	 */
	public function removePersonName($key) {
		$this->connPersonNameMapping[$key] = null;
	}
	
	/*
	 * Return a MUDRoom with the given coordinates
	 */
	public function getRoom($yIdx, $xIdx) {
		return $this->world[$yIdx][$xIdx];
	}
	
	/*
	 * See if name exists in the mapping
	 */
	public function isNameExists($name) {
		foreach($this->connPersonNameMapping as $key=>$value) {
			if (strtolower($value) == strtolower($name) ) {
				return true;
			}
		}
		return false;
	}
	
	public function getPersonConnectionId($name) {
		foreach($this->connPersonNameMapping as $key=>$value) {
			if (strtolower($value) == strtolower($name) ) {
				return $key;
			}
		}
		return false;
	}
	
	public function removeUserFromWorld(ConnectionInterface $conn) {
		//remove from the name mapping
		unset($this->connPersonNameMapping[$conn->resourceId]);
		//remove from the room
		foreach($this->world as $rowRoom) {
			foreach($rowRoom as $room) {
				if ($room->getPersonGivenConnId($conn->resourceId)) {
					$room->removeByConnection($conn);
					break;
				}
			}
		}
	}
	
}


?>