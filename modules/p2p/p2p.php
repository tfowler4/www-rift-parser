<?php
	$filepath 			= dirname(dirname(dirname(__FILE__)));
	include_once 		$filepath."/config/nav.php";

	ob_start( 'load_time' );

	$encid 						= $_GET['enc'];
	$mobName					= $_GET['mob'];
	$displayType				= "";

	if ( isset($_GET['type']) ) {
		$displayType = $_GET['type'];
	} else {
		$displayType = "DPS";
	}

	$players 					= array();
	$playerName_string			= "";

	if ( isset($_GET['players']) ) {
		$players = explode("_", $_GET['players']);
	} else {
		$players = $_POST["compare"];
	}

	$playerName_string 				= $_GET['players'];
	$numOfPlayers 					= count($players);
	$displayType					= "DPS";
	$numOfAbilities 				= 0;
	$numOfMobs		 			= 0;
	$numFilter 					= 0;
	$numFilterName					= 0;
	$currentRow 					= 0;
	$player_array					= array();
	$playerName_array				= array();
	$ability_array 					= array();
	$abilityDetails_array 				= array();
	$abilityDetails_dps_array 			= array();
	$abilityDetails_hps_array 			= array();
	$abilityDetails_inc_array 			= array();
	$playerDPS_array				= array();
	$topValues 					= array();
	$mobs_array					= array();
	$warrior_array					= array();
	$mage_array					= array();
	$cleric_array					= array();
	$rogue_array					= array();
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
	$curAbil 					= "";
	$prevAbil 					= "";
	$idName						= '';
	$chartName					= '';

	//***************START-GET ENCOUNTER DETAILS**************
	$encounter_query 	= get_encounter_details($encid);
	$encounterDetails 	= mysql_fetch_array($encounter_query);
	//***************END-GET ENCOUNTER DETAILS**************

	//***************START-GET ABILITIES USED IN ENCOUNTER**************
	$mobs_array							= explode("_", $mobName);
	$numOfMobs							= count($mobs_array);
	$query_mob_victim_string			= "(victim='";
	$query_mob_victim_string			.= implode("' OR victim='", $mobs_array)."')";

	$query_ability_string 				.= implode("' OR type='", $ability_array). "')";
	$query_player_string 				.= implode("' OR attacker='", $players) . "')";
	$query_mob_attacker_string 			.= implode("' OR attacker='", $mobs_array) . "')";
	$query_player_victim_string			.= implode("' OR victim='", $players) . "')";

	array_push($ability_array, "All");

	$ability_query = get_p2p_ability_data_dps_abilities($encid, $query_player_string, $query_mob_victim_string);
	while ($row = mysql_fetch_array($ability_query)) {
		if ( !(in_array($row['attacktype'], $ability_array)) ) {
			array_push($ability_array, $row['attacktype']);
		}
	}

	$numOfAbilities = count($ability_array);
	//***************END-GET ABILITIES USED IN ENCOUNTER**************
	
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
	$query_combatant_player_string 			.= implode("' OR name='", $temp_array)."')";
	$query_combatant_nonplayer_string 		.= implode("' AND name!='", $temp_array)."')";
	$query_swing_player_victim_string 		.= implode("' OR victim='", $temp_array)."')";
	$query_swing_player_attacker_string 		.= implode("' OR attacker='", $temp_array)."')";
	$query_swing_player_nonattacker_string 		.= implode("' AND attacker!='", $temp_array)."')";

	$query_combatant_mob_attacker_string 		.= implode("' OR name='", $mobs_array)."')";
	$query_combatant_mob_nonattacker_string 	.= implode("' AND name!='", $mobs_array)."')";
	$query_swing_mob_victim_string			.= implode("' OR victim='", $mobs_array)."')";
	$query_swing_mob_attacker_string		.= implode("' OR attacker='", $mobs_array)."')";
	$query_swing_mob_nonattacker_string		.= implode("' AND attacker!='", $mobs_array)."')";
	
	$query_swing_mob_player_victim_string		.= implode("' OR victim='", $temp_array) . "' OR victim='" . implode("' OR victim='", $mobs_array)."')";
	$query_swing_mob_player_attacker_string		.= implode("' OR attacker='", $temp_array) . "' OR attacker='" . implode("' OR attacker='", $mobs_array)."')";
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
	//***************END-GET DPS/HPS/INC DPS DATA**************

	//***************START-GET DATA PER ABILITY PER PLAYER**************
	$ability_query = get_p2p_ability_data_dps_all($encid, $query_player_string, $query_mob_victim_string);
	while ($row = mysql_fetch_array($ability_query)) {
			$row['attacktype'] = "All";
			array_push($abilityDetails_array, $row);

			$playerDPS_array[$row['attacker']] = $row['damage_total'] / $encounterDetails['duration'];
			$playerDPS_array[$row['attacker']."-duration"] = $row['duration'];
	}

	$ability_query = get_p2p_ability_data_dps_abilities($encid, $query_player_string, $query_mob_victim_string);
	while ($row = mysql_fetch_array($ability_query)) {
			array_push($abilityDetails_array, $row);
	}
	
	$numOfPlayers = count($players);
	//***************END-GET DATA PER ABILITY PER PLAYER**************

	echo "<html>";
	echo "	<head>";
	echo "		<title>Trinity :: Online DPS Parser</title>";
	echo "		<script type=\"text/javascript\" src=\"p2p.js\"></script>";
	echo "	</head>";
	echo "	<body onLoad=\"pageLoad()\">";
	echo "		<table align=\"center\" width=\"1024\" border=\"1\">";
	echo "			<tr>";
	echo "				<td align=\"right\" valign=\"top\" width=\"30%\">";
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
	echo "						<tr>";
	echo "							<td colspan=\"2\" align=\"center\"><b>Mob Data</b></td>";
	echo "						</tr>";
								for ($count = 0; $count < $numOfMobs; $count++) {
									if ($numFilter % 2 == 0) {
	echo "									<td align=\"center\" colspan=\"2\">".$mobs_array[$count]."</td>";
	echo "									</tr>";
	echo "									<tr>";
									} else {
	echo "									<td align=\"center\" colspan=\"2\">".$mobs_array[$count]."</td>";
									}
								}
	echo "					</table>";
	echo "				</td>";
	echo "				<td align=\"left\" valign=\"top\" width=\"70%\">";
						$function_string = $encid.",".$mobName.",".$playerName_string;
	echo "					<table align=\"center\" border=\"1\" width=\"100%\">";
	echo "						<tr>";
	echo "							<td align=\"center\"><b>Data Type</b></td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\">";
	echo "								<input type=\"button\" id=\"DPS_".$function_string."\" onClick=\"updatePane(this.id)\" value=\"Outgoing Damage\">";
	echo "								<input type=\"button\" id=\"HPS_".$function_string."\" onClick=\"updatePane(this.id)\" value=\"Outgoing Healing\">";
	echo "								<input type=\"button\" id=\"INCDPS_".$function_string."\" onClick=\"updatePane(this.id)\" value=\"Incoming Damage\">";
	echo "								<input type=\"button\" id=\"INCHPS_".$function_string."\" onClick=\"updatePane(this.id)\" value=\"Incoming Healing\">";
	echo "							</td>";
	echo "						</tr>";
	echo "					</table>";
	echo "					<div id=\"paneFilter\">";
	echo "						<form name=\"filterForm\" action=\"".$PAGE_ABILITYQ."?type=".$displayType."&enc=".$encid."&abil=&mob=".$mobName."\" method=\"POST\">";
	echo "							<table id=\"filterNameTable\" align=\"center\" border=\"1\" width=\"100%\">";
	echo "								<tr>";
	echo "									<td colspan=\"".$numOfPlayers."\" align=\"center\"><b>Players</b></td>";
	echo "								</tr>";
	echo "								<tr>";
										$numPerRow = $numOfPlayers;
										if ( $numOfPlayers > 5)
											$numPerRow = 5;
										for ($count = 0; $count < $numOfPlayers; $count++) {
											$numFilterName++;
		
											if ($numFilterName % $numPerRow == 0) {
	echo "											<td align=\"center\"><input type=\"checkbox\" name=\"filterCompareName[]\" value=\"".$players[$count]."\">".$players[$count]."</td>";
	echo "											</tr>";
	echo "											<tr>";
											} else {
	echo "											<td align=\"center\"><input type=\"checkbox\" name=\"filterCompareName[]\" value=\"".$players[$count]."\">".$players[$count]."</td>";
											}
										}
	echo "								</tr>";
	echo "							</table>";
	echo "							<table id=\"filterTable\" align=\"center\" border=\"1\" width=\"100%\">";
	echo "								<tr>";
	echo "									<td colspan=\"10\" align=\"center\"><b>Ability Filter</b></td>";
	echo "								</tr>";
										$numFilter = 0;
										for ($count = 0; $count < $numOfAbilities; $count++) {
											if ($ability_array[$count] != $prevAbil) {
												$curAbil = $ability_array[$count];
												$numFilter++;
	
												if ($numFilter % 4 == 0) {
	echo "												<td><input type=\"checkbox\" name=\"filterCompareAbility[]\" value=\"".addslashes($curAbil)."\">".stripslashes($curAbil)."</td>";
	echo "												</tr>";
	echo "												<tr>";
												} else {
	echo "												<td><input type=\"checkbox\" name=\"filterCompareAbility[]\" value=\"".addslashes($curAbil)."\">".stripslashes($curAbil)."</td>";
												}
											}
										}
	echo "								</tr>";
	echo "							</table>";
	echo "							<center><input type=\"submit\" value=\"Generate Custom Ability Queue\"></center>";
	echo "						</form>";
	echo "					</div>";
	echo "				</td>";
	echo "			</tr>";
	echo "		</table>";
	echo "		<div id=\"paneData\">";

				$curAbil 	= '';
				$prevAbil 	= '';
				for ($count = 0; $count < count($abilityDetails_array); $count++) {
					$curAbil = $abilityDetails_array[$count]['attacktype'];

					if ($curAbil != $prevAbil) { // New Ability
						if ($count != 0) {
	echo "							</table>";
	echo "						<center><input type=\"submit\" value=\"Generate Ability Queue\"></center>";
	echo "					</form>";
						}

						$playerPerc 		= $playerDPS_array[$abilityDetails_array[$count]['attacker']];
						$abilityPerc 		= $abilityDetails_array[$count]['damage_total'] / $encounterDetails['duration'];
						$topValues 		= $abilityDetails_array[$count];
						$topValues['critperc']	= ($abilityDetails_array[$count]['crithits'] / $abilityDetails_array[$count]['hits'] ) * 100;;
						$topValues['encdps']	= $abilityDetails_array[$count]['damage_total'] / $encounterDetails['duration'];
						$topValues['dpsperc'] 	= ($abilityPerc / $playerPerc) * 100;
						
						$idName = "table".$abilityDetails_array[$count]['attacktype'];
						$chartName = "chartArea".$abilityDetails_array[$count]['attacktype'];

	echo "				<div align=\"center\" id=\"".$chartName."\" style=\"width: 1000px; height: 300px; margin: 0 auto\"></div>";
	echo "				<form name=\"compareForm\" action=\"".$PAGE_ABILITYQ."?type=".$displayType."&enc=".$encid."&abil=".$abilityDetails_array[$count]['attacktype']."&mob=".$mobName."\"method=\"POST\">";
	echo "				<table border=\"0\" align=\"center\" id=\"".$topValues['attacktype']."\" width=\"1024\">";
	echo "					<tr>";
	echo "						<td align=\"center\"><b>".$topValues['attacktype']."</b></td>";
	echo "					</tr>";
	echo "				</table>";
	echo "					<table border=\"1\" align=\"center\" id=\"".$idName."\" width=\"1024\">";
	echo "						<tr>";
	echo "							<td align=\"center\">Player</td>";
	echo "							<td align=\"center\">Duration</td>";
	echo "							<td align=\"center\">Uptime</td>";
	echo "							<td align=\"center\">Damage</td>";
	//echo "							<td align=\"center\">+/-</td>";
	echo "							<td align=\"center\">DPS (%)</td>";
	//echo "							<td align=\"center\">+/-</td>";
	echo "							<td align=\"center\">Hit</td>";
	//echo "							<td align=\"center\">+/-</td>";
	echo "							<td align=\"center\">Crit (%)</td>";
	//echo "							<td align=\"center\">+/-</td>";
	echo "							<td align=\"center\">Min</td>";
	//echo "							<td align=\"center\">+/-</td>";
	echo "							<td align=\"center\">Avg</td>";
	//echo "							<td align=\"center\">+/-</td>";
	echo "							<td align=\"center\">Max</td>";
	//echo "							<td align=\"center\">+/-</td>";
	echo "						</tr>";
					}

					$abilityDPS 					= $abilityDetails_array[$count]['damage_total'] / $encounterDetails['duration'];
					$abilityUptime					= $abilityDetails_array[$count]['duration'];

					$playerDPS 					= $playerDPS_array[$abilityDetails_array[$count]['attacker']];
					$playerUptime					= $playerDPS_array[$abilityDetails_array[$count]['attacker']."-duration"];

					$abilityDetails_array[$count]['critperc']	= ( $abilityDetails_array[$count]['crithits'] / $abilityDetails_array[$count]['hits'] ) * 100;
					$dps_value 					= ($abilityDPS / $playerDPS) * 100;
					$uptime_value 					= ($abilityUptime / $encounterDetails['duration']) 	* 100;
	echo "						<tr>";
	echo "							<td align=\"left\"><input type=\"checkbox\" name=\"compareName[]\" value=\"".$abilityDetails_array[$count]['attacker']."\" /><a href=\"".$PAGE_PLAYER."?enc=".$encid."&player=".$abilityDetails_array[$count]['attacker']."\">".$abilityDetails_array[$count]['attacker']."</a></td>";
	echo "							<td align=\"center\">".minutes($abilityDetails_array[$count]['duration'])."</td>";
	echo "							<td align=\"center\">".number_format($uptime_value, 2,'.',',')."%</td>";
	echo "							<td align=\"center\">".number_format($abilityDetails_array[$count]['damage_total'], 0,'',',')."</td>";
	//echo "							<td align=\"center\">".number_format(($abilityDetails_array[$count]['damage_total'] - $topValues['damage_total']), 0,'',',')."</td>";
	echo "							<td align=\"center\">".number_format($abilityDPS, 2,'.','')." (".  number_format($dps_value, 0, '.', ',') . "%)</td>";
	//echo "							<td align=\"center\">".number_format(($abilityDPS - $topValues['encdps']), 2,'.',',')." (".number_format(($dps_value - $topValues['dpsperc']), 0,'.',',')."%)</td>";
	echo "							<td align=\"center\">".number_format($abilityDetails_array[$count]['hits'], 0,'',',')."</td>";
	//echo "							<td align=\"center\">".number_format(($abilityDetails_array[$count]['hits'] - $topValues['hits']), 0,'',',')."</td>";
	echo "							<td align=\"center\">".number_format($abilityDetails_array[$count]['crithits'], 0,'',',')." (".number_format(floatval($abilityDetails_array[$count]['critperc']), 0,'.',',')."%)</td>";
	//echo "							<td align=\"center\">".number_format(($abilityDetails_array[$count]['crithits'] - $topValues['crithits']), 0,'',',')." (".number_format(($abilityDetails_array[$count]['critperc'] - $topValues['critperc']), 0,'.',',')."%)</td>";
	echo "							<td align=\"center\">".number_format($abilityDetails_array[$count]['minhit'], 0,'',',')."</td>";
	//echo "							<td align=\"center\">".number_format(($abilityDetails_array[$count]['minhit'] - $topValues['minhit']), 0,'',',')."</td>";
	echo "							<td align=\"center\">".number_format($abilityDetails_array[$count]['average'], 2,'.',',')."</td>";
	//echo "							<td align=\"center\">".number_format(($abilityDetails_array[$count]['average'] - $topValues['average']), 2,'.',',')."</td>";
	echo "							<td align=\"center\">".number_format($abilityDetails_array[$count]['maxhit'], 0,'',',')."</td>";
	//echo "							<td align=\"center\">".number_format(($abilityDetails_array[$count]['maxhit'] - $topValues['maxhit']), 0,'',',')."</td>";
	echo "						</tr>";

								$currentRow = 0;
								$prevAbil = $curAbil;
				}
	echo "							</table>";
	echo "							<center><input type=\"submit\" value=\"Generate Ability Queue\"></center>";
	echo "						</form>";
	echo "	</div>";
	echo " 		<center><b>This page rendered in {microtime} seconds</b></center>";
	echo "	</body>";
	echo "</html>";

	mysql_close($link);
?>