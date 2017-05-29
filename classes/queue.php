<?php

class Queue {

	protected $path;

# constructor returns void
	public function __construct($path){
		$this->path = $path;
	}

	public function push($content){
		$file = $this->path .uniqid('', true);
		$data = serialize($content);

		return file_put_contents($file, $data);
	}

	public function getNextItem(){
		$filenames = scandir($this->path);
		$filenames = array_diff($filenames, ['.', '..']);

		$filename = array_shift($filenames);
		if($filename !== null){
			$file = $this->path.$filename;
			$contents = file_get_contents($file);
			if($contents !== false){
				$object = unserialize($contents);
				if($object !== false){
					unlink($file);
					return $object;
				}
			}
		}
	}

}
