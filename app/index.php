<?php

if(phpversion() < 5) {
	echo "";
	return;
}

include 'stacey.inc.php';

// instantiate the app
$s = new Stacey($_GET);

?>