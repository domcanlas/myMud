<?php
/*
 * Person class
 * An object that holds the attributes of each user connection
 */
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Person {
	private $name;
	private $conn;
	private $xPos;
	private $yPos;
	
	public function __construct($name, $conn, $xPos, $yPos) {
		$this->name = $name;
		$this->conn = $conn;
		$this->xPos = $xPos;
		$this->yPos = $yPos;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getYPos() {
		return $this->yPos;
	}
	
	public function getXPos() {
		return $this->xPos;
	}
	
	public function getConnection() {
		return $this->conn;
	}
	
	public function getConnectionId() {
		return $this->conn->resourceId;
	}
	
	public function move($newX, $newY) {
		$this->xPos = $newX;
		$this->yPos = $newY;
	}
	

}
