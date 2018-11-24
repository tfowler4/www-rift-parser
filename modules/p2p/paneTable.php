<?php
	$filepath 			= dirname(dirname(dirname(__FILE__)));
	include_once 		$filepath."/config/nav.php";

	ob_start( 'load_time' );

	$encid 						= $_GET['enc'];
	$mobName					= $_GET['mob'];
	$displayType				= $_GET['type'];
	$players 					= array();
	$playerName_string			= "";

	if ( isset($_GET['players']) ) {
		$players = explode("_", $_GET['players']);
	} else {
		$players = $_POST["compare"];
	}

	$playerName_string 			= $_GET['players'];
	$numOfPlayers 				= count($players);
	$numOfAbilities 			= 0;
	$numFilter 					= 0;
	$numFilterName				= 0;
	$currentRow 				= 0;
	$ability_array 				= array();
	$abilityList_array 			= array();
	$abilityList_dps_array 		= array();
	$abilityList_hps_array 		= array();
	$abilityList_incdps_array	= array();
	$abilityList_inchps_array	= array();
	$playerDPS_array			= array();
	$playerHPS_array			= array();
	$playerINCDPS_array			= array();
	$playerINCHPS_array			= array();
	$topValues 					= array();
	$mobs_array					= array();
	$query_player_string 		= "(attacker='";
	$query_mob_attacker_string	= "(attacker='";
	$query_player_victim_string	= "(victim='";
	$query_ability_string 		= "(type='";

	$curAbil 					= "";
	$prevAbil 					= "";
	$idName						= '';
	$chartName					= '';

	//***************START-GET ENCOUNTER DETAILS**************
	$encounter_query 	= get_encounter_details($encid);
	$encounterDetails 	= mysql_fetch_array($encounter_query);
	//***************END-GET ENCOUNTER DETAILS**************

	//***************START-GET QUERY STRINGS**************
	$encounter_query = get_encounter_details($encid);
	$encounterDetails = mysql_fetch_array($encounter_query);

	$mobs_array							= explode("_", $mobName);
	$query_mob_victim_string			= "(victim='";
	$query_mob_victim_string			.= implode("' OR victim='", $mobs_array)."')";

	$query_ability_string 				.= implode("' OR type='", $ability_array). "')";
	$query_player_string 				.= implode("' OR attacker='", $players) . "')";
	$query_mob_attacker_string 			.= implode("' OR attacker='", $mobs_array) . "')";
	$query_player_victim_string			.= implode("' OR victim='", $players) . "')";
	//***************END-GET QUERY STRINGS**************

	//***************START-GET ABILITIES**************

	if ($displayType == "DPS") {
		array_push($abilityList_dps_array, "All");

		$ability_query = get_p2p_ability_data_dps_abilities($encid, $query_player_string, $query_mob_victim_string);
		while ($row = mysql_fetch_array($ability_query)) {
			if ( !(in_array($row['attacktype'], $abilityList_dps_array)) ) {
				array_push($abilityList_dps_array, $row['attacktype']);
			}
		}

		$abilityList_array = $abilityList_dps_array;

	} else if ($displayType == "HPS") {
		array_push($abilityList_hps_array, "All");

		$ability_query = get_p2p_ability_data_hps_abilities($encid, $query_player_string);
		while ($row = mysql_fetch_array($ability_query)) {
			if ( !(in_array($row['attacktype'], $abilityList_hps_array)) ) {
				array_push($abilityList_hps_array, $row['attacktype']);
			}
		}

		$abilityList_array = $abilityList_hps_array;

	} else if ($displayType == "INCDPS") {
		array_push($abilityList_incdps_array, "All");

		$ability_query = get_p2p_ability_data_incdps_abilities($encid, $query_mob_attacker_string, $query_player_victim_string);
		while ($row = mysql_fetch_array($ability_query)) {
			if ( !(in_array($row['attacktype'], $abilityList_incdps_array)) ) {
				array_push($abilityList_incdps_array, $row['attacktype']);
			}
		}

		$abilityList_array = $abilityList_incdps_array;
	} else if ($displayType == "INCHPS") {
		array_push($abilityList_inchps_array, "All");

		$ability_query = get_p2p_ability_data_inchps_abilities($encid, $query_player_victim_string);
		while ($row = mysql_fetch_array($ability_query)) {
			if ( !(in_array($row['attacktype'], $abilityList_inchps_array)) ) {
				array_push($abilityList_inchps_array, $row['attacktype']);
			}
		}

		$abilityList_array = $abilityList_inchps_array;
	}
	//***************END-GET ABILITIES**************
	echo "		<form name=\"filterForm\" action=\"".$PAGE_ABILITYQ."?type=".$displayType."&enc=".$encid."&abil=&mob=".$mobName."\" method=\"POST\">";
	echo "			<table id=\"filterNameTable\" align=\"center\" border=\"1\" width=\"100%\">";
	echo "				<tr>";
	echo "					<td colspan=\"".$numOfPlayers."\" align=\"center\"><b>Players</b></td>";
	echo "				</tr>";
	echo "				<tr>";
							$numPerRow = $numOfPlayers;
							if ( $numOfPlayers > 6)
								$numPerRow = 6;
							for ($count = 0; $count < $numOfPlayers; $count++) {
								$numFilterName++;

								if ($numFilterName % $numPerRow == 0) {
	echo "							<td align=\"center\"><input type=\"checkbox\" name=\"filterCompareName[]\" value=\"".$players[$count]."\">".$players[$count]."</td>";
	echo "							</tr>";
	echo "							<tr>";
								} else {
	echo "							<td align=\"center\"><input type=\"checkbox\" name=\"filterCompareName[]\" value=\"".$players[$count]."\">".$players[$count]."</td>";
								}
							}
	echo "				</tr>";
	echo "			</table>";
	echo "				<table id=\"filterTable\" align=\"center\" border=\"1\" width=\"100%\">";
	echo "					<tr>";
	echo "						<td colspan=\"4\" align=\"center\"><b>Ability Filter</b></td>";
	echo "					</tr>";
								$numFilter = 0;
								for ($count = 0; $count < count($abilityList_array); $count++) {
									if ($abilityList_array[$count] != $prevAbil) {
										$curAbil = $abilityList_array[$count];
										$numFilter++;

										if ($numFilter % 4 == 0) {
	echo "									<td><input type=\"checkbox\" name=\"filterCompareAbility[]\" value=\"".addslashes($curAbil)."\">".stripslashes($curAbil)."</td>";
	echo "									</tr>";
	echo "									<tr>";
										} else {
	echo "									<td><input type=\"checkbox\" name=\"filterCompareAbility[]\" value=\"".addslashes($curAbil)."\">".stripslashes($curAbil)."</td>";
										}
									}
								}
	echo "					</tr>";
	echo "				</table>";
	echo "			<center><input type=\"submit\" value=\"Generate Custom Ability Queue\"></center>";
	echo "		</form>";

	mysql_close($link);
?>