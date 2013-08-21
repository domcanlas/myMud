
var conn;
var commandList = ["move", "tell", "say", "yell"];

$(function() {
	try {
		conn = new WebSocket('ws://localhost:8080');
	
		conn.onopen = function(e) {
		    console.log("Connection established!");
		};
	
		conn.onmessage = function(e) {
		    //console.log(e);
		    handleMessage(e.data);
		};
		
		conn.onclose = function(e) {
			alert("The server went down");
		};
	}
	catch(e) {
		alert(e);
	}
	//Events
	//the commandLine event
	
	$("#commandLine").keypress(function(e) {
		if (e.which == 13) {
			var query = $(this).val();
			parseCommand(query);
			$(this).val("");
		}
	});
	
	//The user name event
	$("#enterUser").click(function() {
		var name = $("#username").val();
		if (name.trim() != "") {
			messageSender.register(name);
		}
	});
});


function parseCommand(query) {
	var queryArr = query.split(/\s/);
	var cmd = queryArr[0];
	//remove the command
	queryArr.splice(0,1);
	var data = queryArr.join(" ");
	if (commandExists(cmd)) {
		messageSender[cmd](data);
	}
	else {
		alert("Invalid command: "+cmd);
	}
}

function handleMessage(msg) {
	try {
		var data = JSON.parse(msg);
		console.log(data);
		messageHandler[data.cmd](data.data);
	}
	catch(e) {
		alert(e);
	}
}

function commandExists(cmd) {
	if (commandList.indexOf(cmd) >= 0 ) {
		return true;
	}
	else {
		return false;
	}
}