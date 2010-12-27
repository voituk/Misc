<?php
  /**
  * Logger system class
  * @author Vadim Voituk <vadim at voituk.com>
  */

  class Logger {
	  
	  const DEBUG = "DEBUG";
	  const INFO  = " INFO";
	  const ERROR = "ERROR";
	  const SQL   = "  SQL";
	  
	  private static $format = '%level% %ip% %datetime%%uniqid% %message%';
	  
	  private static $uniqid = null;
	  
    /**
    * @static
    */
    function log_file($filepath=null, $systemerr=false) {
      static $log_file;
      if ( is_null($filepath) ) 
		  return empty($log_file)?'/dev/null':$log_file;
	  if ($systemerr) {
		  ini_set('log_errors', true);
		  ini_set('error_log', $filepath);
		  ini_set('error_prepend_string', 'PHP_FATAL_ERROR '.$_SERVER['REMOTE_ADDR']);
		  ini_set('error_append_string', '');
		  ini_set('display_errors', false);
	  }
      return $log_file = $filepath;
    }

	/**
	* @static
	*/
	function log($message, $level=null) {
	  $file = @fopen(Logger::log_file(), "a+");
	  if (!$file) return false;
	  
	  if ($message instanceof Exception) 
		  $message = $message->__toString();
	  else 
	  	$message = is_array($message) || is_object($message) ? print_r($message, true) : $message;
	  
	  $s = str_replace(
		  array(
			  '%level%',
			  '%ip%',
			  '%datetime%',
			  '%message%',
			  '%uniqid%',
			),
		  array(
			  $level?$level:Logger::INFO,
			  isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
			  date('[Y/m/d H:i:s]'),
			  $message,
			  empty(Logger::$uniqid)?'':" ".Logger::$uniqid,
			),
		  Logger::$format
		);
	  fputs($file, $s."\n");
	  fclose($file);
	}
	
	public static function error($message) { Logger::log($message, Logger::ERROR);}
	public static function info($message)  { Logger::log($message, Logger::INFO);}
	
	public static function debug($message, $asList=false) {
		if (is_array($message) && $asList) 
			$message = "[ $asList".join("$asList, $asList", $message)."$asList ]";
		Logger::log($message, Logger::DEBUG);
	}
	
	public static function sql($sql, $arr=null) {
		Logger::log($sql, Logger::SQL);
		if (!empty($arr)) {
			$arr = is_array($arr)?$arr:array($arr);
			Logger::log("[ '".join("', '", $arr)."' ]", Logger::SQL);
		}
	}
	
	
	/**
	* %level%    - current log-message level
	* %ip%       - $_SERVER['REMOTE_ADDR'] value 
	* %datetime% - timestamp
	* %message%  - message text
	* %uniqid%   - Unique per call
	*/
	public static function format($format=null) {
		if (is_null($format)) return Logger::$format;
		Logger::$format = $format;
	}
	
	public static function uniqid($uniqid) {
		Logger::$uniqid = $uniqid;
	}
	
  }

?>
