<?php
class Annotation {
	
	private $name;
	private $attributes;
	
	public function __construct($name, $attr=array()) {
		$this->name = $name;
		$this->attributes = $attr;
	} 
	
	public function getName() {
		return $this->name;
	}
	
	public function getAttribute($name) {
		return $this->attributeExists($name)
			? $this->attributes[$name]
			: null;
	}
	
	public function attributeExists($name) {
		return array_key_exists($name, $this->attributes);
	}
}
