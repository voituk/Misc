var http = require('http'),
	Url = require('url'),
	MySQLClient = require('mysql').Client

function UrlShortener() {
	this.VERSION = "0.1"
	this.ip = "127.0.0.1"
	this.port = 9090;
	
	this.database = {
		host: '192.168.1.50',
		user: 'root',
		pass: '',
		name: 'urlshort'
	}
	
}

UrlShortener.prototype = {
	
	chars: '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_',
	
	url2Id: function(url) {
		var id = 0,
			chars = this.chars,
			base = chars.length,
			cbase = 1,
			ind = -1;
			
		for (var i=url.length-1; i>=0; i-- ) {
			ind = chars.indexOf(url[i])
			if (ind < 0)
				throw 'Unknown char in URL';
			id += ind * cbase;
			cbase *= base;
		}
		return id
	},
	
	
	id2Url: function (id) {
	},
	
	getConnection: function() {
		db = new MySQLClient()
		db.host = this.database.host || 'localhost'
		db.port = this.database.port || 3306
		db.user = this.database.user
		db.password = this.database.pass
		db.database = this.database.name
		db.connect()
		db.query('SET NAMES utf8')
		return db;
	},

	debug: function(obj) {
		for (var i in obj)
			console.log(i + " = " + obj[i].toString())
	},

	/** 
	* Short URL received - perform redirect
	*/
	doGet: function(request, response) {
		var url = Url.parse(request.url).pathname.substring(1)
		var id = this.url2Id(url)
		
		this.db.query('SELECT `url` FROM `urls` WHERE `id`=?', [id], function(err, res, fields) {
			if (err) {
				response.writeHead(500, 'Internal server error')
				response.end()
				return
			}
			
			if (res.length == 0) {
				response.writeHead(404, 'Not found')
				response.end();
				return
			}
			
			response.writeHead(301, {
				'Location': res[0].url
			})
			response.end();
		})
		//res.end((new Date()).toString());
	},
	
	
	/**
	* Create new URL
	*/
	doPost: function(request, response) {
		var reqUrl = Url.parse(request.url, true)
		response.end();
	},

	
	/**
	* Start HTTP server
	*/
	start: function() {
		var me = this
		this.db = this.getConnection();
		//TODO: close connection on exit
		
		http.createServer(function (req, res) {

			if (req.method == "GET")
				return me.doGet(req, res)

			if (req.method == "POST")
				return me.doPost(req, res)

		}).listen(this.port, this.ip);
		console.log('Server running at http://'+this.ip+':'+this.port+'/')
	}
}

new UrlShortener().start()

//new UrlShortener().url2Id('Hs_2k') // 281549125

