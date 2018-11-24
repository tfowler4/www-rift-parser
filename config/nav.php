<?php
	include_once 'dbinfo.php';
	include_once 'functions.php';
	include_once 'scripts.php';
	include_once 'querys.php';

	$ROOT_PATH 			= "/".basename(dirname(dirname(__FILE__)));

	$FOLD_MODULES		= $ROOT_PATH.		"/modules/";
	$FOLD_CONFIG		= $ROOT_PATH.		"/config/";
	$FOLD_JAVASCRIPT	= $ROOT_PATH.		"/js/";
	$FOLD_IMAGES		= $ROOT_PATH.		"/images/";

	$FOLD_INDEX			= $ROOT_PATH;
	$FOLD_PIP			= $FOLD_MODULES.	"pip/";
	$FOLD_PLAYER		= $FOLD_MODULES.	"player/";
	$FOLD_LIST			= $FOLD_MODULES.	"list/";
	$FOLD_P2P			= $FOLD_MODULES.	"p2p/";
	$FOLD_ABILITYQ		= $FOLD_MODULES.	"abilityq/";

	$PAGE_INDEX			= $FOLD_INDEX.		"\index.php";
	$JS_INDEX			= $FOLD_INDEX.		"\index.js";

	$PAGE_PIP			= $FOLD_PIP.		"pip.php";
	$JS_PIP				= $FOLD_PIP.		"pip.js";

	$PAGE_PLAYER		= $FOLD_PLAYER.		"player.php";
	$JS_PLAYER			= $FOLD_PLAYER.		"player.js";

	$PAGE_LIST			= $FOLD_LIST.		"list.php";
	$JS_LIST			= $FOLD_LIST.		"list.js";

	$PAGE_P2P			= $FOLD_P2P.		"p2p.php";
	$JS_P2P				= $FOLD_P2P.		"p2p.js";

	$PAGE_ABILITYQ		= $FOLD_ABILITYQ.	"abilityq.php";
	$JS_ABILITYQ		= $FOLD_ABILITYQ.	"abilityq.js";
?>