// extend String with toQueryParams - http://prototypejs.org/api/string/toQueryParams
/** 
* Decode URL and return object
* First attempt to support URLs like
*	- "a=10&b=20&c%5B%5D=10&c%5B%5D=20"  
*		(aka a=10&b=20&c[]=10&c[]=20)
*		and
*	- "a=10&b=20&c%5Bhello%5D=10&c%5Bhello2%5D=20"	
*		(aka "a=10&b=20&c[hello]=10&c[hello2]=20")
*/
var _p = String.prototype
if (typeof _p.toQueryParams == 'undefined' )
	_p.toQueryParams = function(separator) {
		separator = separator || '&'
		var obj = {}
		if (this.length == 0)
			return obj
		var c = this.substr(0,1)
		var s = c=='?' || c=='#'  ? this.substr(1) : this; 
		 
		var a = s.split(separator)
		for (var i=0; i<a.length; i++) {
			var p = a[i].indexOf('=')
			if (p < 0) {
				obj[a[i]] = ''
				continue
			}
			var k = decodeURIComponent(a[i].substr(0,p)),
				v = decodeURIComponent(a[i].substr(p+1))
			
			var bps = k.indexOf('[')
			if (bps < 0) {
				obj[k] = v
				continue;
			} 
			
			var bpe = k.substr(bps+1).indexOf(']')
			if (bpe < 0) {
				obj[k] = v
				continue;
			}
			
			var bpv = k.substr(bps+1, bps+bpe-1)
			var k = k.substr(0,bps)
			if (bpv.length <= 0) {
				if (typeof(obj[k]) != 'object') obj[k] = []
				obj[k].push(v)
			} else {
				if (typeof(obj[k]) != 'object') obj[k] = {}
				obj[k][bpv] = v
			}
		}
		return obj;
	
}


// extend Object with toQueryString - http://prototypejs.org/api/object/toQueryString
var _p = Object.prototype
if (typeof _p.toQueryString == 'undefined')
	_p.toQueryString = function(separator) {
		separator = separator || '&'
		var s = "";
		for (var i in this) {
			var v = this[i]
			if (typeof(v) == 'function')
				continue;
			s += (s.length ? "&" : "") + i + "=" + encodeURIComponent(v)
		}
		return s;
	}

/**
* URL Hash management tool
* Example:
*		Hash.set("a=b&c=d")
*			or 
*		Hash.set({a: "b", c: "d"})
			or
		Hash.get('a') -> "b"
			or 
		Hash.go(Hash.set({a:10}))
*/
var Hash = {
	set: function (s) {
		var arg = s.toQueryParams()
		var cur = location.hash.toQueryParams()
		for (var i in arg)
			cur[i] = arg[i]
		return cur.toQueryString();
	},
	
	remove: function (s) {
		var arg = s.toQueryParams();
		var cur = location.hash.toQueryParams()
		var res = {}
		for (var i in cur) {
			if (arg[i] != undefined)
				continue;
			res[i] = cur[i]
		}
		return res.toQueryString()
	},
	
	get: function(key) {
		if (typeof key == 'undefined')
			return location.hash.toQueryParams()
		return location.hash.toQueryParams()[key]
	},
	
	go: function(s) {
		location.hash = s.substr(0,1)=='#' ? s : '#'+s
		return false
	} 

}
