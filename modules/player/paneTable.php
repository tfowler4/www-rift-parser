<?php

	$filepath 		= dirname(dirname(dirname(__FILE__)));
	include_once 		$filepath."/config/nav.php";

	$encid 							= $_GET['enc'];
	$displayType						= $_GET['type'];
	$player							= $_GET['player'];
	$playerClass						= $_GET['class'];
	$count_query 						= "";
	$sqlPlayer 						= "";
	$playerNames						= "(name='";
	$attackerNames						= "(attacker!='";
	$query_combatant_player_string				= "(name='" .$player . "')" ;
	$query_combatant_nonplayer_string			= "(name!='" .$player . "')" ;
	$query_combatant_mob_attacker_string			= "(name='";
	$query_combatant_mob_nonattacker_string			= "(name!='";
	$query_swing_player_victim_string			= "(victim='" .$player . "')" ;
	$query_swing_mob_victim_string				= "(victim='";
	$query_swing_player_attacker_string			= "(attacker='" .$player . "')" ;
	$query_swing_player_nonattacker_string			= "(attacker!='" .$player . "')" ;
	$query_swing_mob_attacker_string			= "(attacker='";
	$query_swing_mob_nonattacker_string			= "(attacker!='";
	$numOfPlayers						= 0;
	$damageCounter 						= 0;
	$columnCount 						= 0;
	$standardTime 						= 0;
	$startTime 						= 0;
	$endTime 						= 0;
	$durationTime 						= 0;
	$dps_total						= 0;
	$hps_total						= 0;
	$uptime_total						= 0;
	$numOfAbilities						= 0;
	$rankNum						= 0;
	$player_dps						= 0;
	$player_hps						= 0;
	$player_idps						= 0;
	$ability_array 						= array();
	$ability_heal_array 					= array();
	$dps_abilityq_array 					= array();
	$hps_abilityq_array 					= array();
	$inc_abilityq_array					= array();
	$rank_array						= array();
	$rank_player_array 					= array();
	$players_array						= array();
	$hps_ability_flag					= array();
	$mobName_array 						= array();
	$mobs_array						= array();

	//***************START-GET ENCOUNTER DETAILS**************
	$encounter_query 	= get_encounter_details($encid);
	$encounterDetails 	= mysql_fetch_array($encounter_query);

	$standardTime 		= $encounterDetails['starttime'];
	$saveStand 		= $standardTime;

	$seconds 		= $encounterDetails['duration'];

	$startTime 		= timeFormat($encounterDetails['starttime']);
	$saveTime 		= $startTime;

	$endTimeTemp 		= raidFormat($startTime, $seconds);
	$endTime 		= timeFormat($endTimeTemp);

	$durationTime 		= seconds($startTime, $endTime);
	//***************END-GET ENCOUNTER DETAILS**************
	
	//***************START-GET LIST OF PARTICIPANTS**************
	
	$player_query = mysql_query(sprintf("SELECT DISTINCT *
						FROM players_table"
						)) or die(mysql_error());
	while ($row = mysql_fetch_array($player_query)) {
		if ($row['name'] == $player) {
			$playerClass = $row['class'];
		}
		array_push($players_array, $row['name']);
	}

	$playerNames 	.= implode("' OR name='", $players_array)."')";
	$attackerNames 	.= implode("' AND attacker!='", $players_array)."')";
	//***************END-GET LIST OF PARTICIPANTS**************
	
	//***************START-QUERY STRINGS**************
	$mob_query = get_all_mobs($encid);
	while ($row = mysql_fetch_array($mob_query)) {
		if ( ($row['damage'] > 0 || $row['damagetaken']) && $row['name'] != "Unknown") {
			array_push($mobs_array, $row);
			array_push($mobName_array, addslashes($row['name']));
		}
	}
	
	$query_combatant_mob_attacker_string 		.= implode("' OR name='", $mobName_array)."')";
	$query_combatant_mob_nonattacker_string 	.= implode("' AND name!='", $mobName_array)."')";
	$query_swing_mob_victim_string			.= implode("' OR victim='", $mobName_array)."')";
	$query_swing_mob_attacker_string		.= implode("' OR attacker='", $mobName_array)."')";
	$query_swing_mob_nonattacker_string		.= implode("' AND attacker!='", $mobName_array)."')";
	//***************END-QUERY STRINGS**************

	//***************START-GET DPS/HPS/INC DPS DATA**************
	$dps_query = get_encounter_combatant_dps($encid, $query_combatant_player_string);
	$dps = mysql_fetch_array($dps_query);
	$encounterDetails['encdps'] 				= $dps['dps_total'];
	$encounterDetails['total_outgoing_damage'] 		= $dps['damage_total'];

	$hps_query = get_encounter_combatant_hps($encid, $query_combatant_player_string);
	$hps = mysql_fetch_array($hps_query);
	$encounterDetails['enchps'] 				= $hps['hps_total'];
	$encounterDetails['total_outgoing_healing'] 		= $hps['heal_total'];

	$idps_query = get_encounter_combatant_idps($encid, $query_combatant_player_string);
	$inc = mysql_fetch_array($idps_query);
	$encounterDetails['encidps'] 				= $inc['idps_total'] / $encounterDetails['duration'];
	$encounterDetails['total_incoming_damage'] 		= $inc['idps_total'];
	
	$ihps_query = get_encounter_combatant_ihps($encid, $query_combatant_player_string);
	$inc = mysql_fetch_array($ihps_query);
	$encounterDetails['encihps'] 				= $inc['ihps_total'] / $encounterDetails['duration'];
	$encounterDetails['total_incoming_heal'] 		= $inc['ihps_total'];
	//***************END-GET DPS/HPS/INC DPS DATA**************

	//***************START-GET SWINGS MADE IN ENCOUNTER**************
	$curPlayer 	= "";
	$arrayLoc	= 0;
	$swing_query	= "";

	if ( $displayType == "DPS" ) {
		$swing_query = get_abilityq_all_abilities_dps($encid, $query_swing_player_attacker_string, $query_swing_mob_victim_string);
	} else if ( $displayType == "HPS" ) {
		$swing_query = get_abilityq_all_abilities_hps($encid, $query_swing_player_attacker_string);
	} else if ( $displayType == "iDPS" ) {
		$swing_query = get_abilityq_all_abilities_incdps($encid, $query_swing_player_victim_string, $query_swing_mob_attacker_string);
	} else if ( $displayType == "iHPS" ) {
		$swing_query = get_abilityq_all_abilities_inchps($encid, $query_swing_player_victim_string);
	}

	if ( $displayType != "iDPS" && $displayType != "iHPS" ) {
		while ($row = mysql_fetch_array($swing_query, MYSQL_ASSOC)) {
			if ( $curPlayer == $row['attacker'] ) {
				if ( $row['stime'] == $dps_abilityq_array[$arrayLoc]['stime'] ) {
					if ( !(isset($dps_abilityq_array[$arrayLoc]['numOfAbilities'])) ) {
						$dps_abilityq_array[$arrayLoc]['numOfAbilities'] = 2;
					} else {
						$dps_abilityq_array[$arrayLoc]['numOfAbilities'] = $dps_abilityq_array[$arrayLoc]['numOfAbilities'] + 1;
					}

					if ( !(isset($dps_abilityq_array[$arrayLoc]['extraAbility'])) ) {
						$dps_abilityq_array[$arrayLoc]['extraAbility'] = $dps_abilityq_array[$arrayLoc]['attacktype'] . "|" . $row['attacktype'];
					} else {
						$dps_abilityq_array[$arrayLoc]['extraAbility'] = $dps_abilityq_array[$arrayLoc]['extraAbility'] . "|" . $row['attacktype'];
					}

					if ( !(isset($dps_abilityq_array[$arrayLoc]['extraDamage'])) ) {
						$dps_abilityq_array[$arrayLoc]['extraDamage'] = $dps_abilityq_array[$arrayLoc]['damage'] . "|" . $row['damage'];
					} else {
						$dps_abilityq_array[$arrayLoc]['extraDamage'] 	= $dps_abilityq_array[$arrayLoc]['extraDamage'] . "|" . $row['damage'];
					}
					
					if ( !(isset($dps_abilityq_array[$arrayLoc]['extraPlayer'])) ) {
						$dps_abilityq_array[$arrayLoc]['extraPlayer'] = $dps_abilityq_array[$arrayLoc]['victim'] . "|" . $row['victim'];
					} else {
						$dps_abilityq_array[$arrayLoc]['extraPlayer'] 	= $dps_abilityq_array[$arrayLoc]['extraPlayer'] . "|" . $row['victim'];
					}

					$dps_abilityq_array[$arrayLoc]['damage'] = $dps_abilityq_array[$arrayLoc]['damage'] + $row['damage'];
				} else {
					$row['numOfAbilities'] = 1;
					array_push($dps_abilityq_array, $row);
					$arrayLoc++;
				}

			} else if ( $curPlayer != $row['attacker'] ) {

				if ( $curPlayer != "") {
					$arrayLoc		= 0;
					$row['numOfAbilities'] 	= 1;
					array_push($dps_abilityq_array, $row);
				} else {
					$row['numOfAbilities'] 	= 1;
					array_push($dps_abilityq_array, $row);
				}

				$curPlayer = $row['attacker'];
			}
		}
	} else if ( $displayType == "iDPS" || $displayType == "iHPS" ) {
		while ($row = mysql_fetch_array($swing_query, MYSQL_ASSOC)) {
			if ( $curPlayer == $row['victim'] ) {
				if ( $row['stime'] == $dps_abilityq_array[$arrayLoc]['stime'] ) {
					if ( !(isset($dps_abilityq_array[$arrayLoc]['numOfAbilities'])) ) {
						$dps_abilityq_array[$arrayLoc]['numOfAbilities'] = 2;
					} else {
						$dps_abilityq_array[$arrayLoc]['numOfAbilities'] = $dps_abilityq_array[$arrayLoc]['numOfAbilities'] + 1;
					}

					if ( !(isset($dps_abilityq_array[$arrayLoc]['extraAbility'])) ) {
						$dps_abilityq_array[$arrayLoc]['extraAbility'] = $dps_abilityq_array[$arrayLoc]['attacktype'] . "|" . $row['attacktype'];
					} else {
						$dps_abilityq_array[$arrayLoc]['extraAbility'] = $dps_abilityq_array[$arrayLoc]['extraAbility'] . "|" . $row['attacktype'];
					}

					if ( !(isset($dps_abilityq_array[$arrayLoc]['extraDamage'])) ) {
						$dps_abilityq_array[$arrayLoc]['extraDamage'] = $dps_abilityq_array[$arrayLoc]['damage'] . "|" . $row['damage'];
					} else {
						$dps_abilityq_array[$arrayLoc]['extraDamage'] 	= $dps_abilityq_array[$arrayLoc]['extraDamage'] . "|" . $row['damage'];
					}
					
					if ( !(isset($dps_abilityq_array[$arrayLoc]['extraPlayer'])) ) {
						$dps_abilityq_array[$arrayLoc]['extraPlayer'] = $dps_abilityq_array[$arrayLoc]['attacker'] . "|" . $row['attacker'];
					} else {
						$dps_abilityq_array[$arrayLoc]['extraPlayer'] 	= $dps_abilityq_array[$arrayLoc]['extraPlayer'] . "|" . $row['attacker'];
					}

					$dps_abilityq_array[$arrayLoc]['damage'] = $dps_abilityq_array[$arrayLoc]['damage'] + $row['damage'];
				} else {
					$row['numOfAbilities'] = 1;
					array_push($dps_abilityq_array, $row);
					$arrayLoc++;
				}

			} else if ( $curPlayer != $row['victim'] ) {

				if ( $curPlayer != "") {
					$arrayLoc		= 0;
					$row['numOfAbilities'] 	= 1;
					array_push($dps_abilityq_array, $row);
				} else {
					$row['numOfAbilities'] 	= 1;
					array_push($dps_abilityq_array, $row);
				}

				$curPlayer = $row['victim'];
			}
		}
	}

	//***************END-GET SWINGS MADE IN ENCOUNTER**************

	//***************START-GET LIST OF ABILITIES USED IN ENCOUNTER**************
	$ability_query = "";
	
	if ( $displayType == "DPS" ) {
		$ability_query = get_player_dps_abilities($encid, $query_swing_player_attacker_string, $query_swing_mob_victim_string);
	} else if ( $displayType == "HPS" ) {
		$ability_query = get_player_hps_abilities($encid, $query_swing_player_attacker_string);
	} else if ( $displayType == "iDPS" ) {
		$ability_query = get_player_idps_abilities($encid, $query_swing_player_victim_string, $query_swing_mob_attacker_string);
	} else if ( $displayType == "iHPS" ) {
		$ability_query = get_player_ihps_abilities($encid, $query_swing_player_victim_string);
	}
	
	while($row = mysql_fetch_array($ability_query)) {
		array_push($ability_array, $row);
	}

	$numOfAbilities = count($ability_array);
	//***************END-GET LIST OF ABILITIES USED IN ENCOUNTER**************

	$durationTime 	= seconds($startTime, $endTime);
	$saveTime 	= $startTime;
	$saveStand 	= $standardTime;
	$saveArray 	= $ability_array;

	echo "	<table border=\"1\" align=\"center\" class=\"sortable\" id=\"abilityTable\" width=\"1024\">";
	echo "		<tr>";
				if ( $displayType == "DPS" ) {
	echo "				<td align=\"center\">Ability Name</td>";
	echo "				<td align=\"center\">Duration</td>";
	echo "				<td align=\"center\">Damage</td>";
	echo "				<td align=\"center\">DPS</td>";
	echo "				<td align=\"center\">Percentage</td>";
	echo "				<td align=\"center\">Hits</td>";
	echo "				<td align=\"center\">Crits</td>";
	echo "				<td align=\"center\">Min Hit</td>";
	echo "				<td align=\"center\">Avg Hit</td>";
	echo "				<td align=\"center\">Max Hit</td>";
				} else if ( $displayType == "HPS" ) {
					$encounterDetails['encdps'] = $encounterDetails['enchps'];
	echo "				<td align=\"center\">Ability Name</td>";
	echo "				<td align=\"center\">Duration</td>";
	echo "				<td align=\"center\">Heal</td>";
	echo "				<td align=\"center\">HPS</td>";
	echo "				<td align=\"center\">Percentage</td>";
	echo "				<td align=\"center\">Hits</td>";
	echo "				<td align=\"center\">Crits</td>";
	echo "				<td align=\"center\">Min Hit</td>";
	echo "				<td align=\"center\">Avg Hit</td>";
	echo "				<td align=\"center\">Max Hit</td>";
				} else if ( $displayType == "iDPS" ) {
					$encounterDetails['encdps'] = $encounterDetails['encidps'];
	echo "				<td align=\"center\">Ability Name</td>";
	echo "				<td align=\"center\">Duration</td>";
	echo "				<td align=\"center\">Incoming Damage</td>";
	echo "				<td align=\"center\">iDPS</td>";
	echo "				<td align=\"center\">Percentage</td>";
	echo "				<td align=\"center\">Hits</td>";
	echo "				<td align=\"center\">Crits</td>";
	echo "				<td align=\"center\">Min Hit</td>";
	echo "				<td align=\"center\">Avg Hit</td>";
	echo "				<td align=\"center\">Max Hit</td>";
				} else if ( $displayType == "iHPS" ) {
					$encounterDetails['encdps'] = $encounterDetails['encihps'];
	echo "				<td align=\"center\">Ability Name</td>";
	echo "				<td align=\"center\">Duration</td>";
	echo "				<td align=\"center\">Incoming Heal</td>";
	echo "				<td align=\"center\">iHPS</td>";
	echo "				<td align=\"center\">Percentage</td>";
	echo "				<td align=\"center\">Hits</td>";
	echo "				<td align=\"center\">Crits</td>";
	echo "				<td align=\"center\">Min Hit</td>";
	echo "				<td align=\"center\">Avg Hit</td>";
	echo "				<td align=\"center\">Max Hit</td>";
				}
	echo "		</tr>";
			$totalValues 			= "";
			$totalValues['attacktype'] 	= "Total";
			$totalValues['duration'] 	= 0;
			$totalValues['damage_total'] 	= 0;
			$totalValues['playerdps'] 	= 0;
			$totalValues['percentage'] 	= 0;
			$totalValues['hits'] 		= 0;
			$totalValues['crithits'] 	= 0;
			$totalValues['minhit'] 		= "";
			$totalValues['average'] 	= 0;
			$totalValues['maxhit'] 		= "";

			for ($count = 0; $count < $numOfAbilities; $count++) {
	echo "			<tr>";
	echo "				<td align=\"center\">".$ability_array[$count]['attacktype']."</td>";
	echo "				<td align=\"center\">".minutes($ability_array[$count]['duration'])."</td>";
	echo "				<td align=\"center\">".number_format($ability_array[$count]['damage_total'], 0,'',',')."</td>";
	echo "				<td align=\"center\">".number_format(($ability_array[$count]['damage_total']/$encounterDetails['duration']), 2,'.','')."</td>";
	echo "				<td align=\"center\">".number_format(((($ability_array[$count]['damage_total']/$encounterDetails['duration']) / $encounterDetails['encdps'] ) * 100), 2,'.','')."%</td>";
	echo "				<td align=\"center\">".number_format($ability_array[$count]['hits'], 0,'',',')."</td>";
	echo "				<td align=\"center\">".number_format($ability_array[$count]['crithits'], 0,'',',')."</td>";
	echo "				<td align=\"center\">".number_format($ability_array[$count]['minhit'], 0,'',',')."</td>";
	echo "				<td align=\"center\">".number_format($ability_array[$count]['average'], 2,'.',',')."</td>";
	echo "				<td align=\"center\">".number_format($ability_array[$count]['maxhit'], 0,'',',')."</td>";
	echo "			</tr>";
				$totalValues['attacktype'] 	= "Total";
				$totalValues['duration'] 	+= $ability_array[$count]['duration'];
				$totalValues['damage_total'] 	+= $ability_array[$count]['damage_total'];
				$totalValues['playerdps'] 	+= ($ability_array[$count]['damage_total']/$encounterDetails['duration']);
				$totalValues['percentage'] 	+= (($ability_array[$count]['damage_total']/$encounterDetails['duration']) / $encounterDetails['encdps'] ) * 100;
				$totalValues['hits'] 		+= $ability_array[$count]['hits'];
				$totalValues['crithits'] 	+= $ability_array[$count]['crithits'];
				$totalValues['average'] 	+= $ability_array[$count]['average'];
				
				if ( $totalValues['minhit'] == "") {
					$totalValues['minhit'] = $ability_array[$count]['minhit'];
				} else {
					$totalValues['minhit'] = min($totalValues['minhit'], $ability_array[$count]['minhit']);
				}
								
				if ( $totalValues['maxhit'] == "") {
					$totalValues['maxhit'] = $ability_array[$count]['maxhit'];
				} else {
					$totalValues['maxhit'] = max($totalValues['maxhit'], $ability_array[$count]['maxhit']);
				}
			}
			
			if ( $totalValues['minhit'] == "") {
				$totalValues['minhit'] = 0;
			}
			if ( $totalValues['maxhit'] == "") {
				$totalValues['maxhit'] = 0;
			}
			
	echo "		<tr>";
	echo "			<td align=\"center\"><b>".$totalValues['attacktype']."</b></td>";
	echo "			<td align=\"center\"><b>".minutes($totalValues['duration'])."</b></td>";
	echo "			<td align=\"center\"><b>".number_format($totalValues['damage_total'], 0,'',',')."</b></td>";
	echo "			<td align=\"center\"><b>".number_format($totalValues['playerdps'], 2,'.','')."</b></td>";
	echo "			<td align=\"center\"><b>".number_format($totalValues['percentage'], 2,'.','')."%</b></td>";
	echo "			<td align=\"center\"><b>".number_format($totalValues['hits'], 0,'',',')."</b></td>";
	echo "			<td align=\"center\"><b>".number_format($totalValues['crithits'], 0,'',',')."</b></td>";
	echo "			<td align=\"center\"><b>".number_format($totalValues['minhit'], 0,'',',')."</b></td>";
				
				if ( $totalValues['hits'] > 0 ) {
	echo "				<td align=\"center\"><b>".number_format(($totalValues['damage_total']/$totalValues['hits']), 2,'.',',')."</b></td>";
				} else {
	echo "				<td align=\"center\"><b>".number_format(0, 2,'.',',')."</b></td>";		
				}
	echo "			<td align=\"center\"><b>".number_format($totalValues['maxhit'], 0,'',',')."</b></td>";
	echo "		</tr>";
	echo "		</table>";
	echo "		<table border=\"1\" align=\"center\" width=\"1024\">";
	echo "			<tr>";
	echo "				<td>";
	echo "					<div align=\"left\" id=\"container3\" style=\"width: 1000px; height: 400px; margin: 0 auto\"></div>";
	echo "				</td>";
	echo "			</tr>";
	echo "		</table>";
	echo "		<table border=\"1\" align=\"center\" class=\"sortable\" id=\"timeTable\" width=\"1024\">";
	echo "			<tr>";
					if ( $displayType == "DPS" ) {
	echo "					<td align=\"center\">Time</td>";
	echo "					<td align=\"center\">Ability</td>";
	echo "					<td align=\"center\">#</td>"; // bgcolor=\"#488AC7\"
	echo "					<td align=\"center\">Victim</td>";
	echo "					<td align=\"center\">Damage</td>";
	echo "					<td align=\"center\">Total</td>";
	echo "					<td align=\"center\">DPS</td>";			
					} else if ( $displayType == "HPS" ) {
	echo "					<td align=\"center\">Time</td>";
	echo "					<td align=\"center\">Ability</td>";
	echo "					<td align=\"center\">#</td>"; // bgcolor=\"#3E8A21\"
	echo "					<td align=\"center\">Target</td>";
	echo "					<td align=\"center\" >Heal</td>";
	echo "					<td align=\"center\" >Total</td>";
	echo "					<td align=\"center\" >HPS</td>";		
					} else if ( $displayType == "iDPS" ) {
	echo "					<td align=\"center\">Time</td>";
	echo "					<td align=\"center\">Ability</td>";
	echo "					<td align=\"center\">#</td>"; // bgcolor=\"#E66C2C\"
	echo "					<td align=\"center\">Attacker</td>";
	echo "					<td align=\"center\" >Incoming Damage</td>";
	echo "					<td align=\"center\" >Total</td>";
	echo "					<td align=\"center\" >iDPS</td>";	
					} else if ( $displayType == "iHPS" ) {
	echo "					<td align=\"center\">Time</td>";
	echo "					<td align=\"center\">Ability</td>";
	echo "					<td align=\"center\">#</td>"; // bgcolor=\"#E66C2C\"
	echo "					<td align=\"center\">Healer</td>";
	echo "					<td align=\"center\" >Incoming Heal</td>";
	echo "					<td align=\"center\" >Total</td>";
	echo "					<td align=\"center\" >iHPS</td>";				
					}
	echo "			</tr>";

				$startTime 	= $saveTime;
				$standardTime 	= $saveStand;
				$rowCount 	= 0;
				$currentAbility	= 0;
				$damageCounter 	= 0;
				$realtimeDPS 	= 0;
				$abilityInfo	= array();
				
				if ( $totalValues['hits'] > 0 ) {
				for ($count = 0; $count < $durationTime + 1; $count++) {
	echo "				<tr>";
						if ($count == 0 && $rowCount == 0) {
	echo "						<td align=\"center\">".$startTime."</td>";
						} else {
							$startTime = addSeconds($startTime, 1);
							$standardTime = raidFormat($standardTime, 1);
	echo "						<td align=\"center\">".$startTime."</td>";
						}

						$foundAbility 		= "NO";
						$multipleAbility	= "NO";
						$abilityList		= array();
						$abilityDamage		= array();
						$multiString		= "";
						$finalCount 		= 0;

						if ( $currentAbility < count($dps_abilityq_array) ) {
							$abilityInfo	= $dps_abilityq_array[$currentAbility];
						}

						if ( $abilityInfo['stime'] == $standardTime) {
							$foundAbility = "YES";

							if ( isset($dps_abilityq_array[$currentAbility]['extraAbility']) ) {
								$abilityList 	= explode("|", $dps_abilityq_array[$currentAbility]['extraAbility']);
								$abilityDamage 	= explode("|", $dps_abilityq_array[$currentAbility]['extraDamage']);
								$abilityPlayer 	= explode("|", $dps_abilityq_array[$currentAbility]['extraPlayer']);

								for ($multi = 0; $multi < $dps_abilityq_array[$currentAbility]['numOfAbilities']; $multi++) {
									$multiString .= $abilityPlayer[$multi]. "-" . $abilityList[$multi]. "-" . $abilityDamage[$multi] . "\n";
								}
							}
						}

						if ( $count == $durationTime ) {
							if ( $currentAbility < count($dps_abilityq_array) ) {
								for ($countAgain = $currentAbility + 1; $countAgain < count($dps_abilityq_array); $countAgain++) {
									if ( isset($dps_abilityq_array[$countAgain]['extraAbility']) ) {
										$abilityList 	= explode("|", $dps_abilityq_array[$countAgain]['extraAbility']);
										$abilityDamage 	= explode("|", $dps_abilityq_array[$countAgain]['extraDamage']);

										for ($multi = 0; $multi < $dps_abilityq_array[$countAgain]['numOfAbilities']; $multi++) {
											$multiString .= $abilityPlayer[$multi]. "-" . $abilityList[$multi]. "-" . $abilityDamage[$multi] . "\n";
											$finalCount = $finalCount + $abilityDamage[$multi];
										}
									} else {
										$finalCount = $finalCount + $dps_abilityq_array[$countAgain]['damage'];
									}
								}
							}

							$abilityInfo['damage'] = $abilityInfo['damage'] + $finalCount;
						}

						if ($foundAbility == "YES") {
							if ( $displayType == "iHPS" ) {
							$dps_abilityq_array[$currentAbility]['victim'] = $dps_abilityq_array[$currentAbility]['attacker'];
							}
							
							$higherDamage = 0;

							$damageCounter = $damageCounter + $abilityInfo['damage'];
							$higherDamage = $higherDamage + $abilityInfo['damage'];

							if (strtotime($startTime) - strtotime($saveTime) != 0) {
								$realtimeDPS = $damageCounter / (strtotime($startTime) - strtotime($saveTime));
							} else {
								$realtimeDPS = $damageCounter;
							}

	echo "						<td align=\"center\" title=\"".$multiString."\">".$abilityInfo['attacktype']."</td>";
	echo "						<td align=\"center\">".$dps_abilityq_array[$currentAbility]['numOfAbilities']."</td>";
	echo "						<td align=\"center\">".$dps_abilityq_array[$currentAbility]['victim']."</td>";
	echo "						<td align=\"center\">".number_format($higherDamage, 0,'',',')."</td>";
	echo "						<td align=\"center\">".number_format($damageCounter, 0,'',',')."</td>";
	echo "						<td align=\"center\">".number_format($realtimeDPS, 2,'.','')."</td>";
	echo "						</tr>";

							$currentAbility++;
						} else if ($foundAbility == "NO") {
							if (strtotime($startTime) - strtotime($saveTime) != 0) {
								$realtimeDPS = $damageCounter / (strtotime($startTime) - strtotime($saveTime));
							} else {
								$realtimeDPS = $damageCounter;
							}

	echo "						<td align=\"center\">-</td>";
	echo "						<td align=\"center\">-</td>";
	echo "						<td align=\"center\">-</td>";
	echo "						<td align=\"center\">-</td>";
	echo "						<td align=\"center\">-</td>";
	echo "						<td align=\"center\">".number_format($realtimeDPS, 2,'.','')."</td>";
	echo "						</tr>";
						}
					$rowCount = $rowCount + 1;
				}
				}
	echo "		</table>";
	
	mysql_close($link);
?>