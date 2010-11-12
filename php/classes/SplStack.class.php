<?php
	/**
	* Small part of SplStack implementation 
	* Need to make SimpleProfiler work in PHP < 5.3
	*/
	if (class_exists('SplStack')) return;

	class SplStack {
		
		private $arr;

		public function __construct() {
			$this->arr = array();
		}

		public function push($it) {
			array_push($this->arr, $it);
		}

		public function pop() {
			return array_pop($this->arr);
		}

		public function top() {
			return $this->arr[count($this->arr)-1];
		}
		

	}
?>
