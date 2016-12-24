<?php
	/**
	* Implementaion of managed per-process lock-free file-based mutex
	* 
	* @todo - Implement blocking mutex (waiting for lock release)
	*
	* @version $Id$
	* @author Vadim
	* @since PHP 5.3
	* @uses posix
	*/
	
	class Lock {
		
		const DEFAULT_LOCK_DIR = '/var/run';
		
		private $lockDir;
		private $lockId;
		private $autoRelease;
		
		/**
		* @param bool Specify if lock should be released automatically on normal process shutdown 
		*/
		public function __construct($lockId, $lockDir=null, $autoRelease=true) {
			$this->lockId  = $lockId;
			$this->lockDir = $lockDir ?: self::DEFAULT_LOCK_DIR;
			$this->autoRelease = $autoRelease;
			if ($this->autoRelease)
				register_shutdown_function(array($this, 'release'));
		}
		
		/**
		* Check if lock file exists
		* @return bool
		*/
		public function exists() {
			$f = self::getLockFile();
			if (!is_file($f))
				return false;
			$pid = trim( file_get_contents($f) );
			$find = shell_exec("ps -p $pid -o pid=");
			return !empty($find);
		}
		
		
		/**
		* Acquire lock
		* @throws ErrorException on any error
		*/
		public function acquire() {
			$f = self::getLockFile();
			
			$fd = @fopen($f, "w+");
			if (!$fd)
				throw new ErrorException("Can't open file: $f");
				
			flock($fd, LOCK_EX);
			fputs($fd, posix_getpid());
			flock($fd, LOCK_UN);
			fclose($fd);
		}
		
		/**
		* @throws ErrorException
		*/
		public function release() {
			$f = self::getLockFile();
			if (!is_file($f))
				return;
			if (!unlink($f))
				throw new ErrorException("Can't remove file: $f");
		}
		
		public function getId() {
			return $this->lockId;
		}
		
		private function getLockFile() {
			return "$this->lockDir/$this->lockId.pid";
		}
	}
?>
