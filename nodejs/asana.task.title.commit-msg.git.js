#!/usr/bin/env node

/**
* Simple GIT commit-msg hook, that inserts Asana ticket title next to ticket ID.

* For example, after running "git commit" like:
*	git commit -a -m "Commit message with ticket #1217261"
* The commit message will be changed to
*	"Commit message with ticket #1217261(Title of tiket 1217261 in Asana)"
* 
* Configuration: 
*	0. Install node.js if you do not have it installed yet
*	1. Get you Asana API key in your "Account Settings"
*	2. Put this key to git config file: 
*		git config --global "user.asana-key"  "...put-your-key-here..."
*	3. Copy this file into .git/config/commit-msg of your git repo
*	4. Enjoy
*/

var fs    = require('fs'),
	https = require('https'),
	util  = require('util'),
	exec  = require('child_process').exec;

var messageFile = process.argv[2];
if ( !fs.existsSync(messageFile) )
	process.exit(0);


var message = fs.readFileSync(messageFile, "utf8")
var taskId = null;

var match  = message.match(/.*#([0-9]*).*/);
if (match && match[1]) {
	taskId = match[1];
	message = message.replace(/#([0-9]*)/, '{TASKID}');
}

if (taskId) {
	console.log("Found ticket ID #"+taskId);
	detectAsanaKey(function(err, asanaKey){
		https.get(
			{
				"hostname": "app.asana.com",
				"path":     "/api/1.0/tasks/"+taskId,
				"auth":     asanaKey + ":"
			}, 
			function(res) {
				if (res.statusCode != 200) {
					console.log("Ticket #" + taskId + " not found via Asana API")
					return;
				}

				var body = "";
				res.on("data", function(data) { body += data })
					.on("end", function() {
						var json = JSON.parse(body);
						var newmessage = message.replace(/\{TASKID\}/, "#" + taskId + "(" + json.data.name + ")");
						fs.writeFileSync(messageFile, newmessage, "utf8");
					})

			}
		).on("error", function(){
			console.error(e);
		});
	})

} else {
	console.log("No Asana taskId found");
}


function detectAsanaKey(callback) {
	exec('git config "user.asana-key"', function(err, data) {
		if (err) {
			console.error(err);
			return;

		}
		callback.call(null, err, data.trim())
	});
}

