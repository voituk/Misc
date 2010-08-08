<?php
class Annotations {
	
	private static $classCache;
	private static $methodsCache;
	private static $propsCache;
	
	private function __construct() {}
	
	/**
	* @param ReflectionClass|String $clazz
	*/
	public static function getClassAnnotations($clazz) {
		$className = $clazz instanceof ReflectionClass ? $clazz->getName() : $clazz;
		if (!isset(self::$classCache[$className])) {
			$clazz = $clazz instanceof ReflectionClass ? $clazz : new ReflectionClass($clazz);
			self::$classCache[$className] = AnnotationsParser::parse($clazz->getDocComment()); 
		}
		return self::$classCache[$className];
	}
	
	public static function getMethodAnotation() {
	}
	
}
