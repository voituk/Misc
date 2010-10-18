var assert = require('assert')

assert.equal("bG9naW46cGFzc3dvcmQ=", 
	new Buffer("login:password", "utf-8").toString("base64")
)

var http = require('http')

var request = http.createClient(80, 'stream.twitter.com').request("GET", "/1/statuses/sample.xml?delimited=1", {
	Host: 'voituk.com',
	Authorization: 'Basic ' + new Buffer('voituk:XXXXXXXXXX').toString('base64') 
})

request.on('response', function(response) {
	console.log(response.statusCode)
	console.log(response.headers)
	
	var buf = new Buffer(1024*32),
		ind = 0;
	response.on('data', function(data) {
		for (var i=0, len=data.length; i<len; i++) {
			buf[ind++] = data[i]
			
			if ( (ind >= 9) && (buf.slice(ind-9, ind).toString() == '</status>') ) {
				console.log(ind + ' ' + buf.length);
				console.log(buf.slice(0,ind).toString())
				ind = 0;
				process.exit()
			}
		}
	})
})

request.end()
