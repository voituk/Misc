<?php
	// Internal encoding should be specified before call
	// For example: mb_internal_encoding("UTF-8");
  function mb_str_split($s) {
    $a = array();
    for ($i = 0, $len=mb_strlen($s); $i<$len; $i++)
      $a[] = mb_substr($s, $i, 1);
    return $a;  
  }

