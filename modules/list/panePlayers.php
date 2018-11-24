<?php
	$filepath 			= dirname(dirname(dirname(__FILE__)));
	include_once 		$filepath."/config/nav.php";

	$encid		= "";
	$mobName	= "";

	if ( isset($_GET['enc']) ) {
		$encid = $_GET['enc'];
	}

	if ( isset($_GET['mob']) ) {
		$mobName = $_GET['mob'];
	}

	$saveTime 					= 0;
	$saveStand 					= 0;
	$numOfPlayers					= 0;
	$table_dps					= array();
	$table_hps					= array();
	$table_inc					= array();
	$encounters_array				= array();
	$encounterIDs_array				= array();
	$mobs_array					= array();
	$ranking_array					= array();
	$player_array					= array();
	$playerName_array				= array();
	$playerClass_array				= array();
	$player_final_array				= array();
	$participant_array				= array();
	$warrior_array					= array();
	$rogue_array					= array();
	$cleric_array					= array();
	$mage_array					= array();
	$encounterDetails				= array();
	$data_array					= array();
	$class_array					= array();
	$query_combatant_player_string			= "(name='";
	$query_combatant_nonplayer_string		= "(name!='";
	$query_combatant_mob_attacker_string		= "(name='";
	$query_combatant_mob_nonattacker_string		= "(name!='";
	$query_swing_player_victim_string		= "(victim='";
	$query_swing_mob_victim_string			= "(victim='";
	$query_swing_player_attacker_string		= "(attacker='";
	$query_swing_player_nonattacker_string		= "(attacker!='";
	$query_swing_mob_attacker_string		= "(attacker='";
	$query_swing_mob_nonattacker_string		= "(attacker!='";
	$query_swing_mob_player_victim_string		= "(victim='";
	$query_swing_mob_player_attacker_string		= "(attacker='";
	$encounterIDs					= "(encid='";

	//***************START-GET ENCOUNTER DETAILS**************
	$encounter_query 	= get_encounter_details($encid);
	$encounterDetails 	= mysql_fetch_array($encounter_query);

	$standardTime 		= $encounterDetails['starttime'];
	$saveStand 			= $standardTime;

	$seconds 			= $encounterDetails['duration'];

	$startTime 			= timeFormat($encounterDetails['starttime']);
	$saveTime 			= $startTime;

	$endTimeTemp 		= raidFormat($startTime, $seconds);
	$endTime 			= timeFormat($endTimeTemp);

	$durationTime 		= seconds($startTime, $endTime);
	//***************END-GET ENCOUNTER DETAILS**************

	//***************START-GET LIST OF ALL PLAYERS**************
	$player_query = get_all_players();
	while ($row = mysql_fetch_array($player_query)) {
		array_push($player_array, $row['name']);
	}
	//***************END-GET LIST OF ALL PLAYERS**************

	//***************START-GET LIST OF COMBATANTS**************
	$query_playerName_string 		= "(name='";
	$query_playerName_string 		.= implode("' OR name='", $player_array)."')";

	$player_query = get_all_combatants($encid, $query_playerName_string);
	while ($row = mysql_fetch_array($player_query)) {
		array_push($playerName_array, $row['name']);
	}

	$numOfPlayers = count($playerName_array);
	//***************END-GET LIST OF COMBATANTS**************

	//***************START-PLACE PLAYERS INTO CLASS ARRAY**************
	$class_query = get_all_players();
	while ($row = mysql_fetch_array($class_query)) {
		if (in_array($row['name'], $playerName_array)) {
			if ($row['class'] == 'Warrior') {
				array_push($warrior_array, $row['name']);
			} else if ($row['class'] == 'Rogue') {
				array_push($rogue_array, $row['name']);
			} else if ($row['class'] == 'Cleric') {
				array_push($cleric_array, $row['name']);
			} else if ($row['class'] == 'Mage') {
				array_push($mage_array, $row['name']);
			}

			$participant_array[$row['name']] = $row['class'];


			//array_push($player_final_array, $row);
		}
	}

	$temp_array = array_merge((array)$warrior_array, (array)$rogue_array, (array)$cleric_array, (array)$mage_array);
	//***************END-PLACE PLAYERS INTO CLASS ARRAY**************

	//***************START-QUERY STRINGS**************
	$query_combatant_player_string 			.= implode("' OR name='", $temp_array)."')";
	$query_combatant_nonplayer_string 		.= implode("' AND name!='", $temp_array)."')";
	$query_swing_player_victim_string 		.= implode("' OR victim='", $temp_array)."')";
	$query_swing_player_attacker_string 		.= implode("' OR attacker='", $temp_array)."')";
	$query_swing_player_nonattacker_string 		.= implode("' AND attacker!='", $temp_array)."')";

	$mobs_array					= explode("_", $mobName);
	$query_combatant_mob_attacker_string 		.= implode("' OR name='", $mobs_array)."')";
	$query_combatant_mob_nonattacker_string 	.= implode("' AND name!='", $mobs_array)."')";
	$query_swing_mob_victim_string			.= implode("' OR victim='", $mobs_array)."')";
	$query_swing_mob_attacker_string		.= implode("' OR attacker='", $mobs_array)."')";
	$query_swing_mob_nonattacker_string		.= implode("' AND attacker!='", $mobs_array)."')";
	
	$query_swing_mob_player_victim_string		.= implode("' OR victim='", $temp_array) . "' OR victim='" . implode("' OR victim='", $mobs_array)."')";
	$query_swing_mob_player_attacker_string		.= implode("' OR attacker='", $temp_array) . "' OR attacker='" . implode("' OR attacker='", $mobs_array)."')";
	//***************END-QUERY STRINGS**************

	//***************START-GET DPS/HPS/INC GRAPH DATA**************
	$data_dps_table_query = get_encounter_specific_dps_table($encid, $query_swing_mob_victim_string, $query_swing_mob_nonattacker_string);
	while ($row = mysql_fetch_array($data_dps_table_query)) {
		array_push($table_dps, $row);
	}

	$data_hps_table_query = get_encounter_specific_hps_table($encid, $query_swing_mob_nonattacker_string);
	while ($row = mysql_fetch_array($data_hps_table_query)) {
		array_push($table_hps, $row);
	}

	$data_inc_table_query = get_encounter_specifc_idps_table($encid, $query_swing_mob_attacker_string, $query_swing_player_victim_string, $query_swing_player_nonattacker_string);
	while ($row = mysql_fetch_array($data_inc_table_query)) {
		array_push($table_inc, $row);
	}
	//***************END-GET DPS/HPS/INC GRAPH DATA**************

	//***************START-GET PLAYER DETAILS FROM ENCOUNTER**************
	$hps_query = get_encounter_combatant_hps($encid, $query_combatant_player_string);
	$hps = mysql_fetch_array($hps_query);
	$encounterDetails['enchps'] 			= $hps['hps_total'];
	$encounterDetails['total_outgoing_healing'] 	= $hps['heal_total'];
	
	$data_dps_query = get_encounter_specific_dps($encid, $query_swing_mob_victim_string, $query_swing_player_attacker_string);
	while ($row = mysql_fetch_array($data_dps_query)) {
		$data_array[$row['attacker']]['class'] 		= $participant_array[$row['attacker']];
		$data_array[$row['attacker']]['damage'] 	= $row['damage_total'];

		if ( isset($data_array[$row['attacker']]['hits']) ) {
			$data_array[$row['attacker']]['hits'] = $data_array[$row['attacker']]['hits'] + $row['hits'];
		} else {
			$data_array[$row['attacker']]['hits'] = $row['hits'];
			$data_array[$row['attacker']]['healed'] = 0;
			$data_array[$row['attacker']]['damagetaken'] = 0;
		}
	}
	
	$data_hps_query = get_encounter_specific_hps($encid, $query_swing_player_attacker_string);
	while ($row = mysql_fetch_array($data_hps_query)) {
		$data_array[$row['attacker']]['class'] 		= $participant_array[$row['attacker']];
		$data_array[$row['attacker']]['healed'] 	= $row['heal_total'];

		if ( isset($data_array[$row['attacker']]['hits']) ) {
			$data_array[$row['attacker']]['hits'] = $data_array[$row['attacker']]['hits'] + $row['hits'];
		} else {
			$data_array[$row['attacker']]['hits'] = $row['hits'];
		}
	}
	
	$data_inc_query = get_encounter_specifc_idps($encid, $query_swing_mob_attacker_string, $query_swing_player_victim_string, $query_swing_player_nonattacker_string);
	while ($row = mysql_fetch_array($data_inc_query)) {
		$data_array[$row['victim']]['class'] 		= $participant_array[$row['victim']];
		$data_array[$row['victim']]['damagetaken'] 	= $row['damage_total'];
	}
	
	$data_time_query = get_encounter_specific_duration($encid, $query_swing_player_attacker_string, $query_swing_mob_player_victim_string);
	while ($row = mysql_fetch_array($data_time_query)) {
		$data_array[$row['attacker']]['duration'] = seconds($row['start'], $row['end']);
	}
	//***************END-GET PLAYER DETAILS FROM ENCOUNTER**************	

	//***************START-CLEANING EMPTY KEYS**************
	for ($count = 0; $count < $numOfPlayers; $count++) {
		$playerName = $playerName_array[$count];

		if ( !(isset($data_array[$playerName]['class'])) )
			$data_array[$playerName]['class'] 	= $participant_array[$playerName];
		if ( !(isset($data_array[$playerName]['hits'])) )
			$data_array[$playerName]['hits'] 	= 0;
		if ( !(isset($data_array[$playerName]['damage'])) )
			$data_array[$playerName]['damage'] 	= 0;
		if ( !(isset($data_array[$playerName]['encdps'])) )
			$data_array[$playerName]['encdps'] 	= 0;
		if ( !(isset($data_array[$playerName]['enchps'])) )
			$data_array[$playerName]['enchps'] 	= 0;
		if ( !(isset($data_array[$playerName]['encidps'])) )
			$data_array[$playerName]['encidps'] 	= 0;
		if ( !(isset($data_array[$playerName]['healed'])) )
			$data_array[$playerName]['healed'] 	= 0;
		if ( !(isset($data_array[$playerName]['damagetaken'])) )
			$data_array[$playerName]['damagetaken'] = 0;
		if ( !(isset($data_array[$playerName]['duration'])) )
			$data_array[$playerName]['duration'] 	= 0;
    	}
	//***************END-CLEANING EMPTY KEYS****************************

	echo "	<table border=\"1\" align=\"left\" class=\"sortable\" id=\"listTable\" width=\"700\">";
	echo "		<tr>";
	echo "			<td align=\"center\">Name</td>";
	echo "			<td align=\"center\">Class</td>";
	echo "			<td align=\"center\">Duration</td>";
	echo "			<td align=\"center\">Hits</td>";
	echo "			<td align=\"center\">DPS</td>";
	echo "			<td align=\"center\">Damage</td>";
	echo "			<td align=\"center\">HPS</td>";
	echo "			<td align=\"center\">Heals</td>";
	echo "			<td align=\"center\">iDPS</td>";
	echo "			<td align=\"center\">Inc Damage</td>";
	echo "		</tr>";

				for ($count = 0; $count < $numOfPlayers; $count++) {
					$playerName = $playerName_array[$count];
	echo "			<tr>";
	echo "				<td align=\"left\"><input id=\"".$playerName."\" type=\"checkbox\" name=\"compare[]\" value=\"" .$playerName. "\" /><a href=\"".$PAGE_PLAYER."?enc=".$encid."&player=".$playerName."&mob=".$mobName."\">".$playerName."</a></td>";
	echo "				<td align=\"center\">".$data_array[$playerName]['class']."</td>";
	echo "				<td align=\"center\">".minutes($data_array[$playerName]['duration'])."</td>";
	echo "				<td align=\"center\">".number_format($data_array[$playerName]['hits'], 0,'.',',')."</td>";
	echo "				<td align=\"center\">".number_format(($data_array[$playerName]['damage'] / $encounterDetails['duration']), 2,'.',',')."</td>";
	echo "				<td align=\"center\">".number_format($data_array[$playerName]['damage'], 0,'',',')."</td>";
	echo "				<td align=\"center\">".number_format(($data_array[$playerName]['healed'] / $encounterDetails['duration']), 2,'.',',')."</td>";
	echo "				<td align=\"center\">".number_format($data_array[$playerName]['healed'], 0,'',',')."</td>";
	echo "				<td align=\"center\">".number_format(($data_array[$playerName]['damagetaken'] / $encounterDetails['duration']), 2,'.',',')."</td>";
	echo "				<td align=\"center\">".number_format($data_array[$playerName]['damagetaken'], 0,'.',',')."</td>";
	echo "			</tr>";
				}
	echo "	</table>";

	echo "	<div id=\"graphData\">";
				//if ( count($table_dps) > 0 ) {
	echo "			<table border=\"1\" id=\"tableDataDPS\" align=\"left\">";
	echo "				<tr>";
	echo "					<td>sTime</td>";
	echo "					<td>Damage</td>";
	echo "					<td>Total</td>";
	echo "					<td>DPS</td>";
	echo "				</tr>";

						$startTime 		= $saveTime;
						$standardTime 	= $saveStand;
						$rowCount 		= 0;
						$realtimeDPS 	= 0;
						$startTime 		= timeFormat($encounterDetails['starttime']);
						$counter 		= 0;
						$divider		= 0;

						for ($count = 0; $count < $encounterDetails['duration'] + 1; $count++) {
	echo "					<tr>";
								if ($count == 0 && $rowCount == 0) {
	echo "							<td align=\"center\">".$startTime."</td>";
								} else {
									$startTime = addSeconds($startTime, 1);
									$standardTime = raidFormat($standardTime, 1);
	echo "							<td align=\"center\">".$startTime."</td>";
								}

								$foundAbility = "NO";
								if ($rowCount < count($table_dps)) {
									if ($table_dps[$rowCount]['stime'] == $standardTime) {
										$foundAbility = "YES";
									}
								}

								$finalCount = 0;
								if ($count == ($encounterDetails['duration'])) {
									for ($remainder = $rowCount + 1; $remainder < count($table_dps); $remainder++) {
										$finalCount = $finalCount + number_format($table_dps[$remainder]['damage_total'], 0, '','');
									}
								}

								if ($foundAbility == "YES") {
									$counter = $counter + $table_dps[$rowCount]['damage_total'];

									if ($count == ($encounterDetails['duration'])) {
										$counter = $counter + $finalCount;
									}

									if (strtotime($startTime) - strtotime($saveTime) != 0) {
										$divider		= strtotime($startTime) - strtotime($saveTime);
										$realtimeDPS 	= $counter / ($divider + 0);
									} else {
										$realtimeDPS = $counter;
									}

	echo "							<td>".number_format($table_dps[$rowCount]['damage_total'], 0, '.', ',')."</td>";
	echo "							<td>".number_format($counter, 0, '.', ',')."</td>";
	echo "							<td>".number_format($realtimeDPS, 2, '.', '')."</td>";

									$rowCount++;
								} else if ($foundAbility == "NO") {
									if ($count == ($encounterDetails['duration'])) {
										$counter = $counter + $finalCount;
									}

									if (strtotime($startTime) - strtotime($saveTime) != 0) {
										$divider		= strtotime($startTime) - strtotime($saveTime);
										$realtimeDPS 	= $counter / ($divider + 0);
									} else {
										$realtimeDPS 	= $counter;
									}

	echo "							<td>0</td>";
	echo "							<td>".number_format($counter, 0, '.', ',')."</td>";
	echo "							<td>".number_format($realtimeDPS, 2, '.', '')."</td>";
								}
	echo "					</tr>";
						}
	echo "			</table>";
				//}

				//if ( count($table_hps) > 0 ) {
	echo "			<table border=\"1\" id=\"tableDataHPS\" align=\"left\">";
	echo "				<tr>";
	echo "					<td>sTime</td>";
	echo "					<td>Heal</td>";
	echo "					<td>Total</td>";
	echo "					<td>HPS</td>";
	echo "				</tr>";

						$startTime 		= $saveTime;
						$standardTime 	= $saveStand;
						$rowCount 		= 0;
						$durationCount	= 0;
						$realtimeDPS 	= 0;
						$startTime 		= timeFormat($encounterDetails['starttime']);
						$counter 		= 0;
						$divider		= 0;

						for ($count = 0; $count < $encounterDetails['duration'] + 1; $count++) {
	echo "					<tr>";
								if ($count == 0 && $rowCount == 0) {
	echo "							<td align=\"center\">".$startTime."</td>";
								} else {
									$startTime = addSeconds($startTime, 1);
									$standardTime = raidFormat($standardTime, 1);
	echo "							<td align=\"center\">".$startTime."</td>";
								}

								$foundAbility = "NO";
								if ($rowCount < count($table_hps)) {
									if ($table_hps[$rowCount]['stime'] == $standardTime) {
										$foundAbility = "YES";
									}
								}

								$finalCount = 0;
								if ($count == ($encounterDetails['duration'])) {
									for ($remainder = $rowCount + 1; $remainder < count($table_hps); $remainder++) {
										$finalCount = $finalCount + number_format($table_hps[$remainder]['heal_total'], 0, '','');
									}

									if ( ($counter + $finalCount) != $encounterDetails['total_outgoing_healing'] && $foundAbility = "NO") {
										$finalCount = $finalCount + ($encounterDetails['total_outgoing_healing'] - ($counter + $finalCount));
									} else {
										$finalCount = $finalCount + ($encounterDetails['total_outgoing_healing'] - $counter);
									}
								}

								if ($foundAbility == "YES") {
									$counter = $counter + $table_hps[$rowCount]['heal_total'];

									if ($count == ($encounterDetails['duration'])) {
										$counter = $counter + $finalCount;
									}

									if (strtotime($startTime) - strtotime($saveTime) != 0) {
										$divider		= strtotime($startTime) - strtotime($saveTime);
										$realtimeDPS 	= $counter / ($divider + 0);
									} else {
										$realtimeDPS = $counter;
									}

	echo "							<td>".number_format($table_hps[$rowCount]['heal_total'], 0, '.', ',')."</td>";
	echo "							<td>".number_format($counter, 0, '.', ',')."</td>";
	echo "							<td>".number_format($realtimeDPS, 2, '.', '')."</td>";

									$rowCount++;
								} else if ($foundAbility == "NO") {
									if ($count == ($encounterDetails['duration'])) {
										$counter = $counter + $finalCount;
									}

									if (strtotime($startTime) - strtotime($saveTime) != 0) {
										$divider		= strtotime($startTime) - strtotime($saveTime);
										$realtimeDPS 	= $counter / ($divider + 0);
									} else {
										$realtimeDPS 	= $counter;
									}

	echo "							<td>0</td>";
	echo "							<td>".number_format($counter, 0, '.', ',')."</td>";
	echo "							<td>".number_format($realtimeDPS, 2, '.', '')."</td>";
								}
	echo "					</tr>";
						}
	echo "			</table>";
				//}

				//if ( count($table_inc) > 0 ) {
	echo "			<table border=\"1\" id=\"tableDataINC\" align=\"left\">";
	echo "				<tr>";
	echo "					<td>sTime</td>";
	echo "					<td>Damage</td>";
	echo "					<td>Total</td>";
	echo "					<td>iDPS</td>";
	echo "				</tr>";

						$startTime 		= $saveTime;
						$standardTime 	= $saveStand;
						$rowCount 		= 0;
						$realtimeDPS 	= 0;
						$startTime 		= timeFormat($encounterDetails['starttime']);
						$counter 		= 0;
						$divider		= 0;

						for ($count = 0; $count < $encounterDetails['duration'] + 1; $count++) {
	echo "					<tr>";
								if ($count == 0 && $rowCount == 0) {
	echo "							<td align=\"center\">".$startTime."</td>";
								} else {
									$startTime = addSeconds($startTime, 1);
									$standardTime = raidFormat($standardTime, 1);
	echo "							<td align=\"center\">".$startTime."</td>";
								}

								$foundAbility = "NO";
								if ($rowCount < count($table_inc)) {
									if ($table_inc[$rowCount]['stime'] == $standardTime) {
										$foundAbility = "YES";
									}
								}

								$finalCount = 0;
								if ($count == ($encounterDetails['duration'])) {
									for ($remainder = $rowCount + 1; $remainder < count($table_inc); $remainder++) {
										$finalCount = $finalCount + number_format($table_inc[$remainder]['damage_total'], 0, '','');
									}
								}

								if ($foundAbility == "YES") {
									$counter = $counter + $table_inc[$rowCount]['damage_total'] + $finalCount;

									if ($count == ($encounterDetails['duration'])) {
										$counter = $counter + $finalCount;
									}

									if (strtotime($startTime) - strtotime($saveTime) != 0) {
										$divider		= strtotime($startTime) - strtotime($saveTime);
										$realtimeDPS 	= $counter / ($divider + 0);
									} else {
										$realtimeDPS = $counter;
									}

	echo "							<td>".number_format($table_inc[$rowCount]['damage_total'], 0, '.', ',')."</td>";
	echo "							<td>".number_format($counter, 0, '.', ',')."</td>";
	echo "							<td>".number_format($realtimeDPS, 2, '.', '')."</td>";

									$rowCount++;
								} else if ($foundAbility == "NO") {
									if ($count == ($encounterDetails['duration'])) {
										$counter = $counter + $finalCount;
									}

									if (strtotime($startTime) - strtotime($saveTime) != 0) {
										$divider		= strtotime($startTime) - strtotime($saveTime);
										$realtimeDPS 	= $counter / ($divider + 0);
									} else {
										$realtimeDPS 	= $counter;
									}

	echo "							<td>0</td>";
	echo "							<td>".number_format($counter, 0, '.', ',')."</td>";
	echo "							<td>".number_format($realtimeDPS, 2, '.', '')."</td>";
								}
	echo "					</tr>";
						}
	echo "			</table>";
				//}
	echo "	</div>";

	mysql_close($link);
?>