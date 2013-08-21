<?php
/*
* A room class
* An object that holds a set of users
*/

namespace MyApp;
use Ratchet\ConnectionInterface;

class MUDRoom {
	
	private $contents;
	private $type; //String : either "solid" or "transparent"
	
	public function __construct($type) {
		$this->type = $type;
		$this->contents = array(); 
	} 
	
	public function getType() {
		return $this->type;
	} 
	
	public function getContents() {
		return $this->contents;
	}
	
	/*
	 * Adds a person object to this room
	 */
	public function add(Person &$person) {
		$this->contents[] = $person;
	}
	
	/*
	 * Returns the Person object with the connection resource id given by $connection
	 */
	public function removeByConnection(ConnectionInterface $conn) {
		$rId = $conn->resourceId;
		$connIdx = null;
		for ($i = 0; $i < count($this->contents); $i++) {
			$item = $this->contents[$i];
			$itemConn = $item->getConnection();
			$itemId = $itemConn->resourceId;
			if ($rId == $itemId) {
				$connIdx = $i;
			}
		}
		$arrReturn = array_splice($this->contents, $connIdx, 1);
		return $arrReturn[0];
	}
	
	/*
	 * Get the names of the people in here
	 */
	public function getPeopleNames() {
		if (count($this->contents) == 0) {
			return false;
		}
		else {
			$names = "";
			foreach($this->contents as $person) {
				$names .= $person->getName().", ";
			}
			return substr(trim($names), 0, -1);
		}
	}	
	
	/*
	 * Get the connections of people in here
	 */
	public function getPeopleConnection() {
		if (count($this->contents) == 0) {
			return false;
		}
		else {
			$conns = array();
			foreach($this->contents as $person) {
				$conns[] = $person->getConnection();
			}
			return $conns;
		}
	}
	
	/*
	 * Get the person object given the name
	 */
	public function getPersonGivenConnId($connId) {
		if (count($this->contents) == 0) {
			return false;
		}
		else {
			foreach($this->contents as $person) {
				if ($connId == $person->getConnectionId()) {
					return $person;
				}
			}
		}
		return false;
	}
	
}



