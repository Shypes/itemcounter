<?php

define( 'redis_scheme',     'tcp');
define( 'redis_host',       'localhost');
define( 'redis_port',       '6379');
define( 'redis_database',   '2');

define( 'memcache_host',    '127.0.0.1');
define( 'memcache_port',    '11211');
 
class counter
{
   
    private $catcheclient;
    private $key;  
    private $clinet             = 'redis';
    public $assist              = false; 
    public $maxview             = 20;
    public $startview           = 10;
   
    public function __construct($clinet = '')
    {
        if('' != $clinet)
            $this->clinet = $clinet;

        $this->init(); 
    }
    private function init()
    {
        switch ($this->clinet) {
            case 'memcache':
                $this->catcheclient =& $this->memcache();
            break;
            default:
                $redis =& $this->redis();
                $this->catcheclient = $redis->client;
            break;
        }
    }
    public function doCount( $trackid, $prefix )
    {
        $this->setKey( $trackid, $prefix );
        $this->increment(); //INCREMENT COUNTER
    }
    public function setKey( $trackid, $prefix )
    {  
        $this->key = $prefix.':'.$trackid;
    }
    private function setCount($count)
    {  
        $this->writeToFile($count);   
    }
    private function increment()
    {
        $this->currentCount = $this->getCount( true ); // pass true to get count of the corrent day
        $this->currentCount++; //INCREMENT
        $this->pushcount();    // assit count if required
        $this->setCount($this->currentCount);  
    }
    private function pushcount()
    {
        if($this->assist == true)
        {
            $strings   = array();
            $i = $this->startview;
            for($i; $i<= ($this->maxview+$this->startview); $i++){
                $strings[] = $i;
            }
            $key        = array_rand($strings);
            $push       = (int)$strings[$key];
            $this->currentCount      +=  $push;
        }
    }
    public function getCount($currentday = false)
    {
        $contents = $this->catcheclient->get( $this->key );
        if(strlen($contents) > 0 && $contents{0} == '{')
        {
            $contents = (array)json_decode($contents,true);
            if($currentday == true)
            {
                $keytime  = (int) $this->get_time_key();
                if(array_key_exists($keytime, $contents)){
                    return $contents =  (int) $contents[$keytime];
                }
                return 0;
            }
            return (int)$contents = array_sum($contents);
        }else{
            return 0;
        }
    }
    private function get_time_key($m = '',$d = '',$y = ''){
        $d = ($d == '') ? date('j') : $d;
        $m = ($m == '') ? date('n') : $m;
        $y = ($y == '') ? date('Y') : $y;
        return mktime(0, 0, 0, $m, $d, $y);
    }
    private function writeToFile($count)
    { 
        $contents = $this->catcheclient->get( $this->key );
        if(!empty($contents)){
            $json = (array)json_decode(trim($contents),true);
        }else{
            $json = array();    
        }
        $keytime = (int)$this->get_time_key();
        $json[$keytime] = $count;
        $json = json_encode($json);
        $this->catcheclient->set( $this->key , $json );
        return true;
    }

    // catche client

    // uses redis wraper client
    function &redis($soft = false)
    {
        static $instances;
        global $redis;
        if (!isset ($instances))    $instances = array();
        if (empty($instances['redis'])) {   
            require_once('redis/Predis.php');
            $object     = new Predis();
            $object->scheme     = redis_scheme;
            $object->host       = redis_host;
            $object->port       = redis_port;
            $object->database   = redis_database;
            $object->soft       = $soft;
            $object->connect();
            $redis = $object;
            $instances['redis'] = $object;
        }return $instances['redis'];
    }
    // requires memchache to be compiled with php
    
    function &memcache($soft = false)
    {
        static $instances;
        global $memcache;
        if (!isset ($instances))    $instances = array();
        if (empty($instances['memcache'])) {    
            $object     = new Memcache;
            $object->addServer ( memcache_host,  memcache_port ,true);
            $memcache = $object;
            $instances['memcache'] = $object;
        }return $instances['memcache'];
    }
}
?>