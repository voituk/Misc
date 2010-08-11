<?php
	/**
	*
	*/
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	
	require __DIR__.'/../classes/Lock.class.php';
	
	
	$lock = new Lock('test');
	if ($lock->exists())
		die("Lock '".$lock->getId()."' exists\n");
	
	echo "Acquire lock...\n";
	$lock->acquire();
	
	echo "Doing smth for 10 seconds...\n";
	echo "During this time, another instance of this script will not run\n";
	sleep(10);
	
	echo "Releasing lock...\n";
	echo $lock->release();
	
?>
