<?php

require_once('counter.php');

$counter = new counter();

$counter->assist = true;

$counter->doCount( 1 , 'user' );

var_dump( $counter->getCount() );
?>