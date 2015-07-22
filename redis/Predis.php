<?php 

require_once(__DIR__.'/predis/Autoloader.php');

class Predis {
	public $client;
	var $soft 		= false;
	var $connected 	= false; 
	var $scheme;	
	var $host;		
	var $port; 			
	var $database;

	public function connect(){
		Predis\Autoloader::register();
		try {
			$this->client = new Predis\Client(array(
				"scheme" 		=> $this->scheme,
				"host" 			=> $this->host,
				"port" 			=> $this->port,
				"database" 		=> $this->database
			));
			$this->connected = true;
		}
		catch (Exception $e) {
			if($this->soft == false){
				die("Unable to connect to Redis: {$e->getMessage()}");
			}
		}
	}
}
