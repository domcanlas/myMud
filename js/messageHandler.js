/*
 * This command is the message receiver
 * It handles the messages sent by the server
 */
var messageHandler = {
		"getUser" : getUser,
		"putRoom" : createWorldAndPut,
		"move": doMove,
		"serverMessage": appendServerMessage
};

/*
 * Puts the overlay on the page to prompt user name
 */
function getUser(data) {
	$("#usernameBox").show();
	$(".overlay").show();
}

/*
 * Creates the world map on the page
 */
function createWorldAndPut(data) {
	if (data.status == "OK") {
		var worldSize = data.worldSize;
		var xPos = data.xPos;
		var yPos = data.yPos;
		//clear the username
		$("#username").val("");
		$("#usernameBox").hide();
		$(".overlay").hide();
		createWorldGraphic(worldSize);
		putUserMarker(xPos, yPos);
		addChatMessage(data.chatMessage);
	}
	else {
		$("#username").val("");
		alert(data.message);
	}
}

/*
 * Moves the user in the world map
 */
function doMove(data) {
	var status = data.status;
	if (status == "OK") {
		var xPos = data.xPos;
		var yPos = data.yPos;
		moveUserMarker(xPos,yPos);
		addChatMessage(data.chatMessage);
	}
	else {
		alert(data.message);
	}
}

/*
 * Puts messages on the message log
 */
function appendServerMessage(data) {
	addChatMessage(data.chatMessage);
}

/*
 * Helper functions
*/

function createWorldGraphic(size) {
	var table = $("<table class='map'></table>");
	for (var i = 0; i < size; i++) {
		var tr = $("<tr></tr>");
		for (var j = 0; j < size; j++) {
			var td = $("<td data-row='"+i+"' data-col='"+j+"'></td>");
			tr.append(td);
		}
		table.append(tr);
	}
	$(".mapContainer").append(table);
}

function putUserMarker(xPos, yPos) {
	var marker = $("<div id='userMarker'></div>");
	$("td[data-row='"+yPos+"'][data-col='"+xPos+"']").append(marker);
}

function addChatMessage(msg) {
	var li = $("<li>"+msg+"</li>");
	$("#messageThread").append(li);
	$(".sizedList").scrollTop($(".sizedList")[0].scrollHeight);
}

function moveUserMarker(xPos, yPos) {
	var mark = $("#userMarker").detach();
	$("td[data-row='"+yPos+"'][data-col='"+xPos+"']").append(mark);
}

