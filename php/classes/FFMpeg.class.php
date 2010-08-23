<?php
/**
* PHP Wrapper over ffmpeg binary tool
*/
class FFMpeg {
	private $tool = '/usr/bin/ffmpeg';
	
	public function __construct($tool = null) {
		$this->tool = $tool ?: $this->tool;
		
		if (!is_file($this->tool) || !is_executable($this->tool))
			throw new ErrorException("Invalid ffmpeg tool location: $this->tool");
	}
	
	public function getFileInfo($file) {
		if (!is_file($file))
			throw new ErrorException("No such file: $file");
		
		$cmd = $this->tool . ' -i ' . escapeshellarg($file).' 2>&1';
		
		$info = new StdClass();
		$durationReached = false;
		
		foreach(explode("\n", `$cmd`) as $line) {
			$line = trim($line);
			
			if (preg_match('/^Duration:\s/', $line)) {
				$durationReached = true;
				// parse duration, start, bitrate 
				//$tok = strtok($line, ':');// skip 'Duration' string,
				foreach(explode(',', $line) as $s) {
					list($key, $val) = explode(":", $s, 2);
					$key = strtolower(trim($key));
					$val = strtolower(trim($val));
					switch ($key) {
						case 'duration':
							$info->duration = $val;
							$info->seconds  = self::duration2seconds($val);
							break;
						case 'bitrate':
							$info->bitrate = intval($val);
							break;
						default:
							$info->$key = $val;
					}
				}
			}
			
			if (!$durationReached)
				continue;
			//TODO: Process other lines folling Duration (Stream, Data, etc)
		}
		
		return $info;
	}
	
	
	private static function duration2seconds($s) {
		return array_reduce(
			explode(':', strtok($s, '.')), 
			function($r, $it) {return $r*60+$it;}, 
		0);
	}
	
}
