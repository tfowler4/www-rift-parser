<?php
	$filepath 			= dirname(__FILE__);
	include_once 		$filepath."/config/nav.php";

	$tier_array 			= array();
	$type_array			= array();
	$dungeon_array			= array();
	$mob_array			= array();
	$mobName_array			= array();
	$parseType_array 		= array();
	$encounter_array		= array();
	$query_all_tier_string		= "(tier='";
	$query_all_type_string		= "(players='";
	$query_all_dungeon_string	= "(dungeon='";
	$query_all_name_string		= "(name='";
	$query_all_parse_string		= "(parseType='";
	$tier 				= "";
	$numOfPlayers 			= "";
	$dungeon 			= "";
	$encName 			= "";
	$parseType 			= "";
	$startDate			= "";
	$endDate			= "";
	$query_string			= "WHERE ";

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
	if ( $_GET['tier'] != "" ) {
		$tier = "tier=" . $_GET['tier'] . "";
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

	if ( $_GET['enc'] != "" ) {
		$encName = "name='" . $_GET['enc'] . "'";
	} else {
		$encName = $query_all_name_string;
	}

	if ( $_GET['type'] != "" ) {
		$parseType = "parseType='" . $_GET['type'] . "'";
	} else {
		$parseType = $query_all_parse_string;
	}
	
	$startDate = "starttime>='" . toDBFormat($_GET['start']) . "'";
	$endDate = "starttime<'" . toDBFormat($_GET['end']) . "' + INTERVAL 1 DAY";

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

	$query_string 	= "(title='";
	$query_string 	.= implode("' OR title='", $mobName_array)."')" . " AND " . $parseType;
	
	if ( strtotime($_GET['start']) != strtotime($_GET['end']) ) {
		$query_string 	.= " AND (" . $startDate . " AND " . $endDate . ")";
	} else {
		$query_string 	.= " AND " . $endDate;
	}
	
	//echo $query_string;
	
	$encounter_query = search_for_encounter($query_string);
	while ($row = mysql_fetch_array($encounter_query)) {
		array_push($encounter_array, $row);
	}
	//***************END-GETTING LIST OF MOBS THAT MATCHED SEARCH FILTERS**************

	if (count($encounter_array) != 0) {
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
	} else {
	echo "	<div style=\"width:100%\" align=\"center\">Search returned 0 results.</div>";
	}

	mysql_close($link);
?>