<?php
	require __DIR__.'/classes/ClassLoader.class.php';
	ClassLoader::init('.', __DIR__.'/classes');
	
	//$t = new TestClass();

	$ann = Annotations::getClassAnnotations( 'TestClass' );
	$ann->setFlags(ArrayObject::ARRAY_AS_PROPS);
	print_r($ann->MegaAnnotation);
	
	$pann = Annotations::getPropertyAnnotations('User', 'name');
	
	//new UnexistantClass();
	
	//$s = '{"name": "Hel}lo", "surname": "M\tot)o"}';
	//var_dump(json_decode($s));
