var fs = require('fs')

function Modem(path) {
	this.path = path
	this.fd = fs.openSync(path, 'r+', 0666)
}
Modem.prototype.send = function(cmd, callback) {
	require('fs').writeSync(this.fd, cmd+"\r", null, "ascii")

}

var x = new Modem("/dev/tty.HUAWEIMobile-Modem")
x.send("ati5", function(err, str) {
})

console.log(x)

/*var fd = fs.openSync("/dev/tty.HUAWEIMobile-Modem", 'r+', 0666)

//fs.writeSync(fd, "ati5\r", null, "ascii")
fs.writeSync(fd, "AT+CSQ\r", null, "ascii")

setTimeout(function(fd) {
	fs.read(fd, 1024, null, "ascii", function(err, str, bytesRead) {
		if (err)
			return console.log("Error: " + err.message)
		console.log(str)
		fs.closeSync(fd);
	})
}, 1000, fd)*/

