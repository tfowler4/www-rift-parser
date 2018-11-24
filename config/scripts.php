<?php
	$ROOT_PATH 	= "/".basename(dirname(dirname(__FILE__)));

	$JQUERY			= $ROOT_PATH."/js/jquery-1.7.2.js";
	$SORTABLE		= $ROOT_PATH."/js/sorttable.js";
	$HIGHCHARTS		= $ROOT_PATH."/js/highcharts/js/highcharts.js";
	$EXPORTING		= $ROOT_PATH."/js/highcharts/js/modules/exporting.js";

	echo "		<script type=\"text/javascript\" src=\"".$JQUERY."\"></script>";
	echo "		<script type=\"text/javascript\" src=\"".$SORTABLE."\"></script>";
	echo "		<script type=\"text/javascript\" src=\"".$HIGHCHARTS."\"></script>";
	//echo "		<script type=\"text/javascript\" src=\"".$EXPORTING."\"></script>";
?>