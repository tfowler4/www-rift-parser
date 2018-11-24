<?php
	$filepath 			= dirname(dirname(dirname(__FILE__)));
	include_once 		$filepath."/config/nav.php";

	ob_start( 'load_time' );

	$encid = '';

	if (isset($_POST["names"])) {
		$encid = $_POST["names"];
	} else if (isset($_GET['enc'])) {
		$encid = $_GET['enc'];
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
	$mobName_array					= array();
	$ranking_array					= array();
	$player_array					= array();
	$playerName_array				= array();
	$participant_array				= array();
	$warrior_array					= array();
	$rogue_array					= array();
	$cleric_array					= array();
	$mage_array					= array();
	$encounterDetails				= array();
	$data_array					= array();
	$combatant_array				= array();
	$mobName					= "";
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
		}
	}

	$temp_array = array_merge((array)$warrior_array, (array)$rogue_array, (array)$cleric_array, (array)$mage_array);
	//***************END-PLACE PLAYERS INTO CLASS ARRAY**************

	//***************START-GET LIST OF MOBS**************
	$mob_query = get_all_mobs($encid);
	while ($row = mysql_fetch_array($mob_query)) {
		if ( ($row['damage'] > 0 || $row['damagetaken']) && $row['name'] != "Unknown") {
			array_push($mobs_array, $row);
			array_push($mobName_array, addslashes($row['name']));
		}
	}
	//***************END-GET LIST OF MOBS**************

	//***************START-QUERY STRINGS**************
	$query_combatant_player_string 			.= implode("' OR name='", $temp_array)."')";
	$query_combatant_nonplayer_string 		.= implode("' AND name!='", $temp_array)."')";
	$query_swing_player_victim_string 		.= implode("' OR victim='", $temp_array)."')";
	$query_swing_player_attacker_string 		.= implode("' OR attacker='", $temp_array)."')";
	$query_swing_player_nonattacker_string 		.= implode("' AND attacker!='", $temp_array)."')";

	$query_combatant_mob_attacker_string 		.= implode("' OR name='", $mobName_array)."')";
	$query_combatant_mob_nonattacker_string 	.= implode("' AND name!='", $mobName_array)."')";
	$query_swing_mob_victim_string			.= implode("' OR victim='", $mobName_array)."')";
	$query_swing_mob_attacker_string		.= implode("' OR attacker='", $mobName_array)."')";
	$query_swing_mob_nonattacker_string		.= implode("' AND attacker!='", $mobName_array)."')";
	
	$query_swing_mob_player_victim_string		.= implode("' OR victim='", $temp_array) . "' OR victim='" . implode("' OR victim='", $mobName_array)."')";
	$query_swing_mob_player_attacker_string		.= implode("' OR attacker='", $temp_array) . "' OR attacker='" . implode("' OR attacker='", $mobName_array)."')";
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
	
	//$data_dps_query = get_encounter_dps_table($encid, $query_swing_mob_nonattacker_string);
	//while ($row = mysql_fetch_array($data_dps_query)) {
	//	array_push($table_dps, $row);
	//}

	//$data_hps_query = get_encounter_hps_table($encid, $query_swing_mob_nonattacker_string);
	//while ($row = mysql_fetch_array($data_hps_query)) {
	//	array_push($table_hps, $row);
	//}

	//$data_idps_query = get_encounter_idps_table($encid, $query_swing_player_victim_string);
	//while ($row = mysql_fetch_array($data_idps_query)) {
	//	array_push($table_inc, $row);
	//}
	//***************END-GET DPS/HPS/INC GRAPH DATA**************

	//***************START-GET PLAYER DETAILS FROM ENCOUNTER**************	
	$data_dps_query = get_encounter_specific_dps($encid, $query_swing_mob_victim_string, $query_swing_player_attacker_string);
	while ($row = mysql_fetch_array($data_dps_query)) {
		$data_array[$row['attacker']]['class'] 	= $participant_array[$row['attacker']];
		$data_array[$row['attacker']]['damage'] = $row['damage_total'];

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
		$data_array[$row['attacker']]['class'] 	= $participant_array[$row['attacker']];
		$data_array[$row['attacker']]['healed'] = $row['heal_total'];

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

	//***************START-GET DPS/HPS/INC DPS DATA**************
	$dps_query = get_encounter_combatant_dps($encid, $query_combatant_mob_nonattacker_string);
	$dps = mysql_fetch_array($dps_query);
	$encounterDetails['total_outgoing_damage'] 		= $dps['damage_total'];

	$hps_query = get_encounter_combatant_hps($encid, $query_combatant_player_string);
	$hps = mysql_fetch_array($hps_query);
	$encounterDetails['enchps'] 				= $hps['hps_total'];
	$encounterDetails['total_outgoing_healing'] 		= $hps['heal_total'];

	$inc_query = get_encounter_combatant_idps($encid, $query_combatant_player_string);
	$inc = mysql_fetch_array($inc_query);
	$encounterDetails['encidps'] 				= $inc['idps_total'] / $encounterDetails['duration'];
	$encounterDetails['total_incoming_damage'] 		= $inc['idps_total'];
	//***************END-GET DPS/HPS/INC DPS DATA**************

	//***************START-GET RANKING DETAILS**************
	$encounter_ranking_query = get_all_encounters_mob($encounterDetails['title']);
	while($row = mysql_fetch_array($encounter_ranking_query)) {
		array_push($encounters_array, $row);
		array_push($encounterIDs_array, $row['encid']);
	}

	$encounterIDs .= implode("' OR encid='", $encounterIDs_array)."')";

	$hps_query = mysql_query(sprintf("SELECT DISTINCT encid, SUM(enchps) As hps_total, SUM(damagetaken) As damage_total
										FROM combatant_table
										WHERE %s
										AND %s
										GROUP BY encid",
										$encounterIDs,
										$query_combatant_player_string)) or die(mysql_error());
	$temp_hps_array	= array();
	$temp_dps_array = array();
	while($row = mysql_fetch_array($hps_query)) {
			$temp_hps_array[$row['encid']] = $row['hps_total'];
			$temp_dps_array[$row['encid']] = $row['damage_total'];
	}

	$ranking_array = rankEncounters($encounters_array, $temp_hps_array, $temp_dps_array, $encid);
	//***************END-GET RANKING DETAILS**************
	
	//***************START-CLEANING EMPTY KEYS**************
	for ($count = 0; $count < $numOfPlayers; $count++) {
		$playerName = $playerName_array[$count];

		if ( !(isset($data_array[$playerName]['class'])) )
			$data_array[$playerName]['class'] = $participant_array[$playerName];
		if ( !(isset($data_array[$playerName]['hits'])) )
			$data_array[$playerName]['hits'] = 0;
		if ( !(isset($data_array[$playerName]['duration'])) )
			$data_array[$playerName]['duration'] = 0;
		if ( !(isset($data_array[$playerName]['damage'])) )
			$data_array[$playerName]['damage'] = 0;
		if ( !(isset($data_array[$playerName]['encdps'])) )
			$data_array[$playerName]['encdps'] = 0;
		if ( !(isset($data_array[$playerName]['enchps'])) )
			$data_array[$playerName]['enchps'] = 0;
		if ( !(isset($data_array[$playerName]['encidps'])) )
			$data_array[$playerName]['encidps'] = 0;
		if ( !(isset($data_array[$playerName]['healed'])) )
			$data_array[$playerName]['healed'] = 0;
		if ( !(isset($data_array[$playerName]['damagetaken'])) )
			$data_array[$playerName]['damagetaken'] = 0;
   	}
	//***************END-CLEANING EMPTY KEYS****************************

	echo "<html>";
	echo "	<head>";
	echo "		<title>Trinity :: Online DPS Parser</title>";
	echo "		<script type=\"text/javascript\" src=\"list.js\"></script>";
	echo "	</head>";
	echo "	<body onLoad=\"pageLoad()\">";
	echo "		<table width=\"1024\" align=\"center\" border=\"1\">";
	echo "		<tr><td></td></tr>";
	echo "		</table>";
	echo "		<table width=\"1024\" align=\"center\" border=\"1\">";
	echo "			<tr>";
	echo "				<td colspan=\"2\">";
	echo "					<div id=\"overallChart\" width=\"1020\" align=\"center\"></div>";
	echo "				</td>";
	echo "			</tr>";
	echo "			<tr>";
	echo "				<td valign=\"top\">";
	echo "					<table align=\"left\" id=\"detailsTable\" border=\"1\" width=\"350\">";
	echo "						<tr>";
	echo "							<td colspan=\"2\" align=\"center\"><b>Encounter Details</b></td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Encounter ID: </td>";
	echo "							<td align=\"left\">".$encounterDetails['encid']."</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Encounter Name: </td>";
	echo "							<td align=\"left\">".$encounterDetails['title']."</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Date: </td>";
	echo "							<td align=\"left\">".dateShortFormat($encounterDetails['starttime'])."</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Duration: </td>";
	echo "							<td align=\"left\">".minutes($encounterDetails['duration'])."</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>DPS Total: </td>";
	echo "							<td align=\"left\">".number_format($encounterDetails['encdps'], 2, '.', ',')."</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>HPS Total: </td>";
	echo "							<td align=\"left\">".number_format($encounterDetails['enchps'], 2, '.', ',')."</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>iDPS Total: </td>";
	echo "							<td align=\"left\">".number_format(($encounterDetails['total_incoming_damage'] / $encounterDetails['duration']), 2, '.', ',')."</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Parse Type: </td>";
	echo "							<td align=\"left\">".$encounterDetails['parseType']."</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Uploader: </td>";
	echo "							<td align=\"left\">".$encounterDetails['uploadedby']."</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Special Notes: </td>";
	echo "							<td align=\"left\"><textarea name=\"notes\" id=\"notes\" cols=\"20\" rows=\"2\" style=\"resize: none;\">".$encounterDetails['notes']."</textarea></td>";
	echo "						</tr>";
	echo "					</table>";
	echo "					<table align=\"center\" id=\"detailsTable\" border=\"1\" width=\"350\">";
	echo "						<tr>";
	echo "							<td colspan=\"2\" align=\"center\"><b>Performance Ranking</b></td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Time Ranking: </td>";
	echo "							<td align=\"left\">".toOrdinal($ranking_array[0])."</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>DPS Ranking: </td>";
	echo "							<td align=\"left\">".toOrdinal($ranking_array[1])."</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>HPS Ranking: </td>";
	echo "							<td align=\"left\">".toOrdinal($ranking_array[2])."</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Inc DPS Ranking: </td>";
	echo "							<td align=\"left\">".toOrdinal($ranking_array[3])."</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Overall Ranking: </td>";
	echo "							<td align=\"left\">".toOrdinal($ranking_array[4])."</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td colspan=\"2\" align=\"center\"><b>Mobs in Encounter</b></td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td colspan=\"2\" align=\"center\">";
	echo "								<form name=\"mobForm\" action=\"".$PAGE_ABILITYQ."?enc=".$encid."\" method=\"POST\">";
	echo "									<table id=\"mobTable\" align=\"center\" border=\"1\">";
	echo "										<tr>";
													$filterNum = 0;
													for ($count = 0; $count < count($mobs_array); $count++) {
														$filterNum++;
														if ($filterNum % 2 == 0) {
	echo "													<td><input type=\"checkbox\" onChange=\"changeMob()\" name=\"filterMobs[]\" checked=\"checked\" value=\"".$mobs_array[$count]['name']."\">".$mobs_array[$count]['name']."</td>";
	echo "													</tr>";
	echo "													<tr>";
														} else {
	echo "													<td><input type=\"checkbox\" onChange=\"changeMob()\" name=\"filterMobs[]\" checked=\"checked\" value=\"".$mobs_array[$count]['name']."\">".$mobs_array[$count]['name']."</td>";
														}
													}
	echo "										</tr>";
	echo "									</table>";
	echo "								</form>";
	echo "							</td>";
	echo "						</tr>";
	echo "					</table>";
	echo "						<form name=\"compareForm\" id =\"compareForm\" action=\"".$PAGE_P2P."?enc=".$encid."&mob=".$mobName."\" method=\"POST\">";
	echo "						<div id=\"panePlayers\">";
	echo "							<table border=\"1\" align=\"left\" class=\"sortable\" id=\"listTable\" width=\"700\">";
	echo "								<tr>";
	echo "									<td align=\"center\">Name</td>";
	echo "									<td align=\"center\">Class</td>";
	echo "									<td align=\"center\">Duration</td>";
	echo "									<td align=\"center\">Hits</td>";
	echo "									<td align=\"center\">DPS</td>";
	echo "									<td align=\"center\">Damage</td>";
	echo "									<td align=\"center\">HPS</td>";
	echo "									<td align=\"center\">Heals</td>";
	echo "									<td align=\"center\">iDPS</td>";
	echo "									<td align=\"center\">Inc Damage</td>";
	echo "								</tr>";
									for ($count = 0; $count < $numOfPlayers; $count++) {
										$playerName = $playerName_array[$count];
										
										if ( $data_array[$playerName]['duration'] > $encounterDetails['duration'] ) {
											$data_array[$playerName]['duration'] = $encounterDetails['duration'];
										}
	echo "									<tr>";
	echo "										<td align=\"left\"><input id=\"".$playerName."\" type=\"checkbox\" name=\"compare[]\" value=\"" .$playerName. "\" /><a href=\"".$PAGE_PLAYER."?enc=".$encid."&player=".$playerName."&mob=".$mobName."\">".$playerName."</a></td>";
	echo "										<td align=\"center\">".$data_array[$playerName]['class']."</td>";
	echo "										<td align=\"center\">".minutes($data_array[$playerName]['duration'])."</td>";
	echo "										<td align=\"center\">".number_format($data_array[$playerName]['hits'], 0,'.',',')."</td>";
	echo "										<td align=\"center\">".number_format(($data_array[$playerName]['damage'] / $encounterDetails['duration']), 2,'.',',')."</td>";
	echo "										<td align=\"center\">".number_format($data_array[$playerName]['damage'], 0,'',',')."</td>";
	echo "										<td align=\"center\">".number_format(($data_array[$playerName]['healed'] / $encounterDetails['duration']), 2,'.',',')."</td>";
	echo "										<td align=\"center\">".number_format($data_array[$playerName]['healed'], 0,'',',')."</td>";
	echo "										<td align=\"center\">".number_format(($data_array[$playerName]['damagetaken'] / $encounterDetails['duration']), 2,'.',',')."</td>";
	echo "										<td align=\"center\">".number_format($data_array[$playerName]['damagetaken'], 0,'.',',')."</td>";
	echo "									</tr>";
									}
	echo "							</table>";
	echo "							<div id=\"graphData\">";
									//	if ( count($table_dps) > 0 ) {
	echo "									<table border=\"1\" id=\"tableDataDPS\" align=\"left\">";
	echo "										<tr>";
	echo "											<td>sTime</td>";
	echo "											<td>Damage</td>";
	echo "											<td>Total</td>";
	echo "											<td>DPS</td>";
	echo "										</tr>";

												$startTime 		= $saveTime;
												$standardTime 	= $saveStand;
												$rowCount 		= 0;
												$realtimeDPS 	= 0;
												$startTime 		= timeFormat($encounterDetails['starttime']);
												$counter 		= 0;
												$divider		= 0;

												for ($count = 0; $count < $encounterDetails['duration'] + 1; $count++) {
	echo "											<tr>";
														if ($count == 0 && $rowCount == 0) {
	echo "													<td align=\"center\">".$startTime."</td>";
														} else {
															$startTime = addSeconds($startTime, 1);
															$standardTime = raidFormat($standardTime, 1);
	echo "													<td align=\"center\">".$startTime."</td>";
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

															if ( ($counter + $finalCount) != $encounterDetails['total_outgoing_damage'] && $foundAbility = "NO") {
																$finalCount = $finalCount + ($encounterDetails['total_outgoing_damage'] - ($counter + $finalCount));
															} else {
																$finalCount = $finalCount + ($encounterDetails['total_outgoing_damage'] - $counter);
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

	echo "													<td>".number_format($table_dps[$rowCount]['damage_total'], 0, '.', ',')."</td>";
	echo "													<td>".number_format($counter, 0, '.', ',')."</td>";
	echo "													<td>".number_format($realtimeDPS, 2, '.', '')."</td>";

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

	echo "													<td>0</td>";
	echo "													<td>".number_format($counter, 0, '.', ',')."</td>";
	echo "													<td>".number_format($realtimeDPS, 2, '.', '')."</td>";
														}
	echo "											</tr>";
												}
	echo "									</table>";
									//	}

									//	if ( count($table_hps) > 0 ) {
	echo "									<table border=\"1\" id=\"tableDataHPS\" align=\"left\">";
	echo "										<tr>";
	echo "											<td>sTime</td>";
	echo "											<td>Heal</td>";
	echo "											<td>Total</td>";
	echo "											<td>HPS</td>";
	echo "										</tr>";

												$startTime 		= $saveTime;
												$standardTime 	= $saveStand;
												$rowCount 		= 0;
												$durationCount	= 0;
												$realtimeDPS 	= 0;
												$startTime 		= timeFormat($encounterDetails['starttime']);
												$counter 		= 0;
												$divider		= 0;

												for ($count = 0; $count < $encounterDetails['duration'] + 1; $count++) {
	echo "											<tr>";
														if ($count == 0 && $rowCount == 0) {
	echo "													<td align=\"center\">".$startTime."</td>";
														} else {
															$startTime = addSeconds($startTime, 1);
															$standardTime = raidFormat($standardTime, 1);
	echo "													<td align=\"center\">".$startTime."</td>";
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

	echo "													<td>".number_format($table_hps[$rowCount]['heal_total'], 0, '.', ',')."</td>";
	echo "													<td>".number_format($counter, 0, '.', ',')."</td>";
	echo "													<td>".number_format($realtimeDPS, 2, '.', '')."</td>";

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

	echo "													<td>0</td>";
	echo "													<td>".number_format($counter, 0, '.', ',')."</td>";
	echo "													<td>".number_format($realtimeDPS, 2, '.', '')."</td>";
														}
	echo "											</tr>";
												}
	echo "									</table>";
								//		}

									//	if ( count($table_inc) > 0 ) {
	echo "									<table border=\"1\" id=\"tableDataINC\" align=\"left\">";
	echo "										<tr>";
	echo "											<td>sTime</td>";
	echo "											<td>Damage</td>";
	echo "											<td>Total</td>";
	echo "											<td>iDPS</td>";
	echo "										</tr>";

												$startTime 		= $saveTime;
												$standardTime 	= $saveStand;
												$rowCount 		= 0;
												$realtimeDPS 	= 0;
												$startTime 		= timeFormat($encounterDetails['starttime']);
												$counter 		= 0;
												$divider		= 0;

												for ($count = 0; $count < $encounterDetails['duration'] + 1; $count++) {
	echo "											<tr>";
														if ($count == 0 && $rowCount == 0) {
	echo "													<td align=\"center\">".$startTime."</td>";
														} else {
															$startTime = addSeconds($startTime, 1);
															$standardTime = raidFormat($standardTime, 1);
	echo "													<td align=\"center\">".$startTime."</td>";
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

															if ( ($counter + $finalCount) != $encounterDetails['total_incoming_damage'] && $foundAbility = "NO") {
																$finalCount = $finalCount + ($encounterDetails['total_incoming_damage'] - ($counter + $finalCount));
															} else {
																$finalCount = $finalCount + ($encounterDetails['total_incoming_damage'] - $counter);
															}
														}

														if ($foundAbility == "YES") {
															$counter = $counter + $table_inc[$rowCount]['damage_total'];

															if ($count == ($encounterDetails['duration'])) {
																$counter = $counter + $finalCount;
															}

															if (strtotime($startTime) - strtotime($saveTime) != 0) {
																$divider		= strtotime($startTime) - strtotime($saveTime);
																$realtimeDPS 	= $counter / ($divider + 0);
															} else {
																$realtimeDPS = $counter;
															}

	echo "													<td>".number_format($table_inc[$rowCount]['damage_total'], 0, '.', ',')."</td>";
	echo "													<td>".number_format($counter, 0, '.', ',')."</td>";
	echo "													<td>".number_format($realtimeDPS, 2, '.', '')."</td>";

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

	echo "													<td>0</td>";
	echo "													<td>".number_format($counter, 0, '.', ',')."</td>";
	echo "													<td>".number_format($realtimeDPS, 2, '.', '')."</td>";
														}
	echo "											</tr>";
												}
	echo "									</table>";
									//	}
	echo "							</div>";
	echo "						</div>";
	echo "						<table align=\"left\" border=\"0\" width=\"700\">";
	echo "							<tr>";
	echo "								<td align=\"center\"><input id=\"Warrior\" type=\"button\" onClick=\"compareClass(this.id)\" value=\"Compare All Warriors\"></td>";
	echo "								<td align=\"center\"><input id=\"Rogue\" type=\"button\" onClick=\"compareClass(this.id)\" value=\"Compare All Rogues\"></td>";
	echo "								<td align=\"center\"><input id=\"Mage\" type=\"button\" onClick=\"compareClass(this.id)\" value=\"Compare All Mages\"></td>";
	echo "								<td align=\"center\"><input id=\"Cleric\" type=\"button\" onClick=\"compareClass(this.id)\" value=\"Compare All Clerics\"></td>";
	echo "							</tr>";
	echo "							<tr>";
	echo "								<td align=\"center\" colspan=\"4\"><input id=\"btnCompare\" type=\"button\"  onClick=\"comparePlayers()\" value=\"Compare Selected Players\"></td>";
	echo "							</tr>";
	echo "						</table>";
	echo "					</form>";
	echo "				</td>";
	echo "				<td align=\"center\" valign=\"top\" width=\"400\">";
	echo "					<div id=\"containerDPS\" align=\"center\" style=\"height:250px\";></div>";
	echo "					<div id=\"containerHPS\" align=\"center\" style=\"height:250px\";></div>";
	echo "					<div id=\"containerIDPS\" align=\"center\" style=\"height:250px\";></div>";
	echo "				</td>";
	echo "			</tr>";
	echo "		</table>";

	echo " 		<center><b>This page rendered in {microtime} seconds</b></center>";
	echo "	</body>";
	echo "</html>";

	mysql_close($link);
?>