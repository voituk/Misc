<?php
/*mente was here*/
/**
* Just another test class used for annotation engine testing
* @author Vadim Voituk
* @Entity
* @Table(name=users)
*/
class User {
	
	/**
	* @var(type=int)
	*/
	private $id;
	
	/**
	* @Field(name=user_name) 
	* @var(type=string)
	*/
	private $name;
	
}
