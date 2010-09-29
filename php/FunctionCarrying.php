<?php

class Func {

	public static function carry($callable, $arg1) {
		if (!is_callable($callable))
			throw new RuntimeException(__FUNCTION__.': 1st argument should be callable. See is_callable() for more info.');

		return function() use ($arg1, $callable)	{
			$arr = func_get_args();
			array_unshift($arr, $arg1);
			return call_user_func_array($callable, $arr);
		};
	}
}

	function add($a, $b) {
		echo "$a + $b = " . ($a+$b) . "\n";
		return $a + $b;
	}


	$f = function($a, $b) {
		echo "$a + $b = " . ($a+$b) . "\n";
		return $a + $b;
	};

	$inc10 = Func::carry($f, 10);
	$inc20 = Func::carry('add', 20);

	var_dump($inc10, $inc20);
	var_dump( $inc10(100), $inc20(100) );

	//$f(10, 1);

?>
