<?php
	class HttpUtils {
		
		const DEFAULT_TIMEOUT = 300;
		
		public static function doGet($url, $options=array()) {
			return HttpUtils::_doGetHead($url, 'GET', $options);
		}
		
		public static function doHead($url, $options=array()) {
			$r = HttpUtils::_doGetHead($url, 'HEAD', $options);
			if (empty($options['follow.redirects']))
				return $r;
			
			$maxRedirCount = isset($options['follow.maxcount']) ? $options['follow.maxcount'] : 10;
			
			$redirCount = 0;
			while ( ($r->httpCode[0]=='3') && ($redirCount<$maxRedirCount) ) {
				$location = null;
				foreach($r->headers as $k=>$v) {
					if (strcasecmp($k, 'location') === 0) {
						$location = $v;
						break;
					}
				}
				if (!empty($location))
					$r = HttpUtils::_doGetHead($location, 'HEAD', $options);
				
			}
			return $r;
		}

		/** 
		* Options supported:
		* 	- follow.redirects  - Follow HTTP redirects code
		*	- follow.trace      - Save full redirects history
		*	- follow.maxcount	 - Max number of redirects
		*	- headers.lowercase - Convert HTTP headers into lower-case strings
		*/
		protected static function _doGetHead($url, $method, $options=array()) {
			HttpUtils::update_max_execution_time();
			
			$method = strtoupper($method);
			
			$arr = parse_url($url);
			$arr['port'] = isset($arr['port'])?$arr['port']:80;
			$errno = $errstr = null;
		  
		  $R = new StdClass();
		  $R->isError = false;
		  
		  if ($method != 'GET' && $method!='HEAD') {
			  $R->isError = "Invalid method $method. Only GET & HEAD allowed";
			  return $R;
		  }
			 
		  $f = @fsockopen($arr['host'], $arr['port'], $errno, $errstr, 600);
		  if (!$f) {
			$R->isError = " Connecting to {$arr['host']}:{$arr['port']} failed: $errstr, $errno";
			return $R;
		  }
		  stream_set_blocking($f, 0);
	
		  $req = "$method {$arr['path']}".(empty($arr['query'])?'':'?'.$arr['query'])." HTTP/1.0\r\n";
		  $req.= "Host: {$arr['host']}\r\n";
		  $req.= "Connection: Close\r\n";
		  if (!empty($arr['user']))
			  $req.= "Authorization: Basic ".base64_encode("{$arr['user']}:{$arr['pass']}")."\r\n";
		  
		  $req.= "\r\n";
		  
		  fputs($f, $req);
	
		  $res = '';
		  while (!feof($f)) {
			$res .= fgets($f, 1024);
		  }
		  fclose($f);
		  
		  // Parse result
		  return HttpUtils::parseHttpResponse($res, $options);
		}
		
		public static function doPost($url, $params=null) {
			if (extension_loaded('curl'))
				return self::_doPostCurl($url, $params);

			$inf = parse_url($url);
			
			$Request = new StdClass();
			$Request->method = 'POST';
			$Request->host   = $inf['host'];
			$Request->port   = isset($inf['port']) ? $inf['port'] : 80;
			$Request->path   = $inf['path'];
			$Request->headers = array(
				'Host'         => $inf['host'],    // Optional, will be used from $Request->host if empty
				'Connection'   => 'Close',
				'Content-Type' => 'application/x-www-form-urlencoded',
			);
			
			$p = '';
			if (is_array($params) && (count($params)>0) )
				$p = http_build_query($params);
			else if ( is_string($params))
				$p = $params;
			
			if (isset($inf['query']) && (strlen($inf['query'])>0) )
				$p .= ($p ? '&' : '') . $inf['query'];
			
			$Request->body = $p;
			
			return HttpUtils::sendRequest($Request);
		}
		
		/**
		* doPost method implemented using curl
		* @uses php_curl
		*/
		private static function _doPostCurl($url, $params=null) {
			
			$info = parse_url($url);
			
			if (is_array($params)) {
				$data = empty($info['query']) ? $params : $info['query'] . '&' . http_build_query($params);
			} else {
				$data = empty($params) ? $info['query'] : $params; // Send raw post data
			}
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL            => strtok($url, '?#'),
				CURLOPT_POST           => true,
				CURLOPT_POSTFIELDS     => $data,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER         => true,
				CURLOPT_HTTPHEADER     => array(
					'Expect: ',
				),
			)); 
			$response = curl_exec($curl);
			curl_close($curl);
			
			return HttpUtils::parseHttpResponse($response);
		}
		
		private static function sendRequest($Request, $options=array()) {
			if (!empty($Request->body))
				$Request->headers['Content-Length'] = strlen($Request->body);
			
			if (empty($Request->headers['Host']))
				$Request->headers['Host'] = $Request->host;
			
			if (empty($Request->timeout))
				$Request->timeout = HttpUtils::DEFAULT_TIMEOUT;
			
			if (empty($Request->port))
				$Request->port = 80;
			
			
			$req = "{$Request->method} {$Request->path} HTTP/1.0\r\n";
			foreach ($Request->headers as $key=>$val)
				$req .= "$key: $val\r\n";
			$req .= "\r\n" . ( !empty($Request->body) ? $Request->body : '' );
			
			$errno = $errstr = null;
			$f = @fsockopen($Request->host, $Request->port, $errno, $errstr, $Request->timeout);
			if (!$f) {
				$Response = new StdClass();
				$Response->isError = "Connecting to {$Request->host}:{$Request->port} failed: $errstr, $errno";
				return $Response;
			}
			stream_set_blocking($f, 0);
			fputs($f, $req);
			$res = '';
			while (!feof($f)) $res .= fgets($f, 1024);
			fclose($f);
			
			return HttpUtils::parseHttpResponse($res, $options);
		}
		
		public static function parseHttpResponse($res, $options=array()) {
		  list($hdata, $body) = explode("\r\n\r\n", $res, 2);
		  $hdata = explode("\r\n", $hdata);
		  $httpLine = trim($hdata[0]);
		  array_shift($hdata);
		  $headers = array();
		  foreach ($hdata as $h) {
			list($key, $val) = explode(":", $h, 2);
			$key = trim($key);
			if (!empty($options['headers.lowercase']))
				$key = strtolower($key);
			$headers[$key] = trim($val);
		  }
		  
		  $m = null;
		  preg_match('|^HTTP/(\\d+\\.\\d+)\\s+(\\d+)\\s+(.*)$|i', $httpLine, $m);
		  
		  $R = new StdClass();
		  $R->isError = false;
		  $R->body = $body;
		  $R->headers = $headers;
		  $R->httpCode = $m[2];
		  $R->httpStatus = $m[3];
	
		  return $R;
		}
	  
		
		private static function update_max_execution_time($sec=600) {
			set_time_limit(0);
		}
	}
?>
