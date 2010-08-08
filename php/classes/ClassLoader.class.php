<?php

class ClassNotFoundException extends RuntimeException {}

class ClassLoader {

	private static $dirs;
	
	/** Hide default contructor */
	private function __construct() {}

	/**
	* @param list of dirs
	*/
	public static function init() {
		$dirs = func_get_args();
		self::$dirs = empty($dirs) ? array('.') : $dirs;
		spl_autoload_register(array(__CLASS__, 'loadClass'));
	} 
	
	public static function loadClass($className) {
		if (class_exists($className, false))
			return false;
		foreach (self::$dirs as $d) {
			$fn = "$d/$className.class.php";
			if (!is_file($fn))
				continue;
			
			require $fn;
			if (!class_exists($className, false))
				throw new ClassNotFoundException("$fn have no $className class definition");
			
			return;
		}
		throw new ClassNotFoundException($className);
	}

}
