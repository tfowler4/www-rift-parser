<?php
	$filepath 			= dirname(dirname(dirname(__FILE__)));
	include_once 		$filepath."/config/nav.php";

	ob_start( 'load_time' );

		$dateSearch_array	= array();
		
	$tier_array 		= array();
	$type_array		= array();
	$dungeon_array		= array();
	$mob_array		= array();
	$parseType_array 	= array();
	$playerName = $_GET['player'];

	if ($playerName == '')
		$playerName = $_POST['playerPage'];

	//***********GETTING PLAYER CLASS AND ROLES************
	$class_query = mysql_query(sprintf("SELECT DISTINCT *
						FROM players_table
						WHERE name='%s'",
						mysql_real_escape_string($playerName)
						)) or die(mysql_error());
	$playerInfo = mysql_fetch_array($class_query);
	//***********GETTING PLAYER CLASS************

	//***********GETTING AVALIABLE SOULS************
	$soul_array 	= array();

	$soul_query = mysql_query(sprintf("SELECT DISTINCT soul
						FROM class_table
						WHERE archetype='%s'",
						mysql_real_escape_string($playerInfo['class'])
						)) or die(mysql_error());
	while($row = mysql_fetch_array($soul_query)) {
		array_push($soul_array, $row['soul']);
		//echo "<br>".$row['soul'];
	}
	//***********GETTING AVALIABLE SOULS************

	$id_array = array();
	$query_id_list_string = "(encid='";
	

	$search_query = mysql_query(sprintf("SELECT DISTINCT encid
						FROM combatant_table
						WHERE name='%s'
						ORDER BY starttime DESC",
						mysql_real_escape_string($playerName)
						)) or die(mysql_error());
	$numOfFights = mysql_num_rows($search_query);
	while ($row = mysql_fetch_array($search_query)) {
		array_push($id_array, $row['encid']);
	}

	$query_id_list_string .= implode("' OR encid='", $id_array)."')";
	
	$fightLimit = 20;
	$currentFight = 0;

	$encounter_query = mysql_query(sprintf("SELECT DISTINCT *
						FROM encounter_table
						WHERE %s
						ORDER BY starttime DESC",
						$query_id_list_string
						)) or die(mysql_error());
	$encounterList_array = array();
	while ($row = mysql_fetch_array($encounter_query)) {
		array_push($encounterList_array, $row);
	}
	
	$data_query = mysql_query(sprintf("SELECT DISTINCT *
						FROM combatant_table
						WHERE name='%s'
						AND %s
						ORDER BY starttime DESC",
						mysql_real_escape_string($playerName),
						$query_id_list_string
						)) or die(mysql_error());
	$data_count = 0;
	while ($row = mysql_fetch_array($data_query)) {
		$encounterList_array[$data_count]['encdps'] = $row['encdps'];
		$encounterList_array[$data_count]['enchps'] = $row['enchps'];
		$encounterList_array[$data_count]['idps'] = $row['damagetaken'] / $encounterList_array[$data_count]['duration'];
		$encounterList_array[$data_count]['ihps'] = $row['healstaken'] / $encounterList_array[$data_count]['duration'];
		$data_count++;
	}
	
	$mob_array = array();
	
	$mob_query = mysql_query(sprintf("SELECT title
						FROM encounter_table
						WHERE %s
						GROUP BY title
						ORDER BY title ASC",
						$query_id_list_string
						)) or die(mysql_error());
	while ($row = mysql_fetch_array($mob_query)) {
		array_push($mob_array, $row['title']);
	}
	
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

	echo "<html>";
	echo "	<head>";
	echo "		<title>Trinity :: Online DPS Parser</title>";
	echo "		<script type=\"text/javascript\" src=\"pip.js\"></script>";
	echo "	</head>";
	echo "	<body onLoad=\"pageLoad()\">";
	echo "		<table border=\"1\" align=\"center\" width=\"1024\">";
	echo "			<tr>";
	echo "				<td align=\"center\" valign=\"top\">";
	echo "					<table border=\"1\" align=\"center\" width=\"200\">";
	echo "						<tr>";
	echo "							<td><center><b>Graph Filters</b></center></td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\">";
	echo "								<i>Parse Type</i></center> ";
	echo "							</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\"><i>Tiers</i></td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\">";
	echo "								<select id =\"parseTiers\" name=\"parseTiers\">";
	echo "									<option value=\"\">All Tiers</option>";
											for ($count = 0; $count < count($tier_array); $count++) {
	echo "										<option value=\"".$tier_array[$count]."\">Tier ".$tier_array[$count]."</option>";
											}
	echo "								</select>";
	echo "							</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\"><i>Players</i></td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\">";
	echo "								<select id =\"parsePlayers\" name=\"parsePlayers\">";
	echo "									<option value=\"\">All Types</option>";
											for ($count = 0; $count < count($type_array); $count++) {
	echo "										<option value=\"".$type_array[$count]."\">".$type_array[$count]."-Man</option>";
											}
	echo "								</select>";
	echo "							</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\"><i>Dungeons</i></td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\">";
	echo "								<select id =\"parseDungeons\" name=\"parseDungeons\">";
	echo "									<option value=\"\">All Dungeons</option>";
											for ($count = 0; $count < count($dungeon_array); $count++) {
	echo "										<option value=\"".$dungeon_array[$count]."\">".$dungeon_array[$count]."</option>";
											}
	echo "								</select>";
	echo "							</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\">";
	echo "								<i>Encounter</i></center> ";
	echo "							</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\">";
	echo "								<select id =\"selectEncounter\" name=\"selectEncounter\" onChange=\"RequestParses()\">";
	echo "									<option value=\"All\">All Encounters</option>";
										for ($count = 0; $count < count($mob_array); $count++) {
	echo "										<option value=\"" . $mob_array[$count] . "\">".$mob_array[$count]."</option>";
										}
	echo "								</select>";
	echo "							</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\"><i>Parse Type</i></td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\">";
	echo "								<select id =\"parseTypes\" name=\"parseTypes\">";
	echo "									<option value=\"\">All Types</option>";
												for ($count = 0; $count < count($parseType_array); $count++) {
	echo "											<option value=\"".$parseType_array[$count]."\">".$parseType_array[$count]."</option>";
												}
	echo "								</select>";
	echo "							</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\"><i>Date Range</i></td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\">";
	echo "								<table align=\"center\" border=\"0\">";
	echo "									<tr>";
	echo "										<td colspan=\"3\" align=\"center\">Start</td>";
	echo "									</tr>";
	echo "									<tr>";
	echo "										<td align=\"center\">Month</td>";
	echo "										<td align=\"center\">Day</td>";
	echo "										<td align=\"center\">Year</td>";
	echo "									</tr>";
	echo "									<tr>";
	echo "										<td align=\"center\">";
	echo "											<select id =\"parseStartMonthList\" name=\"months\">";
													for ($month = 1; $month < 13; $month++) {
														if ( $month == $dateSearch_array[0] ) {
	echo "														<option value=\"".$month."\" selected=\"selected\">".$month."</option>";
														} else {
	echo "														<option value=\"".$month."\">".$month."</option>";
														}
													}
	echo "											</select>";
	echo "										</td>";
	echo "										<td align=\"center\">";
	echo "											<select id =\"parseStartDayList\" name=\"days\">";
													for ($day = 1; $day < 33; $day++) {
														if ( $day == $dateSearch_array[1] ) {
	echo "														<option value=\"".$day."\" selected=\"selected\">".$day."</option>";
														} else {
	echo "														<option value=\"".$day."\">".$day."</option>";
														}
													}
	echo "											</select>";
	echo "										</td>";
	echo "										<td align=\"center\">";
	echo "											<select id =\"parseStartYearList\" name=\"years\">";
													for ($year = 2011; $year < 2020; $year++) {
														if ( $year == $dateSearch_array[2] ) {
	echo "														<option value=\"".$year."\" selected=\"selected\">".$year."</option>";
														} else {
	echo "														<option value=\"".$year."\">".$year."</option>";
														}
													}
	echo "											</select>";
	echo "										</td>";
	echo "									</tr>";
	echo "								</table>";
	echo "							</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td align=\"center\">";
	echo "								<table align=\"center\" border=\"0\">";
	echo "									<tr>";
	echo "										<td colspan=\"3\" align=\"center\">End</td>";
	echo "									</tr>";
	echo "									<tr>";
	echo "										<td align=\"center\">Month</td>";
	echo "										<td align=\"center\">Day</td>";
	echo "										<td align=\"center\">Year</td>";
	echo "									</tr>";
	echo "									<tr>";
	echo "										<td align=\"center\">";
	echo "											<select id =\"parseEndMonthList\" name=\"months\">";
													for ($month = 1; $month < 13; $month++) {
														if ( $month == $dateSearch_array[3] ) {
	echo "														<option value=\"".$month."\" selected=\"selected\">".$month."</option>";
														} else {
	echo "														<option value=\"".$month."\">".$month."</option>";
														}
													}
	echo "											</select>";
	echo "										</td>";
	echo "										<td align=\"center\">";
	echo "											<select id =\"parseEndDayList\" name=\"days\">";
													for ($day = 1; $day < 33; $day++) {
														if ( $day == $dateSearch_array[4] ) {
	echo "														<option value=\"".$day."\" selected=\"selected\">".$day."</option>";
														} else {
	echo "														<option value=\"".$day."\">".$day."</option>";
														}
													}
	echo "											</select>";
	echo "										</td>";
	echo "										<td align=\"center\">";
	echo "											<select id =\"parseEndYearList\" name=\"years\">";
													for ($year = 2011; $year < 2020; $year++) {
														if ( $year == $dateSearch_array[5] ) {
	echo "														<option value=\"".$year."\" selected=\"selected\">".$year."</option>";
														} else {
	echo "														<option value=\"".$year."\">".$year."</option>";
														}
													}
	echo "											</select>";
	echo "										</td>";
	echo "									</tr>";
	echo "								</table>";
	echo "							</td>";
	echo "						</tr>";
	echo "							<td align=\"center\" colspan=\"3\">";
	echo "								<input onClick=\"reloadChart()\" type=\"button\" value=\"Load Data\">";
	//echo "								<input onClick=\"changeData()\" type=\"button\" value=\"Search Parses\"> <input onClick=\"resetSearch()\" type=\"button\" value=\"Reset Filters\">";
	echo "							</td>";
	echo "						</tr>";
	echo "					</table>";
	echo "				</td>";
	echo "				<td align=\"center\">";
	echo "					<div align=\"center\" id=\"chartArea\" width=\"824\"></div>";
	echo "				</td>";
	echo "			</tr>";
	echo "			<tr>";
	echo "				<td width=\"200\">";
	echo "					<b id=\"playerName\">".$playerName."</b>";
	echo "					<br> Class: ".$playerInfo['class'];
	echo "					<br> Character Type: Alt of xxx/Primary";
						/*
	echo "					<table border=\"1\">";
	echo "						<tr>";
	echo "							<td>Role #1</td>";
	echo "							<td>";
	echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
											for ($point = 1; $point < 52; $point++) {
	echo "										<option value=\"".$point."\">".$point."</option>";
											}
	echo "								</select>";
	echo "							</td>";
	echo "							<td>";
	echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
											for ($soul = 0; $soul < count($soul_array); $soul++) {
	echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
											}
	echo "								</select>";
	echo "							</td>";
	echo "							<td>";
	echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
											for ($point = 1; $point < 52; $point++) {
	echo "										<option value=\"".$point."\">".$point."</option>";
											}
	echo "								</select>";
	echo "							</td>";
	echo "							<td>";
	echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
											for ($soul = 0; $soul < count($soul_array); $soul++) {
	echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
											}
	echo "								</select>";
	echo "							</td>";
	echo "							<td>";
	echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
											for ($point = 1; $point < 52; $point++) {
	echo "										<option value=\"".$point."\">".$point."</option>";
											}
	echo "								</select>";
	echo "							</td>";
	echo "							<td>";
	echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
											for ($soul = 0; $soul < count($soul_array); $soul++) {
	echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
											}
	echo "								</select>";
	echo "							</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Role #2</td>";
	echo "							<td>";
	echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
													for ($point = 1; $point < 52; $point++) {
	echo "										<option value=\"".$point."\">".$point."</option>";
													}
	echo "								</select>";
	echo "							</td>";
	echo "							<td>";
	echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
													for ($soul = 0; $soul < count($soul_array); $soul++) {
	echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
											}
	echo "								</select>";
	echo "							</td>";
	echo "							<td>";
	echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
											for ($point = 1; $point < 52; $point++) {
	echo "										<option value=\"".$point."\">".$point."</option>";
											}
	echo "								</select>";
	echo "							</td>";
	echo "							<td>";
	echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
											for ($soul = 0; $soul < count($soul_array); $soul++) {
	echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
											}
	echo "								</select>";
	echo "							</td>";
	echo "							<td>";
	echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
											for ($point = 1; $point < 52; $point++) {
	echo "										<option value=\"".$point."\">".$point."</option>";
											}
	echo "								</select>";
	echo "							</td>";
	echo "							<td>";
	echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
											for ($soul = 0; $soul < count($soul_array); $soul++) {
	echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
											}
	echo "								</select>";
	echo "							</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Role #3</td>";
	echo "							<td>";
			echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
													for ($point = 1; $point < 52; $point++) {
			echo "										<option value=\"".$point."\">".$point."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
													for ($soul = 0; $soul < count($soul_array); $soul++) {
			echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
													for ($point = 1; $point < 52; $point++) {
			echo "										<option value=\"".$point."\">".$point."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
													for ($soul = 0; $soul < count($soul_array); $soul++) {
			echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
													for ($point = 1; $point < 52; $point++) {
			echo "										<option value=\"".$point."\">".$point."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
													for ($soul = 0; $soul < count($soul_array); $soul++) {
			echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
													}
			echo "								</select>";
	echo "							</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Role #4</td>";
	echo "							<td>";
			echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
													for ($point = 1; $point < 52; $point++) {
			echo "										<option value=\"".$point."\">".$point."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
													for ($soul = 0; $soul < count($soul_array); $soul++) {
			echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
													for ($point = 1; $point < 52; $point++) {
			echo "										<option value=\"".$point."\">".$point."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
													for ($soul = 0; $soul < count($soul_array); $soul++) {
			echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
													for ($point = 1; $point < 52; $point++) {
			echo "										<option value=\"".$point."\">".$point."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
													for ($soul = 0; $soul < count($soul_array); $soul++) {
			echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
													}
			echo "								</select>";
	echo "							</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Role #5</td>";
	echo "							<td>";
			echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
													for ($point = 1; $point < 52; $point++) {
			echo "										<option value=\"".$point."\">".$point."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
													for ($soul = 0; $soul < count($soul_array); $soul++) {
			echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
													for ($point = 1; $point < 52; $point++) {
			echo "										<option value=\"".$point."\">".$point."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
													for ($soul = 0; $soul < count($soul_array); $soul++) {
			echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
													for ($point = 1; $point < 52; $point++) {
			echo "										<option value=\"".$point."\">".$point."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
													for ($soul = 0; $soul < count($soul_array); $soul++) {
			echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
													}
			echo "								</select>";
	echo "							</td>";
	echo "						</tr>";
	echo "						<tr>";
	echo "							<td>Role #6</td>";
	echo "							<td>";
			echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
													for ($point = 1; $point < 52; $point++) {
			echo "										<option value=\"".$point."\">".$point."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
													for ($soul = 0; $soul < count($soul_array); $soul++) {
			echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
													for ($point = 1; $point < 52; $point++) {
			echo "										<option value=\"".$point."\">".$point."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
													for ($soul = 0; $soul < count($soul_array); $soul++) {
			echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"point_r1s1\" name=\"point_r1s1\">";
													for ($point = 1; $point < 52; $point++) {
			echo "										<option value=\"".$point."\">".$point."</option>";
													}
			echo "								</select>";
			echo "							</td>";
			echo "							<td>";
			echo "								<select id =\"soul_r1s1\" name=\"soul_r1s1\">";
													for ($soul = 0; $soul < count($soul_array); $soul++) {
			echo "										<option value=\"".$soul_array[$soul]."\">".$soul_array[$soul]."</option>";
													}
			echo "								</select>";
	echo "							</td>";
	echo "						</tr>";
	echo "					</table>";*/

	echo "					<br> Role #1: xx Soul, xx Soul, xx Soul";
	echo "					<br> Role #2: xx Soul, xx Soul, xx Soul";
	echo "					<br> Role #3: xx Soul, xx Soul, xx Soul";
	echo "					<br> Role #4: xx Soul, xx Soul, xx Soul";
	echo "					<br> Role #5: xx Soul, xx Soul, xx Soul";
	echo "					<br> Role #6: xx Soul, xx Soul, xx Soul";
	echo "					<br> Number of Uploads: xx";
	echo "					<br> Number of Parses: ".$numOfFights;
	echo "				</td>";
	echo "				<td width=\"574\" align=\"center\"valign=\"middle\">";
	echo "					<b>Current Rankings</b>";
	echo "				</td>";
	echo "			</tr>";
	echo "			<tr>";
	echo "				<td colspan=\"2\" align=\"center\"> Data Navigation will be central here</td>";
	echo "			</tr>";
	echo "			<tr valign=\"top\">";
	echo "				<td colspan=\"2\">";
	echo "					<center><b>Latest Parses</b></center> ";
	echo "					<center>";

	echo "					</center>";
	echo "					<div id = \"parseData\">";
	echo "						<table border=\"1\" class=\"sortable\" id=\"encounterTable\">";
	echo "								<tr>";
	echo "									<td align=\"center\"><b>Name</b></td>";
	echo "									<td align=\"center\"><b>Date</b></td>";
	echo "									<td align=\"center\"><b>DPS</b></td>";
	echo "									<td align=\"center\"><b>HPS</b></td>";
	echo "									<td align=\"center\"><b>Inc DPS</b></td>";
	echo "									<td align=\"center\"><b>Inc HPS</b></td>";
	echo "								</tr>";
										for ($count = 0; $count < count($encounterList_array); $count++) {
											if ($currentFight < $fightLimit) {
	echo "											<tr>";
	echo "												<td align=\"center\"><a href=\"".$PAGE_PLAYER."?enc=".$encounterList_array[$count]['encid']."&player=".$playerName."\">".$encounterList_array[$count]['title']."</a></td>";
	echo " 												<td align=\"center\"><a href=\"".$PAGE_PLAYER."?enc=".$encounterList_array[$count]['encid']."&player=".$playerName."\">".dateShortFormat($encounterList_array[$count]['starttime'])."</a></td>";
	echo "												<td align=\"center\">".number_format($encounterList_array[$count]['encdps'], 2, '.', '')."</td>";
	echo "												<td align=\"center\">".number_format($encounterList_array[$count]['enchps'], 2, '.', '')."</td>";
	echo "												<td align=\"center\">".number_format($encounterList_array[$count]['idps'], 2, '.', ',')."</td>";
	echo "												<td align=\"center\">".number_format($encounterList_array[$count]['ihps'], 2, '.', ',')."</td>";
	echo "											</tr>";
	
												$currentFight++;
											}
										}
	echo "						</table> ";
	echo "					</div>";	
	echo "				</td>";
	echo "				<td valign=\"top\">";
	echo "				</td>";
	echo "			</tr>";
	echo "		</table>";
	echo " 		<center><b>This page rendered in {microtime} seconds</b></center>";
	echo "	</body>";
	echo "</html>";

	mysql_close($link);
?>