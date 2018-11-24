<?php
	$TABLE_ABILITY			= 'ability_table';
	$TABLE_ATTACKTYPE		= 'attacktype_table';
	$TABLE_CLASS			= 'class_table';
	$TABLE_COMBATANT		= 'combatant_table';
	$TABLE_CURRENT			= 'current_table';
	$TABLE_DAMAGETYPE		= 'damagetype_table';
	$TABLE_DUNGEON			= 'dungeon_table';
	$TABLE_ENCOUNTERLIST		= 'encounterList_table';
	$TABLE_ENCOUNTERTYPE		= 'encounterType_table';
	$TABLE_ENCOUNTER		= 'encounter_table';
	$TABLE_PLAYERS			= 'players_table';
	$TABLE_SWINGS			= 'swing_table';
	$TABLE_TIER			= 'tier_table';
	$TABLE_TYPE			= 'type_table';
	$table_array			= array($TABLE_ENCOUNTER, $TABLE_ATTACKTYPE, $TABLE_COMBATANT, $TABLE_DAMAGETYPE, $TABLE_SWINGS);

	//***RETRIEVE ALL ENCOUNTERS AVALIABLE (INDEX ONLY)***
	function get_all_encounters() {
		global $TABLE_ENCOUNTER;

		$currentDate 	= toDBFormat(date('Y-m-d', strtotime('-0 days'))) . " " . toDBTimeFormat(date('H:i:s'));
		$previousDate 	= toDBFormat(date('Y-m-d', strtotime('-14 days'))) . " " . toDBTimeFormat(date('H:i:s', strtotime('-7 days')));
		
		//WHERE parseType='Kill Shot'
		
		$query = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						WHERE starttime >= '%s'
						AND starttime <= '%s'
						ORDER BY starttime DESC",
						mysql_real_escape_string($TABLE_ENCOUNTER),
						$previousDate,
						$currentDate
						)) or die(mysql_error());
		return $query;
	}

	//***SEARCH FOR SPECIFIC ENCOUNTER LIST***
	function search_for_encounter_list($query_string) {
		global $TABLE_ENCOUNTERLIST;

		$query = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						%s
						ORDER BY name ASC",
						mysql_real_escape_string($TABLE_ENCOUNTERLIST),
						$query_string
						)) or die(mysql_error());
		return $query;
	}

	//***SEARCH FOR SPECIFIC ENCOUNTER LIST FOR PLAYER***
	function search_for_player_specific_list($encounter_title_string, $encounter_type_string, $query_combatant_player_string) {
		global $TABLE_ENCOUNTER;
		global $TABLE_COMBATANT;
		
		$encounterID_array = array();	
		$query = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						WHERE %s
						AND %s
						ORDER BY starttime ASC",
						mysql_real_escape_string($TABLE_ENCOUNTER),
						$encounter_title_string,
						$encounter_type_string
						)) or die(mysql_error());
		while ($row = mysql_fetch_array($query)) {
			array_push($encounterID_array, $row['encid']);
		}
		
		$query_encounterID_string = "(encid='";
		$query_encounterID_string .= implode("' OR encid='", $encounterID_array)."')";
		
		$query = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						WHERE %s
						AND %s
						ORDER BY starttime ASC",
						mysql_real_escape_string($TABLE_COMBATANT),
						$query_combatant_player_string,
						$query_encounterID_string
						)) or die(mysql_error());
						
		$encounterID_array = array();
		while ($row = mysql_fetch_array($query)) {
			array_push($encounterID_array, $row['encid']);
		}
		
		$query_encounterID_string = "(encid='";
		$query_encounterID_string .= implode("' OR encid='", $encounterID_array)."')";

		$query = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						WHERE %s
						ORDER BY starttime ASC",
						mysql_real_escape_string($TABLE_ENCOUNTER),
						$query_encounterID_string
						)) or die(mysql_error());
		return $query;
	}

	//***RETRIEVE SPECIFIC ENCOUNTER DETAILS***
	function get_encounter_details($encid) {
		global $TABLE_ENCOUNTER;

		$query = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						WHERE encid = '%s'",
						mysql_real_escape_string($TABLE_ENCOUNTER),
						$encid
						)) or die(mysql_error());
		return $query;
	}

	//***GET LIST OF ENCOUNTERS OF SPECIFIC MOB***
	function get_all_encounters_mob($mobName) {
		global $TABLE_ENCOUNTER;

		$query = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						WHERE title ='%s'",
						mysql_real_escape_string($TABLE_ENCOUNTER),
						$mobName
						)) or die(mysql_error());
		return $query;
	}

	//***SEARCH FOR SPECIFIC ENCOUNTER***
	function search_for_encounter($query_string) {
		global $TABLE_ENCOUNTER;

		$query = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						WHERE %s
						ORDER BY starttime DESC",
						mysql_real_escape_string($TABLE_ENCOUNTER),
						$query_string
						)) or die(mysql_error());
		return $query;
	}

	//***RETRIEVE LIST OF ALL PLAYERS IN GUILD***
	function get_all_players() {
		global $TABLE_PLAYERS;

		$query = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						ORDER BY name ASC",
						mysql_real_escape_string($TABLE_PLAYERS)
						)) or die(mysql_error());
		return $query;
	}

	//***RETRIEVE LIST OF ALL PLAYERS IN ENCOUNTER***
	function get_all_combatants($encid, $query_string) {
		global $TABLE_COMBATANT;

		$query = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						WHERE encid = '%s'
						AND %s",
						mysql_real_escape_string($TABLE_COMBATANT),
						mysql_real_escape_string($encid),
						$query_string
						)) or die(mysql_error());
		return $query;
	}

	//***RETRIEVE LIST OF ALL MOBS IN ENCOUNTER***
	function get_all_mobs($encid) {
		global $TABLE_COMBATANT;

		$query = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						WHERE encid = '%s'
						AND ally='F'",
						mysql_real_escape_string($TABLE_COMBATANT),
						mysql_real_escape_string($encid)
						)) or die(mysql_error());
		return $query;
	}
	/*
	//***RETRIEVE RAID DPS SWING DATA OVERALL***
	function get_encounter_dps_table($encid, $query_string) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT *,
						SUM(CASE WHEN damage >= 0 Then damage ELSE 0 END) as damage_total
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND (swingtype = 1 OR swingtype = 2)
						GROUP BY stime
						ORDER BY stime ASC",
						mysql_real_escape_string($TABLE_SWINGS),
						mysql_real_escape_string($encid),
						$query_string
						)) or die(mysql_error());
		return $query;
	}

	//***RETRIEVE RAID HPS SWING DATA OVERALL***
	function get_encounter_hps_table($encid, $query_string) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT *,
						SUM(CASE WHEN damage >= 0 Then damage ELSE 0 END) as heal_total
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND (swingtype = 3)
						GROUP BY stime
						ORDER BY stime ASC",
						mysql_real_escape_string($TABLE_SWINGS),
						mysql_real_escape_string($encid),
						$query_string
						)) or die(mysql_error());
		return $query;
	}

	//***RETRIEVE RAID IDPS SWING DATA OVERALL***
	function get_encounter_idps_table($encid, $query_string) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT *,
						SUM(CASE WHEN damage >= 0 Then damage ELSE 0 END) as damage_total
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND (swingtype != 3 AND swingtype != 13)
						GROUP BY stime
						ORDER BY stime ASC",
						mysql_real_escape_string($TABLE_SWINGS),
						mysql_real_escape_string($encid),
						$query_string
						)) or die(mysql_error());
		return $query;
	}
	*/
	//***RETRIEVE RAID DURATION SWING DATA SPECIFC MOB***
	function get_encounter_specific_duration($encid, $query_string, $query_string2) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT *, 
						MIN(stime) as start, MAX(stime) as end
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND %s
						AND (swingtype = 1 OR swingtype = 2 OR swingtype = 3)
						GROUP BY attacker
						ORDER BY attacker DESC",
						mysql_real_escape_string($TABLE_SWINGS),
						mysql_real_escape_string($encid),
						$query_string,
						$query_string2
						)) or die(mysql_error());
		return $query;
	}

	//***RETRIEVE RAID DPS SWING DATA SPECIFC MOB***
	function get_encounter_specific_dps($encid, $query_string, $query_string2) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT *,
						SUM(CASE WHEN damage >= 0 Then damage ELSE 0 END) as damage_total,
						COUNT(*) as hits
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND %s
						AND (swingtype = 1 OR swingtype = 2)
						GROUP BY attacker
						ORDER BY attacker DESC",
						mysql_real_escape_string($TABLE_SWINGS),
						mysql_real_escape_string($encid),
						$query_string,
						$query_string2
						)) or die(mysql_error());
		return $query;
	}

	//***RETRIEVE RAID HPS SWING DATA SPECIFIC MOB***
	function get_encounter_specific_hps($encid, $query_string) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT *,
						SUM(CASE WHEN damage >= 0 Then damage ELSE 0 END) as heal_total, 
						COUNT(*) as hits
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND (swingtype = 3)
						GROUP BY attacker
						ORDER BY attacker ASC",
						mysql_real_escape_string($TABLE_SWINGS),
						mysql_real_escape_string($encid),
						$query_string
						)) or die(mysql_error());
		return $query;
	}

	//***RETRIEVE RAID IDPS SWING DATA SPECIFIC MOB***
	function get_encounter_specifc_idps($encid, $query_string, $query_string2) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT *,
						SUM(CASE WHEN damage >= 0 Then damage ELSE 0 END) as damage_total
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND %s
						AND (swingtype != 3 AND swingtype != 13)
						GROUP BY victim
						ORDER BY victim ASC",
						mysql_real_escape_string($TABLE_SWINGS),
						mysql_real_escape_string($encid),
						$query_string,
						$query_string2
						)) or die(mysql_error());
		return $query;
	}

	//***RETRIEVE RAID DPS SWING DATA TABLE SPECIFC MOB***
	function get_encounter_specific_dps_table($encid, $query_string, $query_string2) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT *,
						SUM(CASE WHEN damage >= 0 Then damage ELSE 0 END) as damage_total
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND %s
						AND (swingtype = 1 OR swingtype = 2)
						GROUP BY stime
						ORDER BY stime ASC",
						mysql_real_escape_string($TABLE_SWINGS),
						mysql_real_escape_string($encid),
						$query_string,
						$query_string2
						)) or die(mysql_error());
		return $query;
	}

	//***RETRIEVE RAID HPS SWING DATA TABLE SPECIFIC MOB***
	function get_encounter_specific_hps_table($encid, $query_string) {
		global $TABLE_SWINGS;
		
		$query = mysql_query(sprintf("SELECT *,
						SUM(CASE WHEN damage >= 0 Then damage ELSE 0 END) as heal_total
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND (swingtype = 3)
						GROUP BY stime
						ORDER BY stime ASC",
						mysql_real_escape_string($TABLE_SWINGS),
						mysql_real_escape_string($encid),
						$query_string
						)) or die(mysql_error());
		return $query;
	}

	//***RETRIEVE RAID IDPS SWING DATA TABLE SPECIFIC MOB***
	function get_encounter_specifc_idps_table($encid, $query_string, $query_string2, $query_string3) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT *,
						SUM(CASE WHEN damage >= 0 Then damage ELSE 0 END) as damage_total
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND (%s
						AND %s)
						AND (swingtype != 3 AND swingtype != 13)
						GROUP BY stime
						ORDER BY stime ASC",
						mysql_real_escape_string($TABLE_SWINGS),
						mysql_real_escape_string($encid),
						$query_string,
						$query_string2,
						$query_string3
						)) or die(mysql_error());
		return $query;
	}





	//***RETRIEVE COMBATANT DATA FROM ENCOUNTER***
	function get_encounter_combatant_details($encid, $query_string) {
		global $TABLE_COMBATANT;

		$query = mysql_query(sprintf("SELECT *
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND ally='T'
						ORDER BY name ASC",
						mysql_real_escape_string($TABLE_COMBATANT),
						mysql_real_escape_string($encid),
						$query_string
						)) or die(mysql_error());
		return $query;
	}

	//***RETRIEVE COMBATANT TOTAL DPS DATA FROM ENCOUNTER***
	function get_encounter_combatant_dps($encid, $query_string) {
		global $TABLE_COMBATANT;

		$query = mysql_query(sprintf("SELECT SUM(damage) As damage_total, SUM(encdps) As dps_total
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND ally='T'",
						mysql_real_escape_string($TABLE_COMBATANT),
						mysql_real_escape_string($encid),
						$query_string
						)) or die(mysql_error());
		return $query;
	}

	//***RETRIEVE COMBATANT TOTAL HPS DATA FROM ENCOUNTER***
	function get_encounter_combatant_hps($encid, $query_string) {
		global $TABLE_COMBATANT;

		$query = mysql_query(sprintf("SELECT SUM(healed) As heal_total, SUM(enchps) As hps_total
						FROM %s
						WHERE encid = '%s'
						AND %s",
						mysql_real_escape_string($TABLE_COMBATANT),
						mysql_real_escape_string($encid),
						$query_string
						)) or die(mysql_error());
		return $query;
	}

	//***RETRIEVE COMBATANT TOTAL IDPS DATA FROM ENCOUNTER***
	function get_encounter_combatant_idps($encid, $query_string) {
		global $TABLE_COMBATANT;

		$query = mysql_query(sprintf("SELECT SUM(damagetaken) As idps_total
						FROM %s
						WHERE encid = '%s'
						AND %s",
						mysql_real_escape_string($TABLE_COMBATANT),
						mysql_real_escape_string($encid),
						$query_string
						)) or die(mysql_error());
		return $query;
	}
	
	//***RETRIEVE COMBATANT TOTAL IHPS DATA FROM ENCOUNTER***
	function get_encounter_combatant_ihps($encid, $query_string) {
		global $TABLE_COMBATANT;

		$query = mysql_query(sprintf("SELECT SUM(healstaken) As ihps_total
						FROM %s
						WHERE encid = '%s'
						AND %s",
						mysql_real_escape_string($TABLE_COMBATANT),
						mysql_real_escape_string($encid),
						$query_string
						)) or die(mysql_error());
		return $query;
	}

	//***GETTING SPECIFIC SEARCH FILTERS***
	function get_all_search_tiers() {
		global $TABLE_TIER;

		$query = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						ORDER BY tier DESC",
						mysql_real_escape_string($TABLE_TIER)
						)) or die(mysql_error());
		return $query;
	}

	function get_all_search_types() {
		global $TABLE_TYPE;

		$query = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						ORDER BY type DESC",
						mysql_real_escape_string($TABLE_TYPE)
						)) or die(mysql_error());
		return $query;
	}

	function get_all_search_dungeons() {
		global $TABLE_DUNGEON;

		$query = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						ORDER BY name ASC",
						mysql_real_escape_string($TABLE_DUNGEON)
						)) or die(mysql_error());
		return $query;
	}

	function get_all_search_encounters() {
		global $TABLE_ENCOUNTERLIST;

		$query  = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						ORDER BY name ASC",
						mysql_real_escape_string($TABLE_ENCOUNTERLIST)
						)) or die(mysql_error());
		return $query;
	}

	function get_all_search_encounterType() {
		global $TABLE_ENCOUNTERTYPE;

		$query = mysql_query(sprintf("SELECT DISTINCT *
						FROM %s
						ORDER BY type ASC",
						mysql_real_escape_string($TABLE_ENCOUNTERTYPE)
						)) or die(mysql_error());
		return $query;
	}

	//***GET ENCOUNTER DPS DATA***
	function get_encounter_dps($encounterIDs) {
		global $TABLE_COMBATANT;

		if ( !(isset($encounterIDs)) ) {
			$encounterIDs = "encid!=''";
		}


		$query = mysql_query(sprintf("SELECT DISTINCT encid, starttime, SUM( encdps ) As totaldps
						FROM %s
						WHERE ally='T'
						AND %s
						GROUP BY encid
						ORDER BY 2 DESC",
						mysql_real_escape_string($TABLE_COMBATANT),
						$encounterIDs
						)) or die(mysql_error());
		return $query;
	}

	//***GET ENCOUNTER HPS DATA***
	function get_encounter_hps($encounterIDs) {
		global $TABLE_COMBATANT;

		if ( !(isset($encounterIDs)) ) {
			$encounterIDs = "encid!=''";
		}

		$query = mysql_query(sprintf("SELECT DISTINCT encid, starttime, SUM( enchps ) As totalhps
						FROM %s
						WHERE ally='T'
						AND %s
						GROUP BY encid
						ORDER BY 2 DESC",
						mysql_real_escape_string($TABLE_COMBATANT),
						$encounterIDs
						)) or die(mysql_error());
		return $query;
	}

	//***GET ENCOUNTER INCDPS DATA***
	function get_encounter_idps($encounterIDs) {
		global $TABLE_COMBATANT;

		if ( !(isset($encounterIDs)) ) {
			$encounterIDs = "encid!=''";
		}

		$query = mysql_query(sprintf("SELECT DISTINCT encid, starttime, SUM( damagetaken ) As totalidps
						FROM %s
						WHERE ally='T'
						AND %s
						GROUP BY encid
						ORDER BY 2 DESC",
						mysql_real_escape_string($TABLE_COMBATANT),
						$encounterIDs
						)) or die(mysql_error());
		return $query;
	}
	
	//***GET ENCOUNTER INCHPS DATA***
	function get_encounter_ihps($encounterIDs) {
		global $TABLE_COMBATANT;

		if ( !(isset($encounterIDs)) ) {
			$encounterIDs = "encid!=''";
		}

		$query = mysql_query(sprintf("SELECT DISTINCT encid, starttime, SUM( healstaken ) As totalihps
						FROM %s
						WHERE ally='T'
						AND %s
						GROUP BY encid
						ORDER BY 2 DESC",
						mysql_real_escape_string($TABLE_COMBATANT),
						$encounterIDs
						)) or die(mysql_error());
		return $query;
	}

	//***GET P2P DATA PER ABILITY-ALL***
	function get_p2p_ability_data_all($encid, $query_string, $query_string2) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT DISTINCT *, SUM(damage) as damage_total,
										COUNT(*) as hits, SUM(CASE critical WHEN 'T' Then 1 Else 0 END) as crithits,
										MAX(damage) as maxhit, MIN(damage) as minhit,
										AVG(damage) as average, COUNT(DISTINCT stime) as duration
										FROM %s
										WHERE encid = '%s'
										AND %s
										AND %s
										AND swingtype != 13
										AND damage > 0
										GROUP BY attacker
										ORDER BY attacker ASC",
										mysql_real_escape_string($TABLE_SWINGS),
										mysql_real_escape_string($encid),
										$query_string,
										$query_string2
										)) or die(mysql_error());
		return $query;
	}

	//***GET P2P DATA PER ABILITY-INDIVIDUAL ABILITIES***
	function get_p2p_ability_data_abilities($encid, $query_string, $query_string2) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT DISTINCT *, SUM(damage) as damage_total,
										COUNT(*) as hits, SUM(CASE critical WHEN 'T' Then 1 Else 0 END) as crithits,
										MAX(damage) as maxhit, MIN(damage) as minhit,
										AVG(damage) as average, COUNT(DISTINCT stime) as duration
										FROM %s
										WHERE encid = '%s'
										AND %s
										AND %s
										AND swingtype != 13
										AND damage > 0
										GROUP BY attacker, attacktype
										ORDER BY attacktype ASC, damage_total DESC",
										mysql_real_escape_string($TABLE_SWINGS),
										mysql_real_escape_string($encid),
										$query_string,
										$query_string2
										)) or die(mysql_error());
		return $query;
	}

	//***GET P2P DATA PER DPS ABILITY-ALL***
	function get_p2p_ability_data_dps_all($encid, $query_string, $query_string2) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT DISTINCT *, SUM(damage) as damage_total,
										COUNT(*) as hits, SUM(CASE critical WHEN 'T' Then 1 Else 0 END) as crithits,
										MAX(damage) as maxhit, MIN(damage) as minhit,
										AVG(damage) as average, COUNT(DISTINCT stime) as duration
										FROM %s
										WHERE encid = '%s'
										AND %s
										AND %s
										AND (swingtype = 1 OR swingtype = 2)
										AND damage > 0
										GROUP BY attacker
										ORDER BY damage_total DESC",
										mysql_real_escape_string($TABLE_SWINGS),
										mysql_real_escape_string($encid),
										$query_string,
										$query_string2
										)) or die(mysql_error());
		return $query;
	}

	//***GET P2P DATA PER DPS ABILITY-INDIVIDUAL ABILITIES***
	function get_p2p_ability_data_dps_abilities($encid, $query_string, $query_string2) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT DISTINCT *, SUM(damage) as damage_total,
										COUNT(*) as hits, SUM(CASE critical WHEN 'T' Then 1 Else 0 END) as crithits,
										MAX(damage) as maxhit, MIN(damage) as minhit,
										AVG(damage) as average, COUNT(DISTINCT stime) as duration
										FROM %s
										WHERE encid = '%s'
										AND %s
										AND %s
										AND (swingtype = 1 OR swingtype = 2)
										AND damage > 0
										GROUP BY attacker, attacktype
										ORDER BY attacktype ASC, damage_total DESC",
										mysql_real_escape_string($TABLE_SWINGS),
										mysql_real_escape_string($encid),
										$query_string,
										$query_string2
										)) or die(mysql_error());
		return $query;
	}

	//***GET P2P DATA PER HPS ABILITY-ALL***
	function get_p2p_ability_data_hps_all($encid, $query_string) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT DISTINCT *, SUM(damage) as damage_total,
										COUNT(*) as hits, SUM(CASE critical WHEN 'T' Then 1 Else 0 END) as crithits,
										SUM(CASE WHEN special LIKE '%soverhealed' Then SUBSTRING_INDEX(special, ' ', 1)
										END) as overheal,
										MAX(damage) as maxhit, MIN(damage) as minhit,
										AVG(damage) as average, COUNT(DISTINCT stime) as duration
										FROM %s
										WHERE encid = '%s'
										AND %s
										AND (swingtype = 3)
										AND damage > 0
										GROUP BY attacker
										ORDER BY damage_total DESC",
										"%",
										mysql_real_escape_string($TABLE_SWINGS),
										mysql_real_escape_string($encid),
										$query_string
										)) or die(mysql_error());
		return $query;
	}

	//***GET P2P DATA PER HPS ABILITY-INDIVIDUAL ABILITIES***
	function get_p2p_ability_data_hps_abilities($encid, $query_string) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT DISTINCT *, SUM(damage) as damage_total,
										SUM(CASE WHEN special LIKE '%soverhealed' Then SUBSTRING_INDEX(special, ' ', 1)
										END) as overheal,
										COUNT(*) as hits, SUM(CASE critical WHEN 'T' Then 1 Else 0 END) as crithits,
										MAX(damage) as maxhit, MIN(damage) as minhit,
										AVG(damage) as average, COUNT(DISTINCT stime) as duration
										FROM %s
										WHERE encid = '%s'
										AND %s
										AND (swingtype = 3)
										AND damage > 0
										GROUP BY attacker, attacktype
										ORDER BY attacktype ASC, damage_total DESC",
										"%",
										mysql_real_escape_string($TABLE_SWINGS),
										mysql_real_escape_string($encid),
										$query_string
										)) or die(mysql_error());
		return $query;
	}

	//***GET P2P DATA PER INC DPS ABILITY-ALL***
	function get_p2p_ability_data_incdps_all($encid, $query_string, $query_string2) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT DISTINCT *, COUNT(*) as hits,
										SUM(CASE WHEN damage <= 0 Then 0 ELSE damage END) as damageCorrect,
										SUM(damage) as damage_total,
										SUM(CASE WHEN critical = 'T' Then 1 Else 0 END) as crithits,
										SUM(CASE WHEN damagestring = 'Resist' Then 1 Else 0 END) as resist,
										
										SUM(CASE WHEN damagestring = 'Parry' Then 1
											WHEN damagestring = 'Perry' Then 1 Else 0 
										END) as parry,
										
										SUM(CASE WHEN damagestring = 'Dodge' Then 1
											WHEN damagestring = 'Dogde' Then 1 Else 0 
										END) as dodge,
										
										SUM(CASE WHEN special LIKE '%sabsorbed' Then 1 Else 0 
										END) as absorb,
										
										SUM(CASE WHEN special LIKE 'Blocked%s' Then 1
											WHEN special LIKE '%sBlocked' Then 1 Else 0 
										END) as block,
											
										SUM(CASE WHEN special LIKE 'Deflected%s' Then 1
											WHEN special LIKE '%sDeflected' Then 1 Else 0 
										END) as deflect,
											
										SUM(CASE WHEN special LIKE 'Blocked%s' AND special NOT LIKE '%sabsorbed' Then SUBSTRING(special, 8)
											WHEN special LIKE 'Blocked%s' AND special LIKE '%sabsorbed' Then SUBSTRING(SUBSTRING_INDEX(special, ',', 1), 8)
											WHEN special LIKE '%sBlocked' AND special NOT LIKE '%sabsorbed' Then SUBSTRING_INDEX(special, ' ', 1)
											WHEN special LIKE '%sBlocked' AND special LIKE '%sabsorbed' Then SUBSTRING_INDEX(special, ',', 1)
										END) as blockdamage,
										
										SUM(CASE WHEN special LIKE 'Deflected%s' AND special NOT LIKE '%sabsorbed' Then SUBSTRING(special, 8)
											WHEN special LIKE 'Deflected%s' AND special LIKE '%sabsorbed' Then SUBSTRING(SUBSTRING_INDEX(special, ',', 1), 8)
											WHEN special LIKE '%sDeflected' AND special NOT LIKE '%sabsorbed' Then SUBSTRING_INDEX(special, ' ', 1)
											WHEN special LIKE '%sDeflected' AND special LIKE '%sabsorbed' Then SUBSTRING_INDEX(special, ',', 1)
										END) as deflectdamage,
										
										SUM(CASE WHEN special NOT LIKE 'Blocked%s' AND special LIKE '%sabsorbed' Then SUBSTRING_INDEX(special, ' ', 1)
											WHEN special NOT LIKE '%sBlocked' AND special LIKE '%sabsorbed' Then SUBSTRING_INDEX(special, ' ', 1)
											WHEN special NOT LIKE 'Deflected%s' AND special LIKE '%sabsorbed' Then SUBSTRING_INDEX(special, ' ', 1)
											WHEN special NOT LIKE '%sDeflected' AND special LIKE '%sabsorbed' Then SUBSTRING_INDEX(special, ' ', 1)
											WHEN special LIKE 'Blocked%s' AND special LIKE '%sabsorbed' Then SUBSTRING_INDEX(SUBSTRING(special, LOCATE(', ', special) + 1), ' ', 2)
											WHEN special LIKE '%sBlocked' AND special LIKE '%sabsorbed' Then SUBSTRING_INDEX(SUBSTRING(special, LOCATE(', ', special) + 1), ' ', 2)
											WHEN special LIKE 'Deflected%s' AND special LIKE '%sabsorbed' Then SUBSTRING_INDEX(SUBSTRING(special, LOCATE(', ', special) + 1), ' ', 2)
											WHEN special LIKE '%Deflected' AND special LIKE '%sabsorbed' Then SUBSTRING_INDEX(SUBSTRING(special, LOCATE(', ', special) + 1), ' ', 2)
										END) as absorbdamage,
										
										MAX(damage) as maxhit,
										MIN(damage) as minhit,
										AVG(damage) as average,
										COUNT(DISTINCT stime) as duration
										FROM %s
										WHERE encid = '%s'
										AND %s
										AND %s
										AND (swingtype != 3 OR swingtype != 13)
										GROUP BY victim
										ORDER BY damageCorrect DESC",
										"%","%","%","%","%","%","%","%","%","%",
										"%","%","%","%","%","%","%","%","%","%",
										"%","%","%","%","%","%","%","%","%","%",
										"%","%","%","%","%","%","%",
										mysql_real_escape_string($TABLE_SWINGS),
										mysql_real_escape_string($encid),
										$query_string,
										$query_string2
										)) or die(mysql_error());
		return $query;
	}

	//***GET P2P DATA PER INC DPS ABILITY-INDIVIDUAL ABILITIES***
	function get_p2p_ability_data_incdps_abilities($encid, $query_string, $query_string2) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT DISTINCT *, SUM(damage) as damage_total,
										SUM(CASE WHEN damage <= 0 Then 0 ELSE damage END) as damageCorrect,
										COUNT(*) as hits,
										CASE WHEN damage <= 0 Then 0 END as damage,
										SUM(CASE WHEN critical = 'T' Then 1 Else 0 END) as crithits,
										SUM(CASE WHEN damagestring = 'Resist' Then 1 Else 0 END) as resist,
										SUM(CASE WHEN damagestring = 'Parry' Then 1
										WHEN damagestring = 'Perry' Then 1 Else 0 END) as parry,
										SUM(CASE WHEN damagestring = 'Dodge' Then 1
										WHEN damagestring = 'Dogde' Then 1 Else 0 END) as dodge,
										SUM(CASE WHEN special LIKE '%s absorbed' Then 1 Else 0 END) as absorb,
										SUM(CASE WHEN special LIKE 'Blocked%s' Then 1 Else 0 END) as block,
										SUM(CASE WHEN special LIKE 'Deflected%s' Then 1 Else 0 END) as deflect,
										SUM(CASE WHEN special LIKE 'Blocked%s' AND special NOT LIKE '%sabsorbed' Then SUBSTRING(special, 8)
										WHEN special LIKE 'Blocked%s' AND special LIKE '%sabsorbed' Then SUBSTRING(SUBSTRING_INDEX(special, ',', 1), 8)
										END) as blockdamage,
										SUM(CASE WHEN special LIKE 'Deflected%s' AND special NOT LIKE '%sabsorbed' Then SUBSTRING(special, 8)
										WHEN special LIKE 'Deflected%s' AND special LIKE '%sabsorbed' Then SUBSTRING(SUBSTRING_INDEX(special, ',', 1), 8)
										END) as deflectdamage,
										SUM(CASE WHEN special NOT LIKE 'Blocked%s' AND special LIKE '%sabsorbed' Then SUBSTRING_INDEX(special, ' ', 1)
										WHEN special LIKE 'Blocked%s' AND special LIKE '%sabsorbed' Then SUBSTRING_INDEX(SUBSTRING(special, LOCATE(', ', special) + 1), ' ', 2)
										END) as absorbdamage,
										MAX(damage) as maxhit,
										MIN(damage) as minhit,
										AVG(damage) as average,
										COUNT(DISTINCT stime) as duration
										FROM %s
										WHERE encid = '%s'
										AND %s
										AND %s
										AND (swingtype != 3 OR swingtype != 13)
										GROUP BY victim, attacktype
										ORDER BY attacktype ASC, damageCorrect DESC",
										"%","%","%","%","%","%","%","%","%","%",
										"%","%","%","%","%",
										mysql_real_escape_string($TABLE_SWINGS),
										mysql_real_escape_string($encid),
										$query_string,
										$query_string2
										)) or die(mysql_error());
		return $query;
	}

	//***GET P2P DATA PER INC HPS ABILITY-ALL***
	function get_p2p_ability_data_inchps_all($encid, $query_string) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT DISTINCT *, SUM(damage) as damage_total,
										COUNT(*) as hits, SUM(CASE critical WHEN 'T' Then 1 Else 0 END) as crithits,
										SUM(CASE WHEN special LIKE '%soverhealed' Then SUBSTRING_INDEX(special, ' ', 1)
										END) as overheal,
										MAX(damage) as maxhit, MIN(damage) as minhit,
										AVG(damage) as average, COUNT(DISTINCT stime) as duration
										FROM %s
										WHERE encid = '%s'
										AND %s
										AND (swingtype = 3)
										AND damage > 0
										GROUP BY victim
										ORDER BY damage_total DESC",
										"%",
										mysql_real_escape_string($TABLE_SWINGS),
										mysql_real_escape_string($encid),
										$query_string
										)) or die(mysql_error());
		return $query;
	}

	//***GET P2P DATA PER INC HPS ABILITY-INDIVIDUAL ABILITIES***
	function get_p2p_ability_data_inchps_abilities($encid, $query_string) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT DISTINCT *, SUM(damage) as damage_total,
										COUNT(*) as hits, SUM(CASE critical WHEN 'T' Then 1 Else 0 END) as crithits,
										SUM(CASE WHEN special LIKE '%soverhealed' Then SUBSTRING_INDEX(special, ' ', 1)
										END) as overheal,
										MAX(damage) as maxhit, MIN(damage) as minhit,
										AVG(damage) as average, COUNT(DISTINCT stime) as duration
										FROM %s
										WHERE encid = '%s'
										AND %s
										AND (swingtype = 3)
										AND damage > 0
										GROUP BY victim, attacktype
										ORDER BY attacktype ASC, damage_total DESC",
										"%",
										mysql_real_escape_string($TABLE_SWINGS),
										mysql_real_escape_string($encid),
										$query_string
										)) or die(mysql_error());
		return $query;
	}

	//***GET P2P DATA ABILITIES USED IN ENCOUNTER BY PLAYERS***
	function get_p2p_ability_list($encid, $query_string) {
		global $TABLE_ATTACKTYPE;

		$query = mysql_query(sprintf("SELECT DISTINCT type
										FROM %s
										WHERE encid = '%s'
										AND %s
										GROUP BY type
										ORDER BY type ASC",
										mysql_real_escape_string($TABLE_ATTACKTYPE),
										mysql_real_escape_string($encid),
										$query_string
										)) or die(mysql_error());
		return $query;
	}

	//**************************************
	//
	//	ABILITY QUE QUERYS
	//
	//**************************************

	//***GET ABILITY QUE ALL ABILITY SWINGS - DPS***
	function get_abilityq_all_abilities_dps($encid, $playerNames, $query_mob_victim_string) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT *
										FROM %s
										WHERE encid='%s'
										AND %s
										AND %s
										AND (swingtype != 3 OR swingtype != 13)
										AND damage > 0
										ORDER BY attacker DESC, stime ASC",
										mysql_real_escape_string($TABLE_SWINGS),
										mysql_real_escape_string($encid),
										$playerNames,
										$query_mob_victim_string
										)) or die(mysql_error());
		return $query;
	}

	//***GET ABILITY QUE ALL ABILITY SWINGS - HPS***
	function get_abilityq_all_abilities_hps($encid, $playerNames) {
		global $TABLE_SWINGS;
		$query = mysql_query(sprintf("SELECT *
										FROM %s
										WHERE encid='%s'
										AND %s
										AND (swingtype = 3)
										AND damage > 0
										ORDER BY attacker DESC, stime ASC",
										mysql_real_escape_string($TABLE_SWINGS),
										mysql_real_escape_string($encid),
										$playerNames
										)) or die(mysql_error());
		return $query;
	}

	//***GET ABILITY QUE ALL ABILITY SWINGS - INCDPS***
	function get_abilityq_all_abilities_incdps($encid, $query_swing_player_victim_string, $query_swing_mob_attacker_string) {
		global $TABLE_SWINGS;
		$query = mysql_query(sprintf("SELECT *
										FROM %s
										WHERE encid = '%s'
										AND %s
										AND %s
										AND (swingtype != 3 OR swingtype != 13)
										AND damage > 0
										ORDER BY victim DESC, stime ASC",
										mysql_real_escape_string($TABLE_SWINGS),
										mysql_real_escape_string($encid),
										$query_swing_player_victim_string,
										$query_swing_mob_attacker_string
										)) or die(mysql_error());
		return $query;
	}

	//***GET ABILITY QUE ALL ABILITY SWINGS - INCHPS***
	function get_abilityq_all_abilities_inchps($encid, $query_swing_player_victim_string) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT *
										FROM %s
										WHERE encid = '%s'
										AND %s
										AND (swingtype = 3)
										AND damage > 0
										ORDER BY victim DESC, stime ASC",
										mysql_real_escape_string($TABLE_SWINGS),
										mysql_real_escape_string($encid),
										$query_swing_player_victim_string
										)) or die(mysql_error());
		return $query;
	}
	//***GET ABILITY QUE SPECIFIC ABILITY SWINGS - DPS***
	function get_abilityq_specific_abilities_dps($encid, $abilityNames, $playerNames, $query_mob_victim_string) {
		global $TABLE_SWINGS;
		$swing_query = mysql_query(sprintf("SELECT *
											FROM %s
											WHERE encid='%s'
											AND %s
											AND %s
											AND %s
											AND damage > 0
											ORDER BY attacker DESC, stime ASC",
											mysql_real_escape_string($TABLE_SWINGS),
											mysql_real_escape_string($encid),
											$abilityNames,
											$playerNames,
											$query_mob_victim_string
											)) or die(mysql_error());
		return $query;
	}

	//***GET ABILITY QUE SPECIFIC ABILITY SWINGS - HPS***
	function get_abilityq_specific_abilities_hps($encid, $abilityNames, $playerNames) {
		global $TABLE_SWINGS;
		$swing_query = mysql_query(sprintf("SELECT *
											FROM %s
											WHERE encid='%s'
											AND %s
											AND %s
											AND damage > 0
											ORDER BY attacker DESC, stime ASC",
											mysql_real_escape_string($TABLE_SWINGS),
											mysql_real_escape_string($encid),
											$abilityNames,
											$playerNames
											)) or die(mysql_error());
		return $query;
	}

	//***GET ABILITY QUE SPECIFIC ABILITY SWINGS - INCDPS***
	function get_abilityq_specific_abilities_incdps($encid, $abilityNames, $query_swing_player_victim_string, $query_swing_mob_attacker_string) {
		global $TABLE_SWINGS;
		$swing_query = mysql_query(sprintf("SELECT *
							FROM %s
							WHERE encid = '%s'
							AND %s
							AND %s
							AND %s
							AND (swingtype != 3 OR swingtype != 13)
							AND damage > 0
							ORDER BY victim DESC, stime ASC",
							mysql_real_escape_string($TABLE_SWINGS),
							mysql_real_escape_string($encid),
							$abilityNames,
							$query_swing_player_victim_string,
							$query_swing_mob_attacker_string
							)) or die(mysql_error());
		return $query;
	}

	//***GET ABILITY QUE SPECIFIC ABILITY SWINGS - INCHPS***
	function get_abilityq_specific_abilities_inchps($encid, $abilityNames, $query_swing_player_victim_string) {
		global $TABLE_SWINGS;

		$swing_query = mysql_query(sprintf("SELECT *
											FROM %s
											WHERE encid = '%s'
											AND %s
											AND %s
											AND (swingtype = 3)
											AND damage > 0
											ORDER BY victim DESC, stime ASC",
											mysql_real_escape_string($TABLE_SWINGS),
											mysql_real_escape_string($encid),
											$abilityNames,
											$query_swing_player_victim_string
											)) or die(mysql_error());
		return $query;
	}

	//**************************************
	//
	//	MODIFY PARSE QUERYS
	//
	//**************************************

	//***DELETE PARSE FROM DATABASE***
	function remove_parse($encid) {
		global $table_array;
		for ($table = 0; $table < count($table_array); $table++) {
			$query = mysql_query(sprintf("DELETE
											FROM %s
											WHERE encid='%s'",
											mysql_real_escape_string($table_array[$table]),
											mysql_real_escape_string($encid)
											)) or die(mysql_error());
		}
	}

	//***UPDATE ENCOUNTER TABLE DETAILS***
	function update_encounter_details($name, $notes, $parseType, $dbDate, $date, $encid) {
		global $TABLE_ENCOUNTER;
		$query = mysql_query(sprintf("UPDATE %s
										SET title='%s', notes='%s', parseType='%s', starttime=REPLACE(starttime,'%s', '%s')
										WHERE encid='%s'",
										mysql_real_escape_string($TABLE_ENCOUNTER),
										mysql_real_escape_string($name),
										mysql_real_escape_string($notes),
										mysql_real_escape_string($parseType),
										mysql_real_escape_string($dbDate),
										mysql_real_escape_string($date),
										mysql_real_escape_string($encid)
										)) or die(mysql_error());
		return $query;
	}

	//***UPDATE ENCOUNTER TABLE DETAILS-UPLOADER***
	function update_encounter_details_uploader($uploader, $encid) {
		global $TABLE_ENCOUNTER;
		$query = mysql_query(sprintf("UPDATE %s
										SET uploadedby='%s'
										WHERE encid='%s'",
										mysql_real_escape_string($TABLE_ENCOUNTER),
										mysql_real_escape_string($uploader),
										mysql_real_escape_string($encid))) or die(mysql_error());
		return $query;
	}

	//***UPDATE ENCOUNTER DATE ACROSS MULTIPLE TABLES***
	function update_encounter_dates($name, $dbDate, $date, $encid) {
		global $TABLE_ENCOUNTER;
		global $TABLE_ATTACKTYPE;
		global $TABLE_COMBATANT;
		global $TABLE_DAMAGETYPE;
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("UPDATE %s
										SET title='%s', starttime=REPLACE(starttime,'%s', '%s')
										WHERE encid='%s'",
										mysql_real_escape_string($TABLE_ENCOUNTER),
										mysql_real_escape_string($name),
										mysql_real_escape_string($dbDate),
										mysql_real_escape_string($date),
										mysql_real_escape_string($encid)
										)) or die(mysql_error());
		$query = mysql_query(sprintf("UPDATE %s
										SET starttime=REPLACE(starttime,'%s', '%s')
										WHERE encid='%s'",
										mysql_real_escape_string($TABLE_ATTACKTYPE),
										mysql_real_escape_string($dbDate),
										mysql_real_escape_string($date),
										mysql_real_escape_string($encid)
										)) or die(mysql_error());
		$query = mysql_query(sprintf("UPDATE %s
										SET starttime=REPLACE(starttime,'%s', '%s')
										WHERE encid='%s'",
										mysql_real_escape_string($TABLE_COMBATANT),
										mysql_real_escape_string($dbDate),
										mysql_real_escape_string($date),
										mysql_real_escape_string($encid)
										)) or die(mysql_error());
		$query = mysql_query(sprintf("UPDATE %s
										SET starttime=REPLACE(starttime,'%s', '%s')
										WHERE encid='%s'",
										mysql_real_escape_string($TABLE_DAMAGETYPE),
										mysql_real_escape_string($dbDate),
										mysql_real_escape_string($date),
										mysql_real_escape_string($encid)
										)) or die(mysql_error());
		$query = mysql_query(sprintf("UPDATE %s
										SET stime=REPLACE(stime,'%s', '%s')
										WHERE encid='%s'",
										mysql_real_escape_string($TABLE_SWINGS),
										mysql_real_escape_string($dbDate),
										mysql_real_escape_string($date),
										mysql_real_escape_string($encid)
										)) or die(mysql_error());
	}
	
	//**************************************
	//
	//	PLAYER PHP QUERYS
	//
	//**************************************
	
	function get_player_dps_abilities($encid, $playerName, $query_string) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT DISTINCT *, 
						SUM(damage) as damage_total,
						COUNT(*) as hits, 
						SUM(CASE critical WHEN 'T' Then 1 Else 0 END) as crithits,
						MAX(damage) as maxhit, 
						MIN(damage) as minhit,
						AVG(damage) as average, 
						COUNT(DISTINCT stime) as duration
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND %s
						AND swingtype != 13
						AND damage > 0
						GROUP BY attacker, attacktype
						ORDER BY attacktype ASC, damage_total DESC",
						mysql_real_escape_string($TABLE_SWINGS),
						mysql_real_escape_string($encid),
						$playerName,
						$query_string
						)) or die(mysql_error());
		return $query;
	}
	
	function get_player_hps_abilities($encid, $playerName) {
		global $TABLE_SWINGS;

		$query = mysql_query(sprintf("SELECT DISTINCT *, 
						SUM(damage) as damage_total,
						COUNT(*) as hits, 
						SUM(CASE critical WHEN 'T' Then 1 Else 0 END) as crithits,
						MAX(damage) as maxhit, 
						MIN(damage) as minhit,
						AVG(damage) as average, 
						COUNT(DISTINCT stime) as duration
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND swingtype = 3
						AND damage > 0
						GROUP BY attacker, attacktype
						ORDER BY attacktype ASC, damage_total DESC",
						mysql_real_escape_string($TABLE_SWINGS),
						mysql_real_escape_string($encid),
						$playerName
						)) or die(mysql_error());
		return $query;
	}
	
	function get_player_idps_abilities($encid, $query_swing_player_victim_string, $query_swing_mob_attacker_string) {
		global $TABLE_SWINGS;
		
		$query = mysql_query(sprintf("SELECT DISTINCT *, 
						COUNT(*) as hits,
						SUM(CASE critical WHEN 'T' Then 1 Else 0 END) as crithits,
						SUM(damage) as damage_total,
						MAX(damage) as maxhit,
						MIN(damage) as minhit,
						AVG(damage) as average,
						COUNT(DISTINCT stime) as duration
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND %s
						AND damage > 0
						AND (swingtype != 3 OR swingtype != 13)
						GROUP BY victim, attacktype
						ORDER BY damage_total DESC",
						mysql_real_escape_string($TABLE_SWINGS),
						mysql_real_escape_string($encid),
						$query_swing_player_victim_string,
						$query_swing_mob_attacker_string
						)) or die(mysql_error());
		return $query;
	}
	
	function get_player_ihps_abilities($encid, $query_swing_player_victim_string) {
		global $TABLE_SWINGS;
		
		$query = mysql_query(sprintf("SELECT DISTINCT *, 
						COUNT(*) as hits,
						SUM(CASE critical WHEN 'T' Then 1 Else 0 END) as crithits,
						SUM(damage) as damage_total,
						MAX(damage) as maxhit,
						MIN(damage) as minhit,
						AVG(damage) as average,
						COUNT(DISTINCT stime) as duration
						FROM %s
						WHERE encid = '%s'
						AND %s
						AND damage > 0
						AND swingtype = 3
						GROUP BY victim, attacktype
						ORDER BY damage_total DESC",
						mysql_real_escape_string($TABLE_SWINGS),
						mysql_real_escape_string($encid),
						$query_swing_player_victim_string
						)) or die(mysql_error());
		return $query;
	}
?>