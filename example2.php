<?php

require_once('counter.php');

$counter = new counter();

$counter->setKey( 1 , 'user' );

var_dump( $counter->getCount() );
?>