<?php
	
	class SimpleProfiler {
		
		protected static $root;
		protected static $currentNode;
		protected static $stack;
		protected static $enabled;
		
		protected $name;
		protected $children;
		private $start;
		private $end;
		protected $duration;
		
		private function __construct($name) {
			$this->name  = $name;
			$this->children = array();
			$this->start = microtime(true);
		}
		
		protected function _leaveBlock() {
			$this->end = microtime(true);
			$this->duration = $this->end - $this->start;
		}
		
		public function __toString() {
			return "$this->name - " . round($this->duration, 4);
		}
		
		public function getDuration() {
			return $this->duration;
		}
		
		public static function init($ip=null) {
			static::$enabled = is_array($ip) ? in_array(SiteCore::getClientIp(), $ip) : true;

			if (!static::$enabled)
				return;
			
			static::$stack = new SplStack();
			$className = get_called_class();
			static::$root  = new $className('@');
			static::$stack->push(static::$root);
		}
		
		public static function enable($enable){
			static::$enable = $enable;
		}
		
		public static function enter($name) {
			if (!static::$enabled)
				return;
			$className = get_called_class();
			$obj = new $className($name);
			$top = static::$stack->top();
			$top->children[] = $obj;
			static::$stack->push($obj);
		}
		
		public static function leave() {
			if (!static::$enabled)
				return;
			$top = static::$stack->pop();
			$top->_leaveBlock();
		}
		
		/**
		* Same as leave() + enter($name)
		*/
		public static function step($name) {
			static::leave();
			static::enter($name);
		} 
		
		public static function stop() {
			if (!static::$enabled)
				return;
			static::$root->_leaveBlock();
			// enabled = false;
		}
		
		
		public static function flush($file, $prefix=null, $postfix=null) {
			$f = @fopen($file, 'a+');
			if (!$f) return;
			@flock($f, LOCK_EX);
			@fputs($f, $prefix.static::_flush().$postfix );
			@flock($f, LOCK_UN);
			@fclose($f);
		}
		
		public static function root() {
			return static::$root;
		} 
		
		protected static function _flush($root=null, $indent=null) {
			$root = $root ?: static::$root;
			$indent = $indent ? $indent : 0;
			$s = "\n".str_repeat("\t", $indent) . $root;
			for ($i=0, $len=count($root->children); $i<$len; $i++)
				$s .= static::_flush($root->children[$i], $indent+1);
			return $s;
		}
		
	}
	
?>
