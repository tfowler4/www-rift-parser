<?php
	$dbtype       	= 'mysql';
	$dbhost       	= 'localhost';
	$dbname       	= 'vgtrin5_dps';
	$dbuser       	= 'vgtrin5_dpsadmin';
	$dbpass       	= 'Trinity05!';

	//$dbtype       	= 'mysql';
	//$dbhost       	= 'localhost';
	//$dbname       	= 'vgtrin5_dps';
	//$dbuser       	= 'root';
	//$dbpass       	= 'Trinity05';

	$link = mysql_connect($dbhost, $dbuser, $dbpass);

	if (!$link) {
	 	die('Could not connect: ' . mysql_error());
	}

	mysql_select_db('vgtrin5_dps') or die(mysql_error());
?>