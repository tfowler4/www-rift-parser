<?php
	$filepath 		= dirname(dirname(dirname(__FILE__)));
	include_once 		$filepath."/config/nav.php";

	$playerName 		= $_GET['player'];
	$encName 		= $_GET['enc'];
	$type			= $_GET['type'];
	$id_array 		= array();
	$id_string 		= "";

	$search_query = mysql_query(sprintf("SELECT DISTINCT encid
						FROM encounter_table
						WHERE title='%s'
						AND parseType='%s'
						ORDER BY starttime DESC
						LIMIT 10",
						mysql_real_escape_string($encName),
						mysql_real_escape_string($type)
						)) or die(mysql_error());
	while ($row = mysql_fetch_array($search_query)) {
		array_push($id_array, $row['encid']);
	}

	for ($count = 0; $count < count($id_array); $count++) {
		$id_string .= "encid='".$id_array[$count]."'";

		if (count($id_array) > 1 && $count != (count($id_array) - 1)) {
			$id_string .= " OR ";
		}
	}

	$id_string .= ")";
	$fight_query = "";
	$encounter_query = "";

	if ($encName !=  "All") {
		$fight_query = mysql_query(sprintf("SELECT DISTINCT *
							FROM combatant_table
							WHERE name='%s'
							AND (%s
							ORDER BY starttime DESC
							LIMIT 10",
							mysql_real_escape_string($playerName),
							$id_string
							)) or die(mysql_error());

		$encounter_query = mysql_query(sprintf("SELECT DISTINCT *
							FROM encounter_table
							WHERE title='%s'
							AND parseType='%s'
							LIMIT 10",
							mysql_real_escape_string($encName),
							mysql_real_escape_string($type)
							)) or die(mysql_error());
	} else {
		$fight_query = mysql_query(sprintf("SELECT DISTINCT *
							FROM combatant_table
							WHERE name='%s'
							ORDER BY starttime DESC
							LIMIT 10",
							mysql_real_escape_string($playerName)
							)) or die(mysql_error());

		$encounter_query = mysql_query(sprintf("SELECT DISTINCT *
							FROM encounter_table
							WHERE parseType='%s'
							LIMIT 10",
							mysql_real_escape_string($type)
							)) or die(mysql_error());
	}

	$encounterId_array = array();
	$encounterName_array = array();
	$encounterDate_array = array();
	while ($row = mysql_fetch_array($encounter_query)) {
		array_push($encounterId_array, $row['encid']);
		array_push($encounterName_array, $row['title']);
		array_push($encounterDate_array, $row['starttime']);
	}

	echo "	<table border=\"1\" class=\"sortable\" width=\"100%\" id=\"encounterTable\">";
	echo "		<tr>";
	echo "			<td align=\"center\"><b>Name<b></td>";
	echo "			<th scope=\"col\" align=\"center\">Date</b></th>";
	echo "			<td align=\"center\"><b>DPS</b></td>";
	echo "			<td align=\"center\"><b>HPS</b></td>";
	echo "			<td align=\"center\"><b>Inc Dmg</b></td>";
	echo "		</tr>";
		$perfCurrentDPS = 0;
		$perfCurrentHPS = 0;
		$perfCurrentDMG = 0;
		$numOfPerf = 0;
		while ($fights = mysql_fetch_array($fight_query)) {
			$index = array_search($fights['encid'], $encounterId_array);
	echo "		<tr>";
	echo "			<td align=\"center\"><a href=\"http://www.trinityguild.org/dps/player.php?enc=".$encounterId_array[$index]."&player=".$playerName."\">".$encounterName_array[$index]."</td>";
	echo " 			<td align=\"center\">".dateShortFormat($encounterDate_array[$index])."</td>";
	echo "			<td align=\"center\">".number_format($fights['encdps'], 2, '.', '')."</td>";
	echo "			<td align=\"center\">".number_format($fights['enchps'], 2, '.', '')."</td>";
	echo "			<td align=\"center\">".number_format($fights['damagetaken'], 0, '.', ',')."</td>";
	echo "		</tr>";
			$perfCurrentDPS = $perfCurrentDPS + $fights['encdps'];
			$perfCurrentHPS = $perfCurrentHPS + $fights['hps'];
			$perfCurrentDMG = $perfCurrentDMG + $fights['damagetaken'];
			$numOfPerf = $numOfPerf + 1;
		}

	echo "</table>";

	mysql_close($link);
?>