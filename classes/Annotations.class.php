<?php
class Annotations {
	
	private static $cache;
	
	private function __construct() {}
	
		
	public static function getClassAnnotations($clazz) {
		$clazz = $clazz instanceof ReflectionClass ? $clazz : new ReflectionClass($clazz); 
		
		return AnnotationsParser::parse($clazz->getDocComment());
		
		// Stub
		//$a = new ArrayObject();
		//$a["test"] = $clazz->getName();
		//return $a;
	}
	
}
