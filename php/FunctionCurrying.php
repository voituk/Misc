<?php

class Func {

	public static function curry($callable, $arg1) {
		if (!is_callable($callable))
			throw new RuntimeException(__METHOD__.': 1st argument should be callable. See is_callable() for more info.');

		return function() use ($callable, $arg1)	{
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

	$inc10 = Func::curry($f, 10);
	$inc20 = Func::curry('add', 20);

	print_r($inc10);
	print_r($inc20);
	var_dump( $inc10(100), $inc20(100) );

?>
