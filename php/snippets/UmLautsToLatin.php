<?php
	$f = '§äàáãâåÂÃÄÁÀÅöôóõºòøðÕÖÔÓØ±êèéëÉÈÊÇçìïíîÌÏÍÎÐùûüÚÜÛÑñßý';
	$t = 'saaaaaaAAAAAAooooooooOOOOOteeeeEEECciiiiiIIIDuuuUUUNnby';
	
	$replace = array(
		'Æ' => 'Ae',
		'æ' => 'ae',
	);
	
	mb_internal_encoding("UTF-8");
	function mb_str_split($s) {
		$a = array();
		for ($i = 0, $len=mb_strlen($s); $i<$len; $i++)
			$a[] = mb_substr($s, $i, 1);
		return $a;  
	}

