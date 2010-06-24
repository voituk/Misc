<?php
	require __DIR__.'/classes/ClassLoader.class.php';
	ClassLoader::init('.', __DIR__.'/classes');
	
	//$t = new TestClass();

	$ann = Annotations::getClassAnnotations( 'TestClass' );
	print_r($ann['MegaAnnotation']);
	
	//new UnexistantClass();
	
	//$s = '{"name": "Hel}lo", "surname": "M\tot)o"}';
	//var_dump(json_decode($s));
