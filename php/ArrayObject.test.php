<?php
	$a = new ArrayObject();
	$a['hello'] = 'Moto';
	$a['attr'] = (object)array(
		'name' => 'Vadim', 
		'surname' => 'Voituk',
	);
	
	
	var_dump($a->offsetExists('attr'));
	$a->setFlags(ArrayObject::ARRAY_AS_PROPS);
	var_dump($a->attr);
