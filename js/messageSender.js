/*
 * This file holds the message sender to connection
 * It sends messages to server based on user input
 */
var messageSender = {
		"register" : register,
		"move": sendMove,
		"say": sendSayMessage,
		"yell": sendYellMessage,
		"tell": sendTellMessage
};

/*
 * Send the name of this user
 */
function register(name) {
	var msg = {cmd: "register", data: {name: name}};
	conn.send(JSON.stringify(msg));
}

/*
 * Send a move command
 */
function sendMove(data) {
	//take the first word of data
	var dataArr = data.split(/\s/);
	var direction = dataArr[0];
	if (direction == "north" || direction == "south" || 
			direction == "east" || direction == "west") {
		var user = $("#userMarker");
		var curXPos = user.parent("td").attr("data-col");
		var curYPos = user.parent("td").attr("data-row");
		var msg = {cmd: "move", data: {direction: direction, curX: curXPos, curY: curYPos}};
		conn.send(JSON.stringify(msg));
	}
	else {
		alert("Invalid direction");
	}
}

/*
 * Send a say command
 */
function sendSayMessage(data) {
	var user = $("#userMarker");
	var curXPos = user.parent("td").attr("data-col");
	var curYPos = user.parent("td").attr("data-row");
	var message = data;
	var sendObj = {cmd: "say", data: {message: message, curX: curXPos, curY: curYPos}};
	conn.send(JSON.stringify(sendObj));
}

/*
 * Send a yell command
 */
function sendYellMessage(data) {
	var message = data;
	var sendObj = {cmd: "yell", data: {message: message}};
	conn.send(JSON.stringify(sendObj));
}

/*
 * Send a tell command
 */
function sendTellMessage(data) {
	var dataArr = data.split(/\s/);
	var personName = dataArr[0];
	dataArr.splice(0,1);
	var msg = dataArr.join(" ");
	var sendObj = {cmd: "tell", data: {receiver: personName, message: msg}};
	conn.send(JSON.stringify(sendObj));
}

