<?php

/**
* This is just a etst object 
* used for 
* @Entity
* @Table(name="tests") --hello
* @MegaAnnotation   (  list=[10,20,30,40], hash={"name": "He\"l}lo", "surname": "M\to)o"}) garbage
* @version 2.1
*/
class TestClass  {
	
	public function __construct() {
		var_dump(__METHOD__);
	}

}
