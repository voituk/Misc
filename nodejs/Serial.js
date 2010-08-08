var fs = require("fs"),
		sys = require("sys")

fs.open("/dev/tty.HUAWEIMobile-Modem", 'r+', 0666,  function(err, fd) {
	if (err)
		return console.log("Error:" + err.message)

	fs.write(fd, "AT+COPS=?\r", null, "ascii", function(err, written) {
		console.log("Written: "+written + ", err="+err)
		fs.read(fd, 1024, null, "ascii", function(err, str, bytesRead) {
			console.log("Read: err=" + err + ", bytes=" + bytesRead + ", str=" + str)
			fs.closeSync(fd);
			console.log("Closed");
		})
	})
})


/*var fs = require("fs"),
		sys = require("sys")

fs.open("/dev/tty.HUAWEIMobile-Modem", 'r+', 0666,  function(err, fd) {
	if (err)
		return console.log("Error:" + err.message)

	var written = fs.writeSync(fd, "ati5\r", null, "ascii")
	console.log("Written: "+written + ", err="+err)
	setTimeout(function(fd) {
		fs.read(fd, 1024, null, "ascii", function(err, str, bytesRead) {
			console.log("Read: err=" + err + ", bytes=" + bytesRead + ", str=" + str)
			fs.closeSync(fd);
			console.log("Closed");
		})
	}, 100, fd)
} )*/
