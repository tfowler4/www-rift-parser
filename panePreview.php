<?php
	$filepath 			= dirname(__FILE__);
	include_once 		$filepath."/config/nav.php";

	$encid = $_GET['enc'];

	$player_array			= array();
	$class_array			= array();
	$participantName_array	= array();
	$participantClass_array	= array();
	$participant_array		= array();
	$parseType_array 		= array();

	$encounterDetails		= array();
	$playerCount 			= 0;
	$currentClass 			= "";
	$newClass 				= "NO";
	$player_string			= 'WHERE (';

	//***************START-GETTING LIST OF PLAYERS AND GENERATING SEARCH STRING**************
	$playerList_query = get_all_players();
	while ($row = mysql_fetch_array($playerList_query)) {
		array_push($player_array, $row['name']);
		array_push($class_array, $row['class']);
	}

	$query_all_players		= "(name='";
	$query_all_players		.= implode("' OR name='", $player_array)."')";
	//***************END-GETTING LIST OF PLAYERS AND GENERATING SEARCH STRING**************

	//***************START-GETTING LIST OF PARTICIPANTS OF RAID**************
	$participant_query = get_all_combatants($encid, $query_all_players);
	$playerCount = mysql_num_rows($participant_query);

	while ($row = mysql_fetch_array($participant_query)) {
		if (in_array($row['name'], $player_array)) {
			$index = array_search($row['name'], $player_array);

			array_push($participantName_array, $row['name']);
			array_push($participantClass_array, $class_array[$index]);

			$participant_array[$row['name']] = $class_array[$index];
		}
	}

	asort($participant_array);
	//***************END-GETTING LIST OF PARTICIPANTS OF RAID**************

	//***********START-GETTING PARSE TYPES************
	$parse_query = get_all_search_encounterType();
	while ($row = mysql_fetch_array($parse_query)) {
		if ( $row['type'] != "" ) {
			array_push($parseType_array, $row['type']);
		}
	}
	//***********END-GETTING PARSE TYPES************

	//***********START-GETTING ENCOUNTER DETAILS************
	$detail_query = get_encounter_details($encid);

	$encounterDetails = mysql_fetch_array($detail_query);
	//***********END-GETTING ENCOUNTER DETAILS************

	echo "<form name=\"detailsForm\"  method=\"POST\">";
	echo "	<table align=\"center\" border=\"1\" width=\"100%\">";
	echo "		<tr>";
	echo "			<td colspan=\"2\" align=\"center\"><b>Encounter Details</b></td>";
	echo "		</tr>";
	echo "		<tr>";
	echo "			<td align=\"right\"  width=\"15%\">ID: </td>";
	echo "			<td align=\"left\"  width=\"85%\"><i>".$encounterDetails['encid']."</i></td>";
	echo "		</tr>";
	echo "		<tr>";
	echo "			<td align=\"right\">Name: </td>";
	echo "			<td align=\"left\"><input type=\"text\" size=\"30\" id =\"nameValue\" name=\"nameValue\" value=\"".$encounterDetails['title']."\"></td>";
	echo "		</tr>";
	echo "		<tr>";
	echo "			<td align=\"right\">Duration: </td>";
	echo "			<td align=\"left\"><i>".minutes($encounterDetails['duration'])."</i></td>";
	echo "		</tr>";
	echo "		<tr>";
	echo "			<td align=\"right\">DPS: </td>";
	echo "			<td align=\"left\"><i>".number_format($encounterDetails['encdps'], 2, '.', ',')."</i></td>";
	echo "		</tr>";
	echo "		<tr>";
	echo "			<td align=\"right\">Time: </td>";
	echo "			<td align=\"left\">".dateFormat($encounterDetails['starttime']);
	echo "		</tr>";
	echo "		<tr>";
	echo "			<td align=\"right\">Date: </td>";
	echo "			<td align=\"left\">Month: ";
	echo "				<select id =\"monthList\" name=\"months\">";
							for ($month = 1; $month < 13; $month++) {
								if ($month == getMonth($encounterDetails['starttime'])) {
	echo "							<option value=\"".$month."\" selected=\"selected\">".$month."</option>";
								} else {
	echo "							<option value=\"".$month."\">".$month."</option>";
								}
							}
	echo "				</select>";
	echo " 				Day: ";
	echo "				<select id =\"dayList\" name=\"days\">";
							for ($day = 1; $day < 33; $day++) {
								if ($day == getDay($encounterDetails['starttime'])) {
	echo "							<option value=\"".$day."\" selected=\"selected\">".$day."</option>";
								} else {
	echo "							<option value=\"".$day."\">".$day."</option>";
								}
							}
	echo "				</select>";
	echo " 				Year: ";
	echo "				<select id =\"yearList\" name=\"years\">";
							for ($year = 2011; $year < 2020; $year++) {
								if ($year == getYear($encounterDetails['starttime'])) {
									echo "<option value=\"".$year."\" selected=\"selected\">".$year."</option>";
								} else {
									echo "<option value=\"".$year."\">".$year."</option>";
								}
							}
	echo "				</select>";
	echo "			</td>";
	echo "		</tr>";
	echo "		<tr>";
	echo "			<td align=\"right\">Type: </td>";
	echo "			<td align=\"left\">";
	echo "				<select id =\"parseList\" name=\"parseList\">";
	echo "					<option value=\"\">Select Parse Type</option>";
							for ($count = 0; $count < count($parseType_array); $count++) {
								if ($parseType_array[$count] == $encounterDetails['parseType']) {
	echo "							<option value=\"".$parseType_array[$count]."\" selected=\"selected\">".$parseType_array[$count]."</option>";
								} else {
	echo "							<option value=\"".$parseType_array[$count]."\">".$parseType_array[$count]."</option>";
								}
							}
	echo "				</select>";
	echo "			</td>";
	echo "		</tr>";
	echo "		<tr>";
	echo "			<td align=\"right\">Uploader: </td>";
						if ($encounterDetails['uploadedby'] != "") {
	echo "					<td align=\"left\">"; //<input type=\"text\" size=\"20\" id =\"uploadby\" name=\"uploadby\" readonly=\"readonly\" value=\"".$encounterDetails['uploadedby']."\"></td>";
	echo "						<select id =\"uploadby\" name=\"uploadby\" disabled=\"disabled\">";
	echo "							<option value=\"".$encounterDetails['uploadedby']."\">".$encounterDetails['uploadedby']."</option>";
	echo "						</select>";
						} else {
	echo "					<td align=\"left\">";
	echo "						<select id =\"uploadby\" name=\"uploadby\">";
	echo "							<option value=\"\">Select User</option>";
										for ($count = 0; $count < count($player_array); $count++) {
	echo "									<option value=\"" . $player_array[$count] . "\">";
	echo 										$player_array[$count];
	echo "									</option>";
										}
	echo "						</select>";
						}
	echo "		</tr>";
	echo "		<tr>";
	echo "			<td align=\"right\">Notes: </td>";
	echo "			<td align=\"left\"><textarea name=\"notes\" id=\"notes\" cols=\"20\" rows=\"5\" style=\"resize: none;\">".$encounterDetails['notes']."</textarea></td>";
	echo "		</tr>";
	echo "		<tr>";
	echo "			<td align=\"center\" colspan=\"2\"><i>Raid Participants</i></td>";
	echo "		</tr>";
	echo "		<tr>";
	echo "			<td align=\"center\" colspan=\"2\">";
						$playerCount = 0;

	echo "				<table align=\"center\" border=\"1\" width=\"100%\">";
	echo "					<tr>";
								foreach ($participant_array as $name => $class) {
									if ($currentClass != $class) {
										if ($playerCount ==  0) {
	echo "									<td width=\"25%\" valign=\"top\" align=\"center\">";
										} else {
	echo "										</table>";
	echo "									</td>";
	echo "									<td width=\"25%\" valign=\"top\" align=\"center\">";
										}
											$currentClass = $class;
											$newClass = "YES";
									}

									if ($newClass == "YES") {
	echo "								<table border=\"0\" align=\"center\">";
	echo "									<tr>";
	echo " 										<td align=\"center\"><b>".$currentClass."</b></td>";
	echo "									<tr>";
	echo "										<td align=\"center\"><a href=\"".$PAGE_PLAYER."?enc=".$encid."&player=".$name."\">".$name."</a></td>";
	echo "									</tr>";
										$newClass = "NO";
										$playerCount = $playerCount + 1;
									} else if ($newClass == "NO") {
	echo "								<tr>";
	echo "									<td align=\"center\"><a href=\"".$PAGE_PLAYER."?enc=".$encid."&player=".$name."\">".$name."</a></td>";
	echo "								</tr>";
										$playerCount = $playerCount + 1;
									}
								}
	echo "						</table>";
	echo "					</tr>";
	echo "				</table>";
	echo "			</td>";
	echo "		</tr>";
	echo "		<tr>";
	echo "			<td colspan=\"2\" align=\"center\">";
	echo "			<input onClick=\"MakeChanges()\" type=\"button\" value=\"Submit Changes!\">";
	echo "			<input onClick=\"DeleteParse()\" type=\"button\" value=\"Delete Parse\"></td>";
	//echo "			<input type=\"button\" value=\"Delete Parse\"></td>";
	echo "		</tr>";
	echo "	</table>";
	echo "</form>";

	mysql_close($link);
?>