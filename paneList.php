<?php
	$filepath 			= dirname(__FILE__);
	include_once 		$filepath."/config/nav.php";

	$encid 				= $_GET['enc'];
	$type 				= $_GET['type'];
	$name 				= $_GET['name'];
	$date 				= $_GET['date'];
	$notes				= $_GET['notes'];
	$uploader 			= $_GET['uploader'];
	$parseType			= $_GET['parse'];
	$dateChanged 			= "NO";
	$nameChanged 			= "NO";
	$notesChanged	 		= "NO";
	$parseChanged 			= "NO";
	$tier_array 			= array();
	$type_array			= array();
	$dungeon_array			= array();
	$mob_array			= array();
	$mobName_array			= array();
	$parseType_array 		= array();
	$encounter_array		= array();
	$query_all_tier_string		= "(tier='";
	$query_all_type_string		= "(players='";
	$query_all_dungeon_string	= "(tier='";
	$query_all_name_string		= "(name='";
	$query_all_parse_string		= "(parseType='";
	$tier 				= "";
	$numOfPlayers 			= "";
	$dungeon 			= "";
	$encName 			= "";
	$query_string			= "WHERE ";

	$current_query 			= get_encounter_details($encid);
	$current_details 		= mysql_fetch_array($current_query);

	$dbDate 			= dateOnlyFormat($current_details['starttime']);
	$dbTime 			= timeOnlyFormat($current_details['starttime']);
	$dbName 			= $current_details['title'];
	$dbNotes 			= $current_details['notes'];
	$dbUploader 			= $current_details['uploadedby'];
	$dbParse		 	= $current_details['parseType'];

	// Checking for Different Date
	if (strtotime($date) == strtotime($dbDate)) {
		$dateChanged = "NO";
		$date = $dbDate;
	} else {
		$dateChanged = "YES";
		$date = toDBFormat($date);
	}

	// Checking for Different Name
	if ($name == $dbName) {
		$nameChanged = "NO";
		$name = $dbName;
	} else {
		$nameChanged = "YES";
		$name = $name;
	}

	// Checking for Different Notes
	if ($notes == $dbNotes) {
		$notesChanged = "NO";
		$notes = $dbNotes;
	} else {
		$notesChanged = "YES";
		$notes = $notes;
	}

	// Checking for Different Parse Type
	if ($parseType == $dbParse) {
		$parseChanged = "NO";
		$parseType = $dbParse;
	} else {
		$parseChanged = "YES";
		$parseType = $parseType;
	}

	$dbDate = toDBFormat($dbDate);
	$date = toDBFormat($date);

	if ($type == "delete") {
		remove_parse($encid);
	} else if ($type == "update") {
		if ($nameChanged == "YES" || $notesChanged == "YES" || $parseChanged == "YES") {
			update_encounter_details($name, $notes, $parseType, $dbDate, $date, $encid);
		}

		if ($uploader != $dbUploader) {
			update_encounter_details_uploader($uploader, $encid);
		}

		if ($dateChanged == "YES") {
			update_encounter_dates($name, $dbDate, $date, $encid);
		}
	}
	//***************START-GETTING SEARCH FILTERS**************
	$tierList_query = get_all_search_tiers();
	while ($row = mysql_fetch_array($tierList_query)) {
		array_push($tier_array, $row['tier']);
	}

	$typeList_query = get_all_search_types();
	while ($row = mysql_fetch_array($typeList_query)) {
		array_push($type_array, $row['type']);
	}

	$dungeonList_query = get_all_search_dungeons();
	while ($row = mysql_fetch_array($dungeonList_query)) {
		array_push($dungeon_array, addslashes($row['name']));
	}

	$encounterList_query = get_all_search_encounters();
	while ($row = mysql_fetch_array($encounterList_query)) {
		array_push($mob_array, addslashes($row['name']));
	}

	$parseList_query = get_all_search_encounterType();
	while ($row = mysql_fetch_array($parseList_query)) {
		array_push($parseType_array, $row['type']);
	}

	$query_all_tier_string		.= implode("' OR tier='", $tier_array)."')";
	$query_all_type_string		.= implode("' OR players='", $type_array)."')";
	$query_all_dungeon_string	.= implode("' OR dungeon='", $dungeon_array)."')";
	$query_all_name_string		.= implode("' OR name='", $mob_array)."')";
	$query_all_parse_string		.= implode("' OR parseType='", $parseType_array)."')";
	//***************END-GETTING SEARCH FILTERS**************

	//***************START-GENERATING QUERY STRING FROM FILTERS**************
	if ( $_GET['tiers'] != "" ) {
		$tier = "tier=" . $_GET['tiers'] . "";
	} else {
		$tier = $query_all_tier_string;
	}

	if ( $_GET['play'] != "" ) {
		$numOfPlayers = "players=" . $_GET['play'] . "";
	} else {
		$numOfPlayers = $query_all_type_string;
	}

	if ( $_GET['dun'] != "" ) {
		$dungeon = "dungeon='" . $_GET['dun'] . "'";
	} else {
		$dungeon = $query_all_dungeon_string;
	}

	if ( $_GET['encounter'] != "" ) {
		$encName = "name='" . $_GET['encounter'] . "'";
	} else {
		$encName = $query_all_name_string;
	}

	if ( $_GET['types'] != "" ) {
		$parseType = "parseType='" . $_GET['types'] . "'";
	} else {
		$parseType = $query_all_parse_string;
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
			array_push($mobName_array, addslashes($row['name']));
		}
	}

	$query_string = "(title='";
	$query_string .= implode("' OR title='", $mobName_array)."')" . " AND " . $parseType;

	$encounter_query = search_for_encounter($query_string);
	while ($row = mysql_fetch_array($encounter_query)) {
		array_push($encounter_array, $row);
	}
	//***************END-GETTING LIST OF MOBS THAT MATCHED SEARCH FILTERS**************

	echo "	<form name=\"encounterForm\" action=\"".$PAGE_LIST."\" method=\"POST\">";
	echo "		<table align=\"center\" border=\"1\" width=\"100%\">";
	echo "			<tr>";
	echo "				<td align=\"center\"><b>Parse List</b></td>";
	echo "			</tr>";
	echo "			<tr>";
	echo "				<td><center><input type=\"submit\" value=\"Parse!\"></center>";
	echo "			</tr>";
	echo "			<tr>";
	echo "				<td>";
	echo "					<select id =\"encounterList\" name=\"names\" onChange=\"MakeRequest()\" size=\"33\"  style=\"width:400px\">";
								for($count = 0; $count < count($encounter_array); $count++) {
	echo "								<option value=\"" . $encounter_array[$count]['encid'] . "\">";
	echo 									dateShortFormat($encounter_array[$count]['starttime']);
	echo " 									- ";
										if ($encounter_array[$count]['parseType'] == "") {
											echo "N";
										} else {
											echo substr($encounter_array[$count]['parseType'], 0, 1);
										}
	echo " 									- ";
										if ($encounter_array[$count]['uploadedby'] == "") {
											echo "*";
										}
	echo 									$encounter_array[$count]['title'];
										if ($encounter_array[$count]['uploadedby'] != "") {
	echo "										- ".$encounter_array[$count]['uploadedby'];
										}
	echo "								</option>";
								}
	echo "					</select>";
	echo "				</td>";
	echo "			</tr>";
	echo "		</table>";
	echo "	</form>";

	mysql_close($link);
?>