<?php
	$filepath 			= dirname(__FILE__);
	include_once 		$filepath."/config/nav.php";

	ob_start( 'load_time' );

	$tier_array 		= array();
	$type_array		= array();
	$dungeon_array		= array();
	$mob_array		= array();
	$parseType_array 	= array();
	$players_array		= array();
	$encounter_array	= array();
	$encid_array		= array();
	$name_array		= array();
	$dps_array 		= array();
	$hps_array 		= array();
	$idps_array		= array();
	$ihps_array		= array();
	$enc_array		= array();
	$date_array		= array();
	$duration_array		= array();
	$dateSearch_array	= array();
	$playerCount		= 0;
	$mobAmount 		= 0;
	$currentEncounter 	= '';
	$currentDate 		= '';
	$enc_string 		= '';
	$player_string		= 'WHERE ';

	//***************START-GETTING LIST OF PLAYERS AND GENERATING SEARCH STRING**************
	$playerList_query = get_all_players();
	$playerAmount = mysql_num_rows($playerList_query);	// Number of Players

	while ($row = mysql_fetch_array($playerList_query)) {
		array_push($players_array, $row['name']);
	}
	//***************END-GETTING LIST OF PLAYERS AND GENERATING SEARCH STRING**************

	//***************START-GETTING SEARCH FILTERS**************
	$tierList_query = get_all_search_tiers();
	while ($row = mysql_fetch_array($tierList_query)) {
		if ( $row['tier'] != "" ) {
			array_push($tier_array, $row['tier']);
		}
	}

	$typeList_query = get_all_search_types();
	while ($row = mysql_fetch_array($typeList_query)) {
		if ( $row['type'] != "" ) {
			array_push($type_array, $row['type']);
		}
	}

	$dungeonList_query = get_all_search_dungeons();
	while ($row = mysql_fetch_array($dungeonList_query)) {
		if ( $row['name'] != "" ) {
			array_push($dungeon_array, $row['name']);
		}
	}

	$encounterList_query = get_all_search_encounters();
	while ($row = mysql_fetch_array($encounterList_query)) {
		if ( $row['name'] != "" ) {
			array_push($mob_array, $row['name']);
		}
	}

	$parseList_query = get_all_search_encounterType();
	while ($row = mysql_fetch_array($parseList_query)) {
		if ( $row['type'] != "" ) {
			array_push($parseType_array, $row['type']);
		}
	}
	
	$dateSearch_array[0] = date(n, strtotime('-14 days'));
	$dateSearch_array[1] = date(j, strtotime('-14 days'));
	$dateSearch_array[2] = date(Y, strtotime('-14 days'));
	$dateSearch_array[3] = date(n);
	$dateSearch_array[4] = date(j);
	$dateSearch_array[5] = date(Y);
	//***************END-GETTING SEARCH FILTERS**************

	//***************START-GET ALL ENCOUNTERS**************
	$encounter_query = get_all_encounters();

	while ($row = mysql_fetch_array($encounter_query)) {
		array_push($encounter_array, $row);
		array_push($encid_array, $row['encid']);
	}

	$mobAmount = mysql_num_rows($encounter_query);
	//***************END-GET ALL ENCOUNTERS**************

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

	echo "<html>";
	echo "	<head>";
	echo "		<title>Trinity :: Online DPS Parser</title>";
	echo "		<script type=\"text/javascript\" src=\"".$JS_INDEX."\"></script>";
	echo "		<script type=\"text/javascript\" src=\"index.js\"></script>";
	echo "	</head>";
	echo "	<body onLoad=\"pageLoad()\">";
	echo "		<table border=\"1\" align=\"center\" width=\"1024\">";
	echo "				<td colspan=\"3\">";
	echo "					<div id=\"paneGraph\" width=\"1024\"></div>";
	echo "				</td>";
	echo "			</tr>";
	echo "			<tr>";
	echo "				<td valign=\"top\" align=\"200\">";
	echo "					<table align=\"center\" border=\"1\">";
	/*
	echo "						<tr>";
	echo "							<td align=\"center\"><b>Player Pages</b></td>";
	echo "						</tr>";
	echo "							<td align=\"center\">";
	echo "								<div id=\"panePlayer\">";
	echo "									<form name=\"playerForm\" action=\"".$PAGE_PIP."\" method=\"POST\">";
	echo "										<select id =\"playerPage\" name=\"playerPage\">";
	echo "											<option value=\"\">Select Player</option>";
													for ($count = 0; $count < count($players_array); $count++) {
	echo "												<option value=\"".$players_array[$count]."\">".$players_array[$count]."</option>";
													}
	echo "										</select>";
	echo "										<input type=\"submit\" value=\"View Player Page\">";
	echo "									</form>";
	echo "								</div>";
	echo "							</td>";
	echo "						</tr>";
	*/
	echo "						<tr>";
	echo "							<td align=\"center\"><b>Search Filters</b></td>";
	echo "						</tr>";
	echo "						<div id=\"paneSearch\">";
	echo "							<tr>";
	echo "								<td align=\"center\"><i>Tiers</i></td>";
	echo "							</tr>";
	echo "							<tr>";
	echo "								<td align=\"center\">";
	echo "									<select id =\"parseTiers\" name=\"parseTiers\">";
	echo "										<option value=\"\">All Tiers</option>";
												for ($count = 0; $count < count($tier_array); $count++) {
	echo "											<option value=\"".$tier_array[$count]."\">Tier ".$tier_array[$count]."</option>";
												}
	echo "									</select>";
	echo "								</td>";
	echo "							</tr>";
	echo "							<tr>";
	echo "								<td align=\"center\"><i>Players</i></td>";
	echo "							</tr>";
	echo "							<tr>";
	echo "								<td align=\"center\">";
	echo "									<select id =\"parsePlayers\" name=\"parsePlayers\">";
	echo "										<option value=\"\">All Types</option>";
												for ($count = 0; $count < count($type_array); $count++) {
	echo "											<option value=\"".$type_array[$count]."\">".$type_array[$count]."-Man</option>";
												}
	echo "									</select>";
	echo "								</td>";
	echo "							</tr>";
	echo "							<tr>";
	echo "								<td align=\"center\"><i>Dungeons</i></td>";
	echo "							</tr>";
	echo "							<tr>";
	echo "								<td align=\"center\">";
	echo "									<select id =\"parseDungeons\" name=\"parseDungeons\">";
	echo "										<option value=\"\">All Dungeons</option>";
												for ($count = 0; $count < count($dungeon_array); $count++) {
	echo "											<option value=\"".$dungeon_array[$count]."\">".$dungeon_array[$count]."</option>";
												}
	echo "									</select>";
	echo "								</td>";
	echo "							</tr>";
	echo "							<tr>";
	echo "								<td align=\"center\"><i>Encounters</i></td>";
	echo "							</tr>";
	echo "							<tr>";
	echo "								<td align=\"center\">";
	echo "									<select id =\"parseEncounters\" name=\"parseEncounters\">";
	echo "										<option value=\"\">All Encounters</option>";
												for ($count = 0; $count < count($mob_array); $count++) {
	echo "											<option value=\"".$mob_array[$count]."\">".$mob_array[$count]."</option>";
												}
	echo "									</select>";
	echo "								</td>";
	echo "							</tr>";
	echo "							<tr>";
	echo "								<td align=\"center\"><i>Parse Type</i></td>";
	echo "							</tr>";
	echo "							<tr>";
	echo "								<td align=\"center\">";
	echo "									<select id =\"parseTypes\" name=\"parseTypes\">";
	echo "										<option value=\"\">All Types</option>";
													for ($count = 0; $count < count($parseType_array); $count++) {
	echo "												<option value=\"".$parseType_array[$count]."\">".$parseType_array[$count]."</option>";
													}
	echo "									</select>";
	echo "								</td>";
	echo "							</tr>";
	echo "							<tr>";
	echo "								<td align=\"center\"><i>Date Range</i></td>";
	echo "							</tr>";
	echo "							<tr>";
	echo "								<td align=\"center\">";
	echo "									<table align=\"center\" border=\"0\">";
	echo "										<tr>";
	echo "											<td colspan=\"3\" align=\"center\">Start</td>";
	echo "										</tr>";
	echo "										<tr>";
	echo "											<td align=\"center\">Month</td>";
	echo "											<td align=\"center\">Day</td>";
	echo "											<td align=\"center\">Year</td>";
	echo "										</tr>";
	echo "										<tr>";
	echo "											<td align=\"center\">";
	echo "												<select id =\"parseStartMonthList\" name=\"months\">";
														for ($month = 1; $month < 13; $month++) {
															if ( $month == $dateSearch_array[0] ) {
	echo "															<option value=\"".$month."\" selected=\"selected\">".$month."</option>";
															} else {
	echo "															<option value=\"".$month."\">".$month."</option>";
															}
														}
	echo "												</select>";
	echo "											</td>";
	echo "											<td align=\"center\">";
	echo "												<select id =\"parseStartDayList\" name=\"days\">";
														for ($day = 1; $day < 33; $day++) {
															if ( $day == $dateSearch_array[1] ) {
	echo "															<option value=\"".$day."\" selected=\"selected\">".$day."</option>";
															} else {
	echo "															<option value=\"".$day."\">".$day."</option>";
															}
														}
	echo "												</select>";
	echo "											</td>";
	echo "											<td align=\"center\">";
	echo "												<select id =\"parseStartYearList\" name=\"years\">";
														for ($year = 2011; $year < 2020; $year++) {
															if ( $year == $dateSearch_array[2] ) {
	echo "															<option value=\"".$year."\" selected=\"selected\">".$year."</option>";
															} else {
	echo "															<option value=\"".$year."\">".$year."</option>";
															}
														}
	echo "												</select>";
	echo "											</td>";
	echo "										</tr>";
	echo "									</table>";
	echo "								</td>";
	echo "							</tr>";
	echo "							<tr>";
	echo "								<td align=\"center\">";
	echo "									<table align=\"center\" border=\"0\">";
	echo "										<tr>";
	echo "											<td colspan=\"3\" align=\"center\">End</td>";
	echo "										</tr>";
	echo "										<tr>";
	echo "											<td align=\"center\">Month</td>";
	echo "											<td align=\"center\">Day</td>";
	echo "											<td align=\"center\">Year</td>";
	echo "										</tr>";
	echo "										<tr>";
	echo "											<td align=\"center\">";
	echo "												<select id =\"parseEndMonthList\" name=\"months\">";
														for ($month = 1; $month < 13; $month++) {
															if ( $month == $dateSearch_array[3] ) {
	echo "															<option value=\"".$month."\" selected=\"selected\">".$month."</option>";
															} else {
	echo "															<option value=\"".$month."\">".$month."</option>";
															}
														}
	echo "												</select>";
	echo "											</td>";
	echo "											<td align=\"center\">";
	echo "												<select id =\"parseEndDayList\" name=\"days\">";
														for ($day = 1; $day < 33; $day++) {
															if ( $day == $dateSearch_array[4] ) {
	echo "															<option value=\"".$day."\" selected=\"selected\">".$day."</option>";
															} else {
	echo "															<option value=\"".$day."\">".$day."</option>";
															}
														}
	echo "												</select>";
	echo "											</td>";
	echo "											<td align=\"center\">";
	echo "												<select id =\"parseEndYearList\" name=\"years\">";
														for ($year = 2011; $year < 2020; $year++) {
															if ( $year == $dateSearch_array[5] ) {
	echo "															<option value=\"".$year."\" selected=\"selected\">".$year."</option>";
															} else {
	echo "															<option value=\"".$year."\">".$year."</option>";
															}
														}
	echo "												</select>";
	echo "											</td>";
	echo "										</tr>";
	echo "									</table>";
	echo "								</td>";
	echo "							</tr>";
	echo "								<td align=\"center\" colspan=\"3\">";
	echo "									<input onClick=\"changeData()\" type=\"button\" value=\"Search Parses\"> <input onClick=\"resetSearch()\" type=\"button\" value=\"Reset Filters\">";
	echo "								</td>";
	echo "							</tr>";
	echo "						</div>";
	echo "					</table>";
	echo "				</td>";
	echo "				<td valign=\"top\" width=\"400\">";
	echo "					<div id=\"paneList\">";
	echo "						<form name=\"encounterForm\" action=\"".$PAGE_LIST."\" method=\"POST\">";
	echo "							<table align=\"center\" border=\"1\" width=\"100%\">";
	echo "								<tr>";
	echo "									<td align=\"center\"><b>Parse List</b></td>";
	echo "								</tr>";
	echo "								<tr>";
	echo "									<td><center><input type=\"submit\" value=\"Parse!\"></center>";
	echo "								</tr>";
	echo "								<tr>";
	echo "									<td>";
	echo "										<select id =\"encounterList\" name=\"names\" onChange=\"MakeRequest()\" size=\"33\"  style=\"width:400px\">"; // style=\"width:400px\">";
													for($count = 0; $count < count($encounter_array); $count++) {
	echo "												<option value=\"" . $encounter_array[$count]['encid'] . "\">";
	echo 													dateShortFormat($encounter_array[$count]['starttime']);
	echo " 													- ";
														if ($encounter_array[$count]['parseType'] == "") {
															echo "N";
														} else {
															echo substr($encounter_array[$count]['parseType'], 0, 1);
														}
	echo " 													- ";
														if ($encounter_array[$count]['uploadedby'] == "") {
															echo "*";
														}
	echo 													$encounter_array[$count]['title'];
														if ($encounter_array[$count]['uploadedby'] != "") {
	echo "													- ".$encounter_array[$count]['uploadedby'];
														}
	echo "												</option>";
													}
	echo "										</select>";
	echo "									</td>";
	echo "								</tr>";
	echo "							</table>";
	echo "						</form>";
	echo "					</div>";
	echo "				</td>";
	echo "				<td valign=\"top\" width=\"400\">";
	echo "					<div id=\"panePreview\">";
	echo "						<form name=\"detailsForm\"  method=\"POST\">";
	echo "							<table align=\"center\" border=\"1\" width=\"100%\" height=\"100%\">";
	echo "								<tr>";
	echo "									<td colspan=\"2\" align=\"center\"><b>Encounter Details</b></td>";
	echo "								</tr>";
	echo "								<tr>";
	echo "									<td align=\"right\" width=\"15%\">ID: </td>";
	echo "									<td align=\"left\" width=\"85%\"><i></i></td>";
	echo "								</tr>";
	echo "								<tr>";
	echo "									<td align=\"right\">Name: </td>";
	echo "									<td align=\"left\"><input type=\"text\" size=\"30\" id =\"nameValue\" name=\"nameValue\" readonly=\"readonly\" value=\"\"></td>";
	echo "								</tr>";
	echo "									<tr>";
	echo "									<td align=\"right\">Duration: </td>";
	echo "									<td align=\"left\"><i></i></td>";
	echo "								</tr>";
	echo "								<tr>";
	echo "									<td align=\"right\">DPS: </td>";
	echo "									<td align=\"left\"><i></i></td>";
	echo "								</tr>";
	echo "								<tr>";
	echo "									<td align=\"right\">Time: </td>";
	echo "									<td align=\"left\"></td>";
	echo "								</tr>";
	echo "								<tr>";
	echo "									<td align=\"right\">Date: </td>";
	echo "									<td align=\"left\">";
	echo "										Month: ";
	echo "										<select id =\"monthList\" name=\"months\">";
													for ($month = 1; $month < 13; $month++) {
	echo "												<option value=\"".$month."\">".$month."</option>";
													}
	echo "										</select>";
	echo " 										Day: ";
	echo "										<select id =\"dayList\" name=\"days\">";
													for ($day = 1; $day < 33; $day++) {
	echo "												<option value=\"".$day."\">".$day."</option>";
													}
	echo "										</select>";
	echo " 										Year: ";
	echo "										<select id =\"yearList\" name=\"years\">";
													for ($year = 2011; $year < 2020; $year++) {
	echo "												<option value=\"".$year."\">".$year."</option>";
													}
	echo "										</select>";
	echo "									</td>";
	echo "								</tr>";
	echo "								<tr>";
	echo "									<td align=\"right\">Type: </td>";
	echo "									<td align=\"left\">";
	echo "										<select id =\"parseType\" name=\"parseType\">";
	echo "										</select>";
	echo "									</td>";
	echo "								</tr>";
	echo "								<tr>";
	echo "									<td align=\"right\">Uploader: </td>";
	echo "									<td align=\"left\">";
	echo "										<select id =\"uploadby\" name=\"uploadby\" disabled=\"disabled\">";
	echo "										</select>";
	echo "									</td>";
	echo "								</tr>";
	echo "								<tr>";
	echo "									<td align=\"right\">Notes: </td>";
	echo "									<td align=\"left\"><textarea name=\"notes\" id=\"notes\" cols=\"20\" rows=\"5\" style=\"resize: none;\">".$row['notes']."</textarea></td>";
	echo "								</tr>";
	echo "								<tr>";
	echo "									<td align=\"center\" colspan=\"2\"><i>Raid Participants</i></td>";
	echo "								</tr>";
	echo "								<tr>";
	echo "									<td align=\"center\" colspan=\"2\">";
	echo "										<table border=\"0\">";
	echo "											<tr>";
	echo "											</tr>";
	echo "										</table>";
	echo "									</td>";
	echo "								</tr>";
	echo "								<tr>";
	echo "									<td colspan=\"2\" align=\"center\">";
	echo "										<input type=\"button\" value=\"Submit Changes\">  <input  type=\"button\" value=\"Delete Parse\">";
	echo "									</td>";
	echo "								</tr>";
	echo "							</table>";
	echo "						</form>";
	echo "					</div>";
	echo "				</td>";
	echo "			</tr>";
	echo "		</table>";
	echo " 		<center><b>This page rendered in {microtime} seconds</b></center>";
	echo "		<div id=\"paneChart\">";
	echo "			<table border=\"1\" class=\"sortable\" width=\"400\" id=\"encounterTable\" style=\"display:none;\">";
	echo "				<tr>";
	echo "					<td align=\"center\"><b>Name<b></td>";
	echo "					<td align=\"center\"><b>Date</b></td>";
	echo "					<td align=\"center\"><b>DPS</b></td>";
	echo "					<td align=\"center\"><b>HPS</b></td>";
	echo "					<td align=\"center\"><b>iDPS</b></td>";
	echo "					<td align=\"center\"><b>iHPS</b></td>";
	echo "				</tr>";

						for ($count = 0; $count < $mobAmount; $count++) {
	echo "					<tr>";
	echo "						<td align=\"center\"><a href=\"".$PAGE_LIST."?enc=".$encounter_array[$count]['encid']."\">".$encounter_array[$count]['title']."</a></td>";
	echo " 						<td align=\"center\"><a href=\"".$PAGE_LIST."?enc=".$encounter_array[$count]['encid']."\">".dateShortFormat($encounter_array[$count]['starttime'])."</a></td>";
	echo "						<td align=\"center\">".number_format($dps_array[$count], 2, '.', '')."</td>";
	echo "						<td align=\"center\">".number_format($hps_array[$count], 2, '.', '')."</td>";
	echo "						<td align=\"center\">".number_format(($idps_array[$count]/$encounter_array[$count]['duration']), 2, '.', '')."</td>";
	echo "						<td align=\"center\">".number_format(($ihps_array[$count]/$encounter_array[$count]['duration']), 2, '.', '')."</td>";

	echo "					</tr>";
						}
	echo "			</table>";
	echo "		</div>";
	echo "	</body>";
	echo "</html>";

	mysql_close($link);
?>