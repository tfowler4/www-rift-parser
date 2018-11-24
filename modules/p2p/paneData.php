<?php
	$filepath 			= dirname(dirname(dirname(__FILE__)));
	include_once 		$filepath."/config/nav.php";

	ob_start( 'load_time' );

	$encid 						= $_GET['enc'];
	$mobName					= $_GET['mob'];
	$displayType					= $_GET['type'];
	$players 					= array();
	$playerName_string				= "";

	if ( isset($_GET['players']) ) {
		$players = explode("_", $_GET['players']);
	} else {
		$players = $_POST["compare"];
	}

	$playerName_string 				= $_GET['players'];
	$numOfPlayers 					= count($players);
	$numOfAbilities 				= 0;
	$numFilter 						= 0;
	$numFilterName					= 0;
	$currentRow 					= 0;
	$numOfMobs						= 0;
	$ability_array 					= array();
	$abilityDetails_array 				= array();
	$abilityDetails_dps_array 			= array();
	$abilityDetails_hps_array 			= array();
	$abilityDetails_incdps_array 			= array();
	$abilityDetails_inchps_array 			= array();
	$playerDPS_array				= array();
	$playerHPS_array				= array();
	$playerINCDPS_array				= array();
	$playerINCHPS_array				= array();
	$topValues 					= array();
	$mobs_array					= array();
	$query_player_string 				= "(attacker='";
	$query_mob_attacker_string			= "(attacker='";
	$query_player_victim_string			= "(victim='";
	$query_ability_string 				= "(type='";

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

	$filter_query = get_p2p_ability_list($encid, $query_player_string);
	while ($row = mysql_fetch_array($filter_query)) {
		array_push($ability_array, addslashes($row['type']));
		$numOfAbilities++;
	}
	//***************END-GET ABILITIES USED IN ENCOUNTER**************

	//***************START-GET DATA PER ABILITY PER PLAYER**************
	if ($displayType == "DPS") {
		$ability_query = get_p2p_ability_data_dps_all($encid, $query_player_string, $query_mob_victim_string);
		while ($row = mysql_fetch_array($ability_query)) {
				$row['attacktype'] = "All";
				array_push($abilityDetails_dps_array, $row);

				$playerDPS_array[$row['attacker']] = $row['damage_total'] / $encounterDetails['duration'];
				$playerDPS_array[$row['attacker']."-duration"] = $row['duration'];
		}

		$ability_query = get_p2p_ability_data_dps_abilities($encid, $query_player_string, $query_mob_victim_string);
		while ($row = mysql_fetch_array($ability_query)) {
				array_push($abilityDetails_dps_array, $row);
		}

		$abilityDetails_array = $abilityDetails_dps_array;
	} else if ($displayType == "HPS") {

		$ability_query = get_p2p_ability_data_hps_all($encid, $query_player_string);
		while ($row = mysql_fetch_array($ability_query)) {
				$row['attacktype'] = "All";
				array_push($abilityDetails_hps_array, $row);

				$playerHPS_array[$row['attacker']] = $row['damage_total'] / $encounterDetails['duration'];
				$playerHPS_array[$row['attacker']."-duration"] = $row['duration'];
		}

		$ability_query = get_p2p_ability_data_hps_abilities($encid, $query_player_string);
		while ($row = mysql_fetch_array($ability_query)) {
				array_push($abilityDetails_hps_array, $row);
		}

		$abilityDetails_array = $abilityDetails_hps_array;
	} else if ($displayType == "INCDPS") {
		$ability_query = get_p2p_ability_data_incdps_all($encid, $query_mob_attacker_string, $query_player_victim_string);
		while ($row = mysql_fetch_array($ability_query)) {
			$row['damage_total'] 	= $row['damageCorrect'];
			$row['attacker'] 		= $row['victim'];
			$row['attacktype'] 		= "All";
			array_push($abilityDetails_incdps_array, $row);

			$playerINCDPS_array[$row['victim']] 			= $row['damage_total'] / $encounterDetails['duration'];
			$playerINCDPS_array[$row['victim']."-duration"] = $row['duration'];
		}

		$ability_query = get_p2p_ability_data_incdps_abilities($encid, $query_mob_attacker_string, $query_player_victim_string);
		while ($row = mysql_fetch_array($ability_query)) {
			$row['damage_total'] 	= $row['damageCorrect'];
			$row['attacker'] 		= $row['victim'];
			array_push($abilityDetails_incdps_array, $row);
		}

		$abilityDetails_array = $abilityDetails_incdps_array;
	} else if ($displayType == "INCHPS") {
		$ability_query = get_p2p_ability_data_inchps_all($encid, $query_player_victim_string);
		while ($row = mysql_fetch_array($ability_query)) {
			$row['attacker'] 		= $row['victim'];
			$row['attacktype'] 		= "All";
			array_push($abilityDetails_inchps_array, $row);

			$playerINCHPS_array[$row['victim']] 			= $row['damage_total'] / $encounterDetails['duration'];
			$playerINCHPS_array[$row['victim']."-duration"] = $row['duration'];
		}

		$ability_query = get_p2p_ability_data_inchps_abilities($encid, $query_player_victim_string);
		while ($row = mysql_fetch_array($ability_query)) {
			$row['attacker'] = $row['victim'];
			array_push($abilityDetails_inchps_array, $row);
		}

		$abilityDetails_array = $abilityDetails_inchps_array;
	}
	//***************END-GET DATA PER ABILITY PER PLAYER**************
	$curAbil 	= '';
	$prevAbil 	= '';
	for ($count = 0; $count < count($abilityDetails_array); $count++) {
		$curAbil = $abilityDetails_array[$count]['attacktype'];

		if ($curAbil != $prevAbil) { // New Ability
			if ($count != 0) {
	echo "			</table>";
	echo "			<center><input type=\"submit\" value=\"Generate Ability Queue\"></center>";
	echo "			</form>";
			}

		if ( $displayType == "DPS" ) {
			$playerPerc 					= $playerDPS_array[$abilityDetails_array[$count]['attacker']];
		} else if ( $displayType == "HPS" ) {
			$playerPerc 					= $playerHPS_array[$abilityDetails_array[$count]['attacker']];
		} else if ( $displayType == "INCDPS" ) {
			$playerPerc 					= $playerINCDPS_array[$abilityDetails_array[$count]['attacker']];
		} else if ( $displayType == "INCHPS" ) {
			$playerPerc 					= $playerINCHPS_array[$abilityDetails_array[$count]['attacker']];
		}

		$abilityPerc 		= $abilityDetails_array[$count]['damage_total'] / $encounterDetails['duration'];
		$topValues 		= $abilityDetails_array[$count];
		$topValues['critperc']	= ($abilityDetails_array[$count]['crithits'] / $abilityDetails_array[$count]['hits'] ) * 100;;
		$topValues['encdps']	= $abilityDetails_array[$count]['damage_total'] / $encounterDetails['duration'];
		$topValues['dpsperc'] 	= ($abilityPerc / $playerPerc) * 100;


		if ( $displayType == "DPS" ) {
		
		} else if ( $displayType == "HPS" ) {

		} else if ( $displayType == "INCDPS" ) {
			$topValues['actdamage'] 	= $abilityDetails_array[$count]['damage_total'] - $abilityDetails_array[$count]['blockdamage'] - $abilityDetails_array[$count]['deflectdamage'] - $abilityDetails_array[$count]['absorbdamage'];
			$topValues['actidps']		= $topValues['actdamage'] / $encounterDetails['duration'];
			$topValues['block']		= $topValues['block'] + $topValues['deflect'];
			$topValues['blockdamage']	= $topValues['blockdamage'] + $topValues['deflectdamage'];
		} else if ( $displayType == "INCHPS" ) {

		}

		$idName = "table".$abilityDetails_array[$count]['attacktype'];
		$chartName = "chartArea".$abilityDetails_array[$count]['attacktype'];

	echo "		<div align=\"center\" id=\"".$chartName."\" style=\"width: 1000px; height: 300px; margin: 0 auto\"></div>";
	echo "			<form name=\"compareForm\" action=\"".$PAGE_ABILITYQ."?type=".$displayType."&enc=".$encid."&abil=".$abilityDetails_array[$count]['attacktype']."&mob=".$mobName."\"method=\"POST\">";
	echo "				<table border=\"0\" align=\"center\" id=\"".$topValues['attacktype']."\" width=\"1024\">";
	echo "					<tr>";
	echo "						<td align=\"center\"><b>".$topValues['attacktype']."</b></td>";
	echo "					</tr>";
	echo "				</table>";
					if ( $displayType == "DPS" ) {
	echo "				<table border=\"1\" align=\"center\" class=\"sortable\" id=\"".$idName."\" width=\"1024\">";
	echo "					<tr>";
	echo "						<td align=\"center\">Player</td>";
	echo "						<td align=\"center\">Duration</td>";
	echo "						<td align=\"center\">Uptime</td>";
	echo "						<td align=\"center\">Damage</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">DPS (%)</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Hit</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Crit (%)</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Min</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Avg</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Max</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "					</tr>";
					} else if ( $displayType == "HPS" ) {
	echo "				<table border=\"1\" align=\"center\" class=\"sortable\" id=\"".$idName."\" width=\"1024\">";
	echo "					<tr>";
	echo "						<td align=\"center\">Player</td>";
	echo "						<td align=\"center\">Duration</td>";
	echo "						<td align=\"center\">Uptime</td>";
	echo "						<td align=\"center\">Heal</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">HPS (%)</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Hit</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Crit (%)</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Min.</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Avg</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Max.</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Overheal</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "					</tr>";
					} else if ( $displayType == "INCDPS" ) {
	echo "				<table border=\"1\" align=\"center\" class=\"sortable\" id=\"".$idName."\" width=\"1024\">";
	echo "					<tr>";
	echo "						<td align=\"center\">Player</td>";
	echo "						<td align=\"center\">Inc Damage</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">iDPS (%)</td>";
	//echo "						<td align=\"center\">+/-</td>";
	//echo "						<td align=\"center\">Act. Damage</td>";
	//echo "						<td align=\"center\">+/-</td>";
	//echo "						<td align=\"center\">Act. iDPS</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Hit</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Crit (%)</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Min.</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Avg</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Max.</td>";
	//echo "						<td align=\"center\">+/-</td>";
	//echo "						<td align=\"center\">Block/Deflect (#)</td>";
	//echo "						<td align=\"center\">+/-</td>";
	//echo "						<td align=\"center\">Deflect (#)</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Absorb (#)</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Dodge</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Parry</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Resist</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "					</tr>";
					} else if ( $displayType == "INCHPS" ) {
	echo "				<table border=\"1\" align=\"center\" class=\"sortable\" id=\"".$idName."\" width=\"1024\">";
	echo "					<tr>";
	echo "						<td align=\"center\">Player</td>";
	echo "						<td align=\"center\">Inc Heal</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">iHPS (%)</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Hit</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Crit (%)</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Min.</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Avg</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Max.</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "						<td align=\"center\">Overheal</td>";
	//echo "						<td align=\"center\">+/-</td>";
	echo "					</tr>";
					}
		}

		$actDamage 						= 0;
		$actHeal 						= 0;
		$actiHeal						= 0;
		$actiDPS						= 0;
		$actiHPS						= 0;
		$actHPS							= 0;

		$currentRow 						= 0;
		$abilityDPS 						= $abilityDetails_array[$count]['damage_total'] / $encounterDetails['duration'];

		$abilityPerc 						= $abilityDetails_array[$count]['damage_total'] / $encounterDetails['duration'];
		$abilityUptime						= $abilityDetails_array[$count]['duration'];

		$abilityDetails_array[$count]['critperc']		= ( $abilityDetails_array[$count]['crithits'] / $abilityDetails_array[$count]['hits'] ) * 100;

		if ( $displayType == "DPS" ) {
			$playerPerc 					= $playerDPS_array[$abilityDetails_array[$count]['attacker']];
			$playerUptime					= $playerDPS_array[$abilityDetails_array[$count]['attacker']."-duration"];
		} else if ( $displayType == "HPS" ) {
			$playerPerc 					= $playerHPS_array[$abilityDetails_array[$count]['attacker']];
			$playerUptime					= $playerHPS_array[$abilityDetails_array[$count]['attacker']."-duration"];
		} else if ( $displayType == "INCDPS" ) {
			$playerPerc 					= $playerINCDPS_array[$abilityDetails_array[$count]['attacker']];
			$playerUptime					= $playerINCDPS_array[$abilityDetails_array[$count]['attacker']."-duration"];
			$actDamage					= $abilityDetails_array[$count]['damage_total'] - $abilityDetails_array[$count]['blockdamage'] - $abilityDetails_array[$count]['deflectdamage'] - $abilityDetails_array[$count]['absorbdamage'];
			$actiDPS					= $actDamage / $encounterDetails['duration'];
			$abilityDetails_array[$count]['block']		= $abilityDetails_array[$count]['block'] + $abilityDetails_array[$count]['deflect'];
			$abilityDetails_array[$count]['blockdamage']	= $abilityDetails_array[$count]['blockdamage'] + $abilityDetails_array[$count]['deflectdamage'];
		} else if ( $displayType == "INCHPS" ) {
			$playerPerc 					= $playerINCHPS_array[$abilityDetails_array[$count]['attacker']];
			$playerUptime					= $playerINCHPS_array[$abilityDetails_array[$count]['attacker']."-duration"];
		}

		$dps_value 									= ($abilityDPS / $playerPerc) * 100;
		$uptime_value 								= ($abilityUptime / $encounterDetails['duration']) 	* 100;

	if ( $displayType == "DPS" ) {
	echo "	<tr>";
	echo "		<td align=\"left\"><input type=\"checkbox\" name=\"compareName[]\" value=\"".$abilityDetails_array[$count]['attacker']."\" /><a href=\"".$PAGE_PLAYER."?enc=".$encid."&player=".$abilityDetails_array[$count]['attacker']."\">".$abilityDetails_array[$count]['attacker']."</a></td>";
	echo "		<td align=\"center\">".minutes($abilityDetails_array[$count]['duration'])."</td>";
	echo "		<td align=\"center\">".number_format($uptime_value, 2,'.',',')."%</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['damage_total'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['damage_total'] - $topValues['damage_total']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDPS, 2,'.','')." (".  number_format($dps_value, 0, '.', ',') . "%)</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDPS - $topValues['encdps']), 2,'.',',')." (".number_format(($dps_value - $topValues['dpsperc']), 0,'.',',')."%)</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['hits'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['hits'] - $topValues['hits']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['crithits'], 0,'',',')." (".number_format(floatval($abilityDetails_array[$count]['critperc']), 0,'.',',')."%)</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['crithits'] - $topValues['crithits']), 0,'',',')." (".number_format(($abilityDetails_array[$count]['critperc'] - $topValues['critperc']), 0,'.',',')."%)</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['minhit'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['minhit'] - $topValues['minhit']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['average'], 2,'.',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['average'] - $topValues['average']), 2,'.',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['maxhit'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['maxhit'] - $topValues['maxhit']), 0,'',',')."</td>";
	echo "	</tr>";
	} else if ($displayType == "HPS" ) {
	echo "	<tr>";
	echo "		<td align=\"left\"><input type=\"checkbox\" name=\"compareName[]\" value=\"".$abilityDetails_array[$count]['attacker']."\" /><a href=\"".$PAGE_PLAYER."?enc=".$encid."&player=".$abilityDetails_array[$count]['attacker']."\">".$abilityDetails_array[$count]['attacker']."</a></td>";
	echo "		<td align=\"center\">".minutes($abilityDetails_array[$count]['duration'])."</td>";
	echo "		<td align=\"center\">".number_format($uptime_value, 2,'.',',')."%</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['damage_total'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['damage_total'] - $topValues['damage_total']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDPS, 2,'.','')." (".  number_format($dps_value, 0, '.', ',') . "%)</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDPS - $topValues['encdps']), 2,'.',',')." (".number_format(($dps_value - $topValues['dpsperc']), 0,'.',',').")</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['hits'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['hits'] - $topValues['hits']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['crithits'], 0,'',',')." (".number_format(floatval($abilityDetails_array[$count]['critperc']), 0,'.',',')."%)</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['crithits'] - $topValues['crithits']), 0,'',',')." (".number_format(($abilityDetails_array[$count]['critperc'] - $topValues['critperc']), 0,'.',',')."%)</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['minhit'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['minhit'] - $topValues['minhit']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['average'], 2,'.',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['average'] - $topValues['average']), 2,'.',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['maxhit'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['maxhit'] - $topValues['maxhit']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['overheal'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['overheal'] - $topValues['overheal']), 0,'',',')."</td>";
	echo "	</tr>";
	} else if ( $displayType == "INCDPS" ) {
	echo "	<tr>";
	echo "		<td align=\"left\"><input type=\"checkbox\" name=\"compareName[]\" value=\"".$abilityDetails_array[$count]['attacker']."\" /><a href=\"".$PAGE_PLAYER."?enc=".$encid."&player=".$abilityDetails_array[$count]['attacker']."\">".$abilityDetails_array[$count]['attacker']."</a></td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['damage_total'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['damage_total'] - $topValues['damage_total']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDPS, 2,'.','')." (".  number_format($dps_value, 0, '.', ',') . "%)</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDPS - $topValues['encdps']), 2,'.',',')." (".number_format(($dps_value - $topValues['dpsperc']), 0,'.',',').")</td>";
	//echo "		<td align=\"center\">".number_format($actDamage, 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($actDamage - $topValues['actdamage']), 0,'.',',')."</td>";
	//echo "		<td align=\"center\">".number_format($actiDPS, 2,'.','')."</td>";
	//echo "		<td align=\"center\">".number_format(($actiDPS - $topValues['actidps']), 2,'.',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['hits'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['hits'] - $topValues['hits']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['crithits'], 2,'.',',')." (".number_format(floatval($abilityDetails_array[$count]['critperc']), 0,'.',',')."%)</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['crithits'] - $topValues['crithits']), 0,'',',')." (".number_format(($abilityDetails_array[$count]['critperc'] - $topValues['critperc']), 0,'.',',')."%)</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['minhit'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['minhit'] - $topValues['minhit']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['average'], 2,'.',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['average'] - $topValues['average']), 2,'.',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['maxhit'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['maxhit'] - $topValues['maxhit']), 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['blockdamage'], 0,'',',')." (".$abilityDetails_array[$count]['block'].")</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['blockdamage'] - $topValues['blockdamage']), 0,'',',')." (".number_format(($abilityDetails_array[$count]['block'] - $topValues['block']), 0,'',',').")</td>";
	//echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['deflectdamage'], 0,'',','). " (".$abilityDetails_array[$count]['deflect'].")</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['deflectdamage'] - $topValues['deflectdamage']), 0,'',',')." (".number_format(($abilityDetails_array[$count]['deflect'] - $topValues['deflect']), 0,'',',').")</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['absorbdamage'], 0,'',','). " (".$abilityDetails_array[$count]['absorb'].")</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['absorbdamage'] - $topValues['absorbdamage']), 0,'',',')." (".number_format(($abilityDetails_array[$count]['absorb'] - $topValues['absorb']), 0,'',',').")</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['dodge'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['dodge'] - $topValues['dodge']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['parry'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['parry'] - $topValues['parry']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['resist'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['resist'] - $topValues['resist']), 0,'.',',')."</td>";
	echo "	</tr>";
	} else if ( $displayType == "INCHPS" ) {
	echo "	<tr>";
	echo "		<td align=\"left\"><input type=\"checkbox\" name=\"compareName[]\" value=\"".$abilityDetails_array[$count]['attacker']."\" /><a href=\"".$PAGE_PLAYER."?enc=".$encid."&player=".$abilityDetails_array[$count]['attacker']."\">".$abilityDetails_array[$count]['attacker']."</a></td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['damage_total'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['damage_total'] - $topValues['damage_total']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDPS, 2,'.','')." (".  number_format($dps_value, 0, '.', ',') . "%)</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDPS - $topValues['encdps']), 2,'.',',')." (".number_format(($dps_value - $topValues['dpsperc']), 0,'.',',').")</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['hits'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['hits'] - $topValues['hits']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['crithits'], 2,'.',',')." (".number_format(floatval($abilityDetails_array[$count]['critperc']), 0,'.',',')."%)</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['crithits'] - $topValues['crithits']), 0,'',',')." (".number_format(($abilityDetails_array[$count]['critperc'] - $topValues['critperc']), 0,'.',',')."%)</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['minhit'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['minhit'] - $topValues['minhit']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['average'], 2,'.',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['average'] - $topValues['average']), 2,'.',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['maxhit'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['maxhit'] - $topValues['maxhit']), 0,'',',')."</td>";
	echo "		<td align=\"center\">".number_format($abilityDetails_array[$count]['overheal'], 0,'',',')."</td>";
	//echo "		<td align=\"center\">".number_format(($abilityDetails_array[$count]['overheal'] - $topValues['overheal']), 0,'',',')."</td>";
	echo "	</tr>";
	}

		$currentRow = 0;
		$prevAbil = $curAbil;
	}
	echo "	</table>";
	echo "	<center><input type=\"submit\" value=\"Generate Ability Queue\"></center>";
	echo "</form>";

	mysql_close($link);
?>