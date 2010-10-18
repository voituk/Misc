var app = require('express').createServer()

app.get('/', function(req, res) {
	res.send(new Date().toString())
})

app.listen(3000)
