<?php
	$filepath 			= dirname(dirname(dirname(__FILE__)));
	include_once 			$filepath."/config/nav.php";

	ob_start( 'load_time' );

	$encid 						= $_GET['enc'];
	$mobName					= $_GET['mob'];
	$displayType					= $_GET['type'];
	$abilityNames					= "(attacktype='";
	$playerNames					= "(attacker='";
	$query_swing_mob_victim_string			= "(victim='";
	$query_swing_player_victim_string		= "(victim='";
	$query_swing_mob_attacker_string		= "(attacker='";
	$query_player_string 				= "(attacker='";
	$query_mob_attacker_string			= "(attacker='";
	$query_player_victim_string			= "(victim='";
	$query_ability_string 				= "(type='";
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
	$query_ability_string 				= "(type='";
	$query_player_string 				= "(attacker='";
	$curAbil 					= "";
	$prevAbil 					= "";
	$numOfMobs					= 0;
	$numOfPlayers 					= 0;
	$numOfAbilities					= 0;
	$damageCounter 					= 0;
	$columnCount 					= 0;
	$standardTime 					= 0;
	$startTime 					= 0;
	$endTime 					= 0;
	$durationTime 					= 0;
	$durationTime 					= 0;
	$saveTime 					= 0;
	$saveStand 					= 0;
	$numOfTables					= 3;
	$numFilter 					= 0;
	$numFilterName					= 0;
	$abilityDetails_array 				= array();
	$abilityDetails_dps_array 			= array();
	$abilityDetails_hps_array 			= array();
	$abilityDetails_incdps_array 			= array();
	$abilityDetails_inchps_array 			= array();
	$playerDPS_array				= array();
	$playerHPS_array				= array();
	$playerINCDPS_array				= array();
	$playerINCHPS_array				= array();
	$playerName_array				= array();
	$listOfAbilities				= array();
	$listOfPlayers					= array();
	$mobs_array					= array();
	$ability_array 					= array();
	$player_array					= array();
	$swing_array					= array();
	$saveArray 					= array();
	$warrior_array					= array();
	$mage_array					= array();
	$cleric_array					= array();
	$rogue_array					= array();
	$allAbility_array				= array();

	if ( $_GET['abil'] != "" ) {
		array_push($listOfAbilities, $_GET['abil']);
		$abilityNames .= $_GET['abil']."')";
	} else if ( isset($_POST['filterCompareAbility']) ) {
		$listOfAbilities = $_POST['filterCompareAbility'];
		$abilityNames .= implode("' OR attacktype='", $_POST['filterCompareAbility'])."')";
	} else {
		$abilityNames = "";
	}

	if ( isset($_POST['compareName']) ) {
		$listOfPlayers = $_POST['compareName'];
		$playerNames .= implode("' OR attacker='", $_POST['compareName'])."')";
	} else if ( isset($_POST['filterCompareName']) ) {
		$listOfPlayers = $_POST['filterCompareName'];
		$playerNames .= implode("' OR attacker='", $_POST['filterCompareName'])."')";
	} else {
		$playerNames = "";
	}

	$numOfPlayers 				= count($listOfPlayers);
	$numOfAbilities				= count($listOfAbilities);
	$mobs_array 				= explode("_", $mobName);
	$numOfMobs				= count($mobs_array);
	
	if ($numOfPlayers > 0 && $numOfAbilities > 0) {
	//***************START-GET START AND END TIME OF ENCOUNTER IN STANDARD/DB FORMAT**************
	$encounterDetails	= mysql_fetch_array(get_encounter_details($encid));
	$standardTime 		= $encounterDetails['starttime'];
	$saveStand 		= $standardTime;
	$seconds 		= $encounterDetails['duration'];
	$startTime 		= timeFormat($encounterDetails['starttime']);
	$saveTime 		= $startTime;
	$endTimeTemp 		= raidFormat($startTime, $seconds);
	$endTime 		= timeFormat($endTimeTemp);
	$durationTime 		= seconds($startTime, $endTime);
	//***************END-GET START AND END TIME OF ENCOUNTER IN STANDARD/DB FORMAT**************
	
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

	//***************START-QUERY STRINGS**************
	$query_player_string 				.= implode("' OR attacker='", $listOfPlayers) . "')";
	
	$query_combatant_player_string 			.= implode("' OR name='", $listOfPlayers)."')";
	$query_combatant_nonplayer_string 		.= implode("' AND name!='", $listOfPlayers)."')";
	$query_swing_player_victim_string 		.= implode("' OR victim='", $listOfPlayers)."')";
	$query_swing_player_attacker_string 		.= implode("' OR attacker='", $listOfPlayers)."')";
	$query_swing_player_nonattacker_string 		.= implode("' AND attacker!='", $listOfPlayers)."')";

	$query_combatant_mob_attacker_string 		.= implode("' OR name='", $mobs_array)."')";
	$query_combatant_mob_nonattacker_string 	.= implode("' AND name!='", $mobs_array)."')";
	$query_swing_mob_victim_string			.= implode("' OR victim='", $mobs_array)."')";
	$query_swing_mob_attacker_string		.= implode("' OR attacker='", $mobs_array)."')";
	$query_swing_mob_nonattacker_string		.= implode("' AND attacker!='", $mobs_array)."')";
	
	$query_swing_mob_player_victim_string		.= implode("' OR victim='", $listOfPlayers) . "' OR victim='" . implode("' OR victim='", $mobs_array)."')";
	$query_swing_mob_player_attacker_string		.= implode("' OR attacker='", $listOfPlayers) . "' OR attacker='" . implode("' OR attacker='", $mobs_array)."')";
	//***************END-QUERY STRINGS**************
	
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
	
	$numOfPlayers 						= count($listOfPlayers);
	$numOfAbilities						= count($listOfAbilities);
	//***************END-GET DPS/HPS/INC DPS DATA**************

	//***************START-GET ALL SWINGS AND PLACE IN ARRAY**************
	$swing_query = "";
	if (in_array("All", $listOfAbilities) && $numOfPlayers > 0 && $numOfAbilities > 0) {
		if ( $displayType == "DPS" ) {
			$swing_query = get_abilityq_all_abilities_dps($encid, $playerNames, $query_swing_mob_victim_string);
		} else if ( $displayType == "HPS" ) {
			$swing_query = get_abilityq_all_abilities_hps($encid, $playerNames);
		} else if ( $displayType == "INCDPS" ) {
			$swing_query = get_abilityq_all_abilities_incdps($encid, $query_swing_player_victim_string, $query_swing_mob_attacker_string);
		} else if ( $displayType == "INCHPS" ) {
			$swing_query = get_abilityq_all_abilities_inchps($encid, $query_swing_player_victim_string);
		}
	} else if ($numOfPlayers > 0 && $numOfAbilities > 0) {
		if ( $displayType == "DPS" ) {
			$swing_query = get_abilityq_specific_abilities_dps($encid, $abilityNames, $playerNames, $query_swing_mob_victim_string);
		} else if ( $displayType == "HPS" ) {
			$swing_query = get_abilityq_specific_abilities_hps($encid, $abilityNames, $playerNames);
		} else if ( $displayType == "INCDPS" ) {
			$swing_query = get_abilityq_specific_abilities_incdps($encid, $abilityNames, $query_swing_player_victim_string, $query_swing_mob_attacker_string);
		} else if ( $displayType == "INCHPS" ) {
			$swing_query = get_abilityq_specific_abilities_inchps($encid, $abilityNames, $query_swing_player_victim_string);
		}
	}

	$curPlayer 	= "";
	$arrayLoc	= 0;

	if ( $displayType != "INCDPS" && $displayType != "INCHPS" ) {
		while ($row = mysql_fetch_array($swing_query, MYSQL_ASSOC)) {
			if ( $curPlayer == $row['attacker'] ) {
				if ( $row['stime'] == $swing_array[$arrayLoc]['stime'] ) {
					if ( !(isset($swing_array[$arrayLoc]['numOfAbilities'])) ) {
						$swing_array[$arrayLoc]['numOfAbilities'] = 2;
					} else {
						$swing_array[$arrayLoc]['numOfAbilities'] = $swing_array[$arrayLoc]['numOfAbilities'] + 1;
					}

					if ( !(isset($swing_array[$arrayLoc]['extraAbility'])) ) {
						$swing_array[$arrayLoc]['extraAbility'] = $swing_array[$arrayLoc]['attacktype'] . "|" . $row['attacktype'];
					} else {
						$swing_array[$arrayLoc]['extraAbility'] = $swing_array[$arrayLoc]['extraAbility'] . "|" . $row['attacktype'];
					}

					if ( !(isset($swing_array[$arrayLoc]['extraDamage'])) ) {
						$swing_array[$arrayLoc]['extraDamage'] = $swing_array[$arrayLoc]['damage'] . "|" . $row['damage'];
					} else {
						$swing_array[$arrayLoc]['extraDamage'] 	= $swing_array[$arrayLoc]['extraDamage'] . "|" . $row['damage'];
					}

					$swing_array[$arrayLoc]['damage'] = $swing_array[$arrayLoc]['damage'] + $row['damage'];
				} else {
					$row['numOfAbilities'] = 1;
					array_push($swing_array, $row);
					$arrayLoc++;
				}

			} else if ( $curPlayer != $row['attacker'] ) {

				if ( $curPlayer != "") {
					$ability_array[$curPlayer] 	= $swing_array;
					$swing_array 				= array();
					$arrayLoc					= 0;

					$row['numOfAbilities'] = 1;
					array_push($swing_array, $row);
				} else {
					$row['numOfAbilities'] = 1;
					array_push($swing_array, $row);
				}

				$curPlayer = $row['attacker'];
			}
			
			if ( count($allAbility_array) > 0 ) {
				if ( !(in_array($row['attacktype'], $allAbility_array)) ) {
					array_push($allAbility_array, $row['attacktype']);
				}
			} else {
				array_push($allAbility_array, $row['attacktype']);
			}
		}

		$ability_array[$curPlayer] 	= $swing_array;
	} else if ( $displayType == "INCDPS" || $displayType == "INCHPS" ) {
		while ($row = mysql_fetch_array($swing_query, MYSQL_ASSOC)) {
			if ( $curPlayer == $row['victim'] ) {
				if ( $row['stime'] == $swing_array[$arrayLoc]['stime'] ) {
					if ( !(isset($swing_array[$arrayLoc]['numOfAbilities'])) ) {
						$swing_array[$arrayLoc]['numOfAbilities'] = 2;
					} else {
						$swing_array[$arrayLoc]['numOfAbilities'] = $swing_array[$arrayLoc]['numOfAbilities'] + 1;
					}

					if ( !(isset($swing_array[$arrayLoc]['extraAbility'])) ) {
						$swing_array[$arrayLoc]['extraAbility'] = $swing_array[$arrayLoc]['attacktype'] . "|" . $row['attacktype'];
					} else {
						$swing_array[$arrayLoc]['extraAbility'] = $swing_array[$arrayLoc]['extraAbility'] . "|" . $row['attacktype'];
					}

					if ( !(isset($swing_array[$arrayLoc]['extraDamage'])) ) {
						$swing_array[$arrayLoc]['extraDamage'] = $swing_array[$arrayLoc]['damage'] . "|" . $row['damage'];
					} else {
						$swing_array[$arrayLoc]['extraDamage'] 	= $swing_array[$arrayLoc]['extraDamage'] . "|" . $row['damage'];
					}

					$swing_array[$arrayLoc]['damage'] = $swing_array[$arrayLoc]['damage'] + $row['damage'];
				} else {
					$row['numOfAbilities'] = 1;
					array_push($swing_array, $row);
					$arrayLoc++;
				}

			} else if ( $curPlayer != $row['victim'] ) {

				if ( $curPlayer != "") {
					$ability_array[$curPlayer] 	= $swing_array;
					$swing_array 				= array();
					$arrayLoc					= 0;

					$row['numOfAbilities'] = 1;
					array_push($swing_array, $row);
				} else {
					$row['numOfAbilities'] = 1;
					array_push($swing_array, $row);
				}

				$curPlayer = $row['victim'];
			}
			
			if ( count($allAbility_array) > 0 ) {
				if ( !(in_array($row['attacktype'], $allAbility_array)) ) {
					array_push($allAbility_array, $row['attacktype']);
				}
			} else {
				array_push($allAbility_array, $row['attacktype']);
			}
		}

		$ability_array[$curPlayer] 	= $swing_array;
	}

	$saveArray 		= $ability_array;
	$query_ability_string 	.= implode("' OR type='", $allAbility_array). "')";
	//***************END-GET ALL SWINGS AND PLACE IN ARRAY**************
	
	//***************START-GET DATA PER ABILITY PER PLAYER**************
	if ($displayType == "DPS") {
		$ability_query = get_p2p_ability_data_dps_all($encid, $query_player_string, $query_swing_mob_victim_string);
		while ($row = mysql_fetch_array($ability_query)) {
			$row['attacktype'] = "All";

			if ( !(isset($abilityDetails_dps_array[$row['attacker']])) ) {
				$abilityDetails_dps_array[$row['attacker']] = array();
			}

			array_push($abilityDetails_dps_array[$row['attacker']], $row);

			$playerDPS_array[$row['attacker']] = $row['damage_total'] / $encounterDetails['duration'];
			$playerDPS_array[$row['attacker']."-duration"] = $row['duration'];
		}

		$ability_query = get_p2p_ability_data_dps_abilities($encid, $query_player_string, $query_swing_mob_victim_string);
		while ($row = mysql_fetch_array($ability_query)) {
			array_push($abilityDetails_dps_array[$row['attacker']], $row);
		}

		array_push($abilityDetails_array, $abilityDetails_dps_array);
	} else if ($displayType == "HPS") {

		$ability_query = get_p2p_ability_data_hps_all($encid, $query_player_string);
		while ($row = mysql_fetch_array($ability_query)) {
			$row['attacktype'] = "All";

			if ( !(isset($abilityDetails_hps_array[$row['attacker']])) ) {
				$abilityDetails_hps_array[$row['attacker']] = array();
			}

			array_push($abilityDetails_hps_array[$row['attacker']], $row);

			$playerHPS_array[$row['attacker']] = $row['damage_total'] / $encounterDetails['duration'];
			$playerHPS_array[$row['attacker']."-duration"] = $row['duration'];
		}

		$ability_query = get_p2p_ability_data_hps_abilities($encid, $query_player_string);
		while ($row = mysql_fetch_array($ability_query)) {
			array_push($abilityDetails_hps_array[$row['attacker']], $row);
		}

		array_push($abilityDetails_array, $abilityDetails_hps_array);
	} else if ($displayType == "INCDPS") {
		$ability_query = get_p2p_ability_data_incdps_all($encid, $query_swing_mob_attacker_string, $query_swing_player_victim_string);
		while ($row = mysql_fetch_array($ability_query)) {
			$row['damage_total'] 	= $row['damageCorrect'];
			$row['attacker'] 		= $row['victim'];
			$row['attacktype'] 		= "All";
			
			if ( !(isset($abilityDetails_incdps_array[$row['attacker']])) ) {
				$abilityDetails_incdps_array[$row['victim']] = array();
			}
			
			array_push($abilityDetails_incdps_array[$row['victim']], $row);

			$playerINCDPS_array[$row['victim']] 			= $row['damage_total'] / $encounterDetails['duration'];
			$playerINCDPS_array[$row['victim']."-duration"] = $row['duration'];
		}

		$ability_query = get_p2p_ability_data_incdps_abilities($encid, $query_swing_mob_attacker_string, $query_swing_player_victim_string);
		while ($row = mysql_fetch_array($ability_query)) {
			$row['damage_total'] 	= $row['damageCorrect'];
			$row['attacker'] 		= $row['victim'];
			
			array_push($abilityDetails_incdps_array[$row['victim']], $row);
		}

		array_push($abilityDetails_array, $abilityDetails_incdps_array);
	} else if ($displayType == "INCHPS") {
		$ability_query = get_p2p_ability_data_inchps_all($encid, $query_player_victim_string);
		while ($row = mysql_fetch_array($ability_query)) {
			$row['attacker'] 		= $row['victim'];
			$row['attacktype'] 		= "All";
			
			if ( !(isset($abilityDetails_inchps_array[$row['attacker']])) ) {
				$abilityDetails_inchps_array[$row['victim']] = array();
			}
			
			array_push($abilityDetails_inchps_array[$row['victim']], $row);

			$playerINCHPS_array[$row['victim']] 			= $row['damage_total'] / $encounterDetails['duration'];
			$playerINCHPS_array[$row['victim']."-duration"] = $row['duration'];
		}

		$ability_query = get_p2p_ability_data_inchps_abilities($encid, $query_player_victim_string);
		while ($row = mysql_fetch_array($ability_query)) {
			$row['attacker'] = $row['victim'];
			array_push($abilityDetails_inchps_array[$row['victim']], $row);
		}

		array_push($abilityDetails_array,$abilityDetails_inchps_array);
	}
	//***************END-GET DATA PER ABILITY PER PLAYER**************

	echo "<html>";
	echo "	<head>";
	echo "		<title>Trinity :: Online DPS Parser</title>";
	echo "		<script type=\"text/javascript\" src=\"abilityq.js\"></script>";
	echo "	</head>";
	echo "	<body onLoad=\"pageLoad()\">";
	echo "		<table align=\"center\" width=\"1024\" border=\"1\">";
	echo "			<tr>";
	echo "				<td align=\"right\" valign=\"top\">";
	echo "					<table align=\"right\" id=\"detailsTable\" border=\"1\" width=\"100%\">";
	echo "						<tr>";
	echo "							<td colspan=\"2\" align=\"center\"><b>Encounter Details</b></td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Encounter ID: </td>";
	echo "							<td align=\"left\">".$encounterDetails['encid']."</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Name: </td>";
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
	echo "					<table align=\"center\" border=\"1\" width=\"100%\">";
	echo "						<tr>";
	echo "							<td align=\"center\"><b>Data Type</b></td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\">";
									if ( $displayType == "DPS" ) {
	echo "									Outgoing Damage Per Second";
									} else if ( $displayType == "HPS" ) {
	echo "									Outgoing Healing Per Second";								
									} else if ( $displayType == "INCDPS" ) {
	echo "									Incoming Damage Per Second";								
									} else if ( $displayType == "INCHPS" ) {
	echo "									Incoming Healing Per Second";								
									}
	echo "							</td>";
	echo "						</tr>";
	echo "					</table>";
	echo "				</td>";
	echo "				<td valign=\"top\">";
	echo "					<table align=\"center\" border=\"1\" width=\"100%\">";
	echo "						<tr>";
	echo "							<td colspan=\"".$numOfPlayers."\" align=\"center\"><b>Players</b></td>";
	echo "						</tr>";
	echo "						<tr>";
								$numPerRow = $numOfPlayers;
								if ( $numOfPlayers > 5)
									$numPerRow = 1;
								for ($count = 0; $count < $numOfPlayers; $count++) {
									$numFilterName++;
									if ($numFilterName % 2 == 0) {
	echo "									<td align=\"center\">".$listOfPlayers[$count]."</td>";
	echo "									</tr>";
	echo "									<tr>";
									} else {
	echo "									<td align=\"center\">".$listOfPlayers[$count]."</td>";
									}
								}
	echo "						</tr>";
	echo "					</table>";
	echo "					<table align=\"center\" border=\"1\" width=\"100%\">";
	echo "						<tr>";
	echo "							<td colspan=\"2\" align=\"center\"><b>Mob Data</b></td>";
	echo "						</tr>";
	echo "						<tr>";
								for ($count = 0; $count < $numOfMobs; $count++) {
									$numFilter++;
									if ($numFilter % 2 == 0) {
	echo "									<td align=\"center\">".$mobs_array[$count]."</td>";
	echo "									</tr>";
	echo "									<tr>";
									} else {
	echo "									<td align=\"center\">".$mobs_array[$count]."</td>";
									}
								}
	echo "					</table>";
	echo "				</td>";
	echo "			</tr>";
	echo "		</table>";
	echo "		<table id=\"filterTable\" align=\"center\" border=\"1\" width=\"1024\">";
	echo "			<tr>";
	echo "				<td colspan=\"10\" align=\"center\"><b>Abilities</b></td>";
	echo "			</tr>";
					$numFilter = 0;
					for ($count = 0; $count < count($allAbility_array); $count++) {
						if ($allAbility_array[$count] != $prevAbil) {
							$curAbil = $allAbility_array[$count];
							$numFilter++;
							if ($numFilter % 6 == 0) {
	echo "							<td align=\"center\">".stripslashes($curAbil)."</td>";
	echo "							</tr>";
	echo "							<tr>";
							} else {
	echo "							<td align=\"center\">".stripslashes($curAbil)."</td>";
							}
						}
					}
	echo "			</tr>";
	echo "		</table>";
	echo "			<div align=\"center\" id=\"container\" style=\"width: 1000px; height: 300px; margin: 0 auto\"></div>";
	echo "			<table border=\"0\" align=\"center\">";
	echo "				<tr>";
						for ($player = 0; $player < $numOfPlayers; $player++) {
							if ($player % $numOfTables == 0) {
	echo "							</tr>";
	echo "							<tr>";
							}
	echo "						<td valign=\"top\">";
	echo "							<div align=\"center\" id=\"chart".$listOfPlayers[$player]."\"></div>";
	echo "							<table border=\"1\">";
	echo "								<tr>";
	echo "									<td><b><a href=\"".$PAGE_PLAYER."?enc=".$encid."&player=".$listOfPlayers[$player]."\">".$listOfPlayers[$player]."</a></b></td>";
	echo "								</tr>";
	echo "							</table>";
	echo "							<table border=\"1\" class=\"sortable\" id=\"".$listOfPlayers[$player]."\">";
	echo "								<tr>";
										if ( $displayType == "DPS" ) {
	echo "										<td align=\"center\">Time</td>";
	echo "										<td align=\"center\">Ability</td>";
	echo "										<td align=\"center\">#</td>";
	echo "										<td align=\"center\">Damage</td>";
	echo "										<td align=\"center\">Total</td>";
	echo "										<td align=\"center\">DPS</td>";
										} else if ( $displayType == "HPS" ) {
	echo "										<td align=\"center\">Time</td>";
	echo "										<td align=\"center\">Ability</td>";
	echo "										<td align=\"center\">#</td>";
	echo "										<td align=\"center\">Heal</td>";
	echo "										<td align=\"center\">Total</td>";
	echo "										<td align=\"center\">HPS</td>";
										} else if ( $displayType == "INCDPS" ) {
	echo "										<td align=\"center\">Time</td>";
	echo "										<td align=\"center\">Ability</td>";
	echo "										<td align=\"center\">#</td>";
	echo "										<td align=\"center\">Damage</td>";
	echo "										<td align=\"center\">Total</td>";
	echo "										<td align=\"center\">iDPS</td>";
										} else if ( $displayType == "INCHPS" ) {
	echo "										<td align=\"center\">Time</td>";
	echo "										<td align=\"center\">Ability</td>";
	echo "										<td align=\"center\">#</td>";
	echo "										<td align=\"center\">Heal</td>";
	echo "										<td align=\"center\">Total</td>";
	echo "										<td align=\"center\">iHPS</td>";
										}
	echo "								</tr>";
										$startTime 	= $saveTime;
										$standardTime 	= $saveStand;
										$rowCount 	= 0;
										$currentAbility	= 0;
										$damageCounter 	= 0;
										$realtimeDPS 	= 0;
										$abilityInfo	= array();

										for ($count = 0; $count < $durationTime + 1; $count++) {
	echo "									<tr>";
												if ($count == 0 && $rowCount == 0) {
	echo "											<td align=\"center\">".$startTime."</td>";
												} else {
													$startTime = addSeconds($startTime, 1);
													$standardTime = raidFormat($standardTime, 1);
	echo "											<td align=\"center\">".$startTime."</td>";
												}

												$foundAbility 		= "NO";
												$multipleAbility	= "NO";
												$abilityList		= array();
												$abilityDamage		= array();
												$multiString		= "";
												$finalCount 		= 0;

												if ( $currentAbility < count($ability_array[$listOfPlayers[$player]]) ) {
													$abilityInfo	= $ability_array[$listOfPlayers[$player]][$currentAbility];
												}

												if ( $abilityInfo['stime'] == $standardTime) {
													$foundAbility = "YES";

													if ( isset($ability_array[$listOfPlayers[$player]][$currentAbility]['extraAbility']) ) {
														$abilityList 	= explode("|", $ability_array[$listOfPlayers[$player]][$currentAbility]['extraAbility']);
														$abilityDamage 	= explode("|", $ability_array[$listOfPlayers[$player]][$currentAbility]['extraDamage']);

														for ($multi = 0; $multi < $ability_array[$listOfPlayers[$player]][$currentAbility]['numOfAbilities']; $multi++) {
															$multiString .= $abilityList[$multi]. "-" . $abilityDamage[$multi] . "\n";
														}
													}
												}

												if ( $count == $durationTime ) {
													if ( $currentAbility < count($ability_array[$listOfPlayers[$player]]) ) {
														for ($countAgain = $currentAbility + 1; $countAgain < count($ability_array[$listOfPlayers[$player]]); $countAgain++) {
															if ( isset($ability_array[$listOfPlayers[$player]][$countAgain]['extraAbility']) ) {
																$abilityList 	= explode("|", $ability_array[$listOfPlayers[$player]][$countAgain]['extraAbility']);
																$abilityDamage 	= explode("|", $ability_array[$listOfPlayers[$player]][$countAgain]['extraDamage']);

																for ($multi = 0; $multi < $ability_array[$listOfPlayers[$player]][$countAgain]['numOfAbilities']; $multi++) {
																	$multiString .= $abilityList[$multi]. "-" . $abilityDamage[$multi] . "\n";

																	$finalCount = $finalCount + $abilityDamage[$multi];
																}
															} else {
																$finalCount = $finalCount + $ability_array[$listOfPlayers[$player]][$countAgain]['damage'];
															}
														}
													}

													$abilityInfo['damage'] = $abilityInfo['damage'] + $finalCount;
												}

												if ($foundAbility == "YES") {
													$higherDamage = 0;

													$damageCounter = $damageCounter + $abilityInfo['damage'];
													$higherDamage = $higherDamage + $abilityInfo['damage'];

													if (strtotime($startTime) - strtotime($saveTime) != 0) {
														$realtimeDPS = $damageCounter / (strtotime($startTime) - strtotime($saveTime));
													} else {
														$realtimeDPS = $damageCounter;
													}

	echo "											<td align=\"center\" title=\"".$multiString."\">".$abilityInfo['attacktype']."</td>";
	echo "											<td align=\"center\">".$ability_array[$listOfPlayers[$player]][$currentAbility]['numOfAbilities']."</td>";
	echo "											<td align=\"center\">".number_format($higherDamage, 0,'',',')."</td>";
	echo "											<td align=\"center\">".number_format($damageCounter, 0,'',',')."</td>";
	echo "											<td align=\"center\">".number_format($realtimeDPS, 2,'.','')."</td>";
	echo "											</tr>";

													$currentAbility++;
												} else if ($foundAbility == "NO") {
													if (strtotime($startTime) - strtotime($saveTime) != 0) {
														$realtimeDPS = $damageCounter / (strtotime($startTime) - strtotime($saveTime));
													} else {
														$realtimeDPS = $damageCounter;
													}

	echo "											<td align=\"center\">-</td>";
	echo "											<td align=\"center\">-</td>";
	echo "											<td align=\"center\">-</td>";
	echo "											<td align=\"center\">-</td>";
	echo "											<td align=\"center\">".number_format($realtimeDPS, 2,'.','')."</td>";
	echo "											</tr>";
												}

											$rowCount = $rowCount + 1;
										}
	echo "						</table>";
	echo "					</td>";
					}
	echo "			</tr>";
	echo "		</table>";
			
			for ($count = 0; $count < $numOfPlayers; $count++) {
	echo "			<table id=\"graph".$listOfPlayers[$count]."\" border=\"1\" align=\"center\">";
	echo "				<tr>";
	echo "					<td align=\"center\">Ability</td>";
	echo "					<td align=\"center\">Name</td>";
	echo "					<td align=\"center\">DPS</td>";
	echo "					<td align=\"center\">Pct</td>";
	echo "				</tr>";
					for ($ability = 0; $ability < count($abilityDetails_array[0][$listOfPlayers[$count]]); $ability++) {								
						$abilityDPS 	= $abilityDetails_array[0][$listOfPlayers[$count]][$ability]['damage_total'] / $encounterDetails['duration'];
						$dps_value 	= ($abilityDPS / ($abilityDetails_array[0][$listOfPlayers[$count]][0]['damage_total'] / $encounterDetails['duration'])) * 100;
	
	echo "					<tr>";
	echo "						<td align=\"left\">".$abilityDetails_array[0][$listOfPlayers[$count]][$ability]['attacker']."</a></td>";
							
							if ( $ability == 0 ) {
	echo "							<td align=\"center\">Rest of Abilities</td>";
							} else {
	echo "							<td align=\"center\">".$abilityDetails_array[0][$listOfPlayers[$count]][$ability]['attacktype']. "</td>";
							}
	echo "						<td align=\"center\">".number_format($abilityDPS, 2, '.', ',') . "</td>";
	echo "						<td align=\"center\">".number_format($dps_value, 2, '.', ',') . "</td>";
	echo "					</tr>";
	
						$currentRow = 0;
						$prevAbil = $curAbil;
					}
	echo "			</table>";
			}
			
		} else {
	echo "		<center>Please select a player and ability to use for comparison in the Ability Que.<center>";
		}
	echo " 		<center><b>This page rendered in {microtime} seconds</b></center>";
	echo "	</body>";
	echo "</html>";

	mysql_close($link);
?>