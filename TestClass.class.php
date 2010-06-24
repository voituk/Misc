<?php

/**
* This is just a test object with annotations
* 
* @Entity
* @Table(name="tests") --hello
* @MegaAnnotation   (  list=[10,20,30,40], hash={"name": "He\"l}lo", "surname": "M\to)o"}) garbage
* @version 2.1
* @var(type=hello)
*/
class TestClass  {
	
	/**
	* @Id
	* @var(type=int)
	*/
	private $id;

	public function __construct() {
		var_dump(__METHOD__);
	}

	

}
