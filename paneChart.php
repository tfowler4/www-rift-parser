<?php
	$filepath 			= dirname(__FILE__);
	include_once 		$filepath."/config/nav.php";

	$tier 				= "";
	$numOfPlayers 		= "";
	$dungeon 			= "";
	$encName 			= "";
	$parseType 			= "";
	$query_string		= "WHERE ";

	$encounter_array	= array();
	$players_array		= array();
	$encid_array		= array();
	$name_array			= array();
	$dps_array 			= array();
	$hps_array 			= array();
	$idps_array			= array();
	$ihps_array			= array();
	$mob_array			= array();
	$enc_array			= array();
	$date_array			= array();

	$player_string		= 'WHERE ';
	$playerCount		= 0;
	$currentEncounter 	= '';
	$currentDate 		= '';
	$enc_string 		= '';
	$mobAmount 			= 0;

	$player_string		= 'WHERE ';
	$playerCount		= 0;
	$currentEncounter 	= '';
	$currentDate 		= '';
	$enc_string 		= '';
	$mobAmount 			= 0;

	//***************START-GENERATING QUERY STRING FROM FILTERS**************
	if ( $_GET['tier'] != "" ) {
		$tier = "tier=" . $_GET['tier'] . "";
	} else {
		$tier = "tier!=''";
	}

	if ( $_GET['play'] != "" ) {
		$numOfPlayers = "players=" . $_GET['play'] . "";
	} else {
		$numOfPlayers = "players!=''";
	}

	if ( $_GET['dun'] != "" ) {
		$dungeon = "dungeon='" . $_GET['dun'] . "'";
	} else {
		$dungeon = "dungeon!=''";
	}

	if ( $_GET['enc'] != "" ) {
		$encName = "name='" . $_GET['enc'] . "'";
	} else {
		$encName = "name!=''";
	}

	if ( $_GET['type'] != "" ) {
		$parseType = "parseType='" . $_GET['type'] . "'";
	} else {
		$parseType = "(parseType='Kill Shot' OR parseType='Attempt' OR parseType='Practice')";
	}

	$query_string = "WHERE " . $tier .
					" AND " . $numOfPlayers .
					" AND " . $dungeon .
					" AND " . $encName;
	//***************END-GENERATING QUERY STRING FROM FILTERS**************

	//***************START-GETTING LIST OF MOBS THAT MATCHED SEARCH FILTERS**************
	$encounterList_query = search_for_encounter_list($query_string);
	$mobAmount = mysql_num_rows($encounterList_query);

	if ($mobAmount > 0) {
		while ($row = mysql_fetch_array($encounterList_query)) {
			array_push($mob_array, addslashes($row['name']));
		}
	}

	$query_string = "(title='";
	$query_string .= implode("' OR title='", $mob_array)."')" . " AND " . $parseType;

	if ($mobAmount > 0) {
		$encounter_query = search_for_encounter($query_string);

		while ($row = mysql_fetch_array($encounter_query)) {
			array_push($encounter_array, $row);
			array_push($encid_array, $row['encid']);
		}

		$mobAmount = mysql_num_rows($encounter_query);
	}
	//***************END-GETTING LIST OF MOBS THAT MATCHED SEARCH FILTERS**************

	//***************START-GET DPS, HPS AND IDPS**************
	$encounterIDs = "(encid='";
	$encounterIDs .= implode("' OR encid='", $encid_array)."')";

	$dps_query = get_encounter_dps($encounterIDs);
	while ($row = mysql_fetch_array($dps_query)) {
		array_push($dps_array, $row['totaldps']);
	}

	$hps_query = get_encounter_hps($encounterIDs);
	while ($row = mysql_fetch_array($hps_query)) {
		array_push($hps_array, $row['totalhps']);
	}

	$idps_query = get_encounter_idps($encounterIDs);
	while ($row = mysql_fetch_array($idps_query)) {
		array_push($idps_array, $row['totalidps']);
	}
	
	$ihps_query = get_encounter_ihps($encounterIDs);
	while ($row = mysql_fetch_array($ihps_query)) {
		array_push($ihps_array, $row['totalihps']);
	}
	//***************END-GET DPS, HPS AND IDPS**************

	echo "	<table border=\"1\" class=\"sortable\" width=\"400\" id=\"encounterTable\" style=\"display:none;\">";
	echo "		<tr>";
	echo "			<td align=\"center\"><b>Name<b></td>";
	echo "			<td align=\"center\"><b>Date</b></td>";
	echo "			<td align=\"center\"><b>DPS</b></td>";
	echo "			<td align=\"center\"><b>HPS</b></td>";
	echo "			<td align=\"center\"><b>iDPS</b></td>";
	echo "			<td align=\"center\"><b>iHPS</b></td>";
	echo "		</tr>";

				for ($count = 0; $count < $mobAmount; $count++) {
	echo "			<tr>";
	echo "				<td align=\"center\"><a href=\"".$PAGE_LIST."?enc=".$encounter_array[$count]['encid']."\">".$encounter_array[$count]['title']."</a></td>";
	echo " 				<td align=\"center\"><a href=\"".$PAGE_LIST."?enc=".$encounter_array[$count]['encid']."\">".dateShortFormat($encounter_array[$count]['starttime'])."</a></td>";
	echo "				<td align=\"center\">".number_format($dps_array[$count], 2, '.', '')."</td>";
	echo "				<td align=\"center\">".number_format($hps_array[$count], 2, '.', '')."</td>";
	echo "				<td align=\"center\">".number_format(($idps_array[$count]/$encounter_array[$count]['duration']), 2, '.', '')."</td>";
	echo "				<td align=\"center\">".number_format(($ihps_array[$count]/$encounter_array[$count]['duration']), 2, '.', '')."</td>";
	echo "			</tr>";
				}
	echo "	</table>";

	mysql_close($link);
?>