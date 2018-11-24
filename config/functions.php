<?php
	$GLOBALS['REQUEST_MICROTIME'] = microtime( TRUE );

	//*************Get Load Time******************************
	function load_time($buffer) {
		return str_replace('{microtime}',round( microtime( TRUE ) - $GLOBALS['REQUEST_MICROTIME'], 4 ), $buffer);
	}

	//*************Time Formatting******************************

	function minutes($seconds) {
		return sprintf( "%02.2d:%02.2d", floor($seconds / 60), $seconds % 60);
	}

	function timeFormat($date) {
		return sprintf(date('h:i:s', strtotime($date)));
	}

	function seconds($start, $finish) {
		$start = strtotime($start);
		$finish = strtotime($finish);

		return ($finish - $start);
	}

	function addSeconds($time, $second) {
		$time = date('h:i:s', strtotime($time) + $second);
		return $time;
	}

	function timeOnlyFormat($date) {
		return sprintf(date('h:i:s', strtotime($date)));
	}

	//*************Date Formatting******************************
	function raidFormat($time, $second) {
		$time = date('Y-m-d H:i:s', strtotime($time) + $second);
		return $time;
	}

	function dateFormat($date) {
		return sprintf(date('m-d-Y @ h:i:s', strtotime($date)));
	}

	function dateShortFormat($date) {
		return sprintf(date('m-d-Y', strtotime($date)));
	}

	function dateOnlyFormat($date) {
		return sprintf(date('m/d/Y', strtotime($date)));
	}

	//*************Get Month/Day/Year******************************
	function getDay($date) {
		return sprintf(date('d', strtotime($date)));
	}

	function getMonth($date) {
		return sprintf(date('m', strtotime($date)));
	}

	function getYear($date) {
		return sprintf(date('Y', strtotime($date)));
	}

	//*************Database Formatting******************************
	function toDBFormat($date) {
		return sprintf(date('Y-m-d', strtotime($date)));
	}

	function toDBTimeFormat($date) {
		return sprintf(date('H:i:s', strtotime($date)));
	}

	//*************Ordinal Number Formatting************************
	function toOrdinal($number) {
		$ends = array('th','st','nd','rd','th','th','th','th','th','th');

		if (($number %100) >= 11 && ($number%100) <= 13) {
		   $abbreviation = $number. 'th';
		} else {
		   $abbreviation = $number. $ends[$number % 10];
		}

		return $abbreviation;
	}

	//*************Ranking Encounter Performance************************
	function rankEncounters($encounters, $hpsencounters, $dpsencounters, $encid) {
		$rank_array 		= array(100,88,74,60,52,40,40,40,40, 40); // First to Tenth Place
		$numOfRankings		= count($rank_array);
		$numOfEncounters	= count($encounters);
		$dps_array			= array();
		$hps_array			= array();
		$time_array			= array();
		$idps_array			= array();
		$overall_array		= array();
		$final_array		= array();
		$rank_dps			= 0;
		$rank_hps			= 0;
		$rank_idps			= 0;
		$rank_time			= 0;
		$rank_overall		= 0;
		$rank				= 0;

		for ($count = 0; $count < $numOfEncounters; $count++) {
			$enc = $encounters[$count]['encid'];

			$hps_array[$enc] 	= $hpsencounters[$enc];
			$idps_array[$enc] 	= $dpsencounters[$enc];
			$dps_array[$enc] 	= $encounters[$count]['encdps'];
			$time_array[$enc] 	= $encounters[$count]['duration'];
		}

		arsort	($dps_array);
		arsort	($hps_array);
		asort	($time_array);
		asort	($idps_array);

		//echo "<br>DPS";
		foreach ($dps_array as $key => $val) {
		//	echo "<br>".$key." = ".$val;
		}
		//echo "<br>HPS";
		foreach ($hps_array as $key => $val) {
		//	echo "<br>".$key." = ".$val;
		}
		//echo "<br>TIME";
		foreach ($time_array as $key => $val) {
		//	echo "<br>".$key." = ".$val;
		}
		//echo "<br>IDPS";
		foreach ($idps_array as $key => $val) {
		//	echo "<br>".$key." = ".$val;
		}

		foreach ($dps_array as $key => $val) {
		    if ($rank < $numOfRankings) {
		    	$dps_array[$key] = $rank_array[$rank];
		    } else {
		    	$dps_array[$key] = 0;
		    }

		    $rank++;
		}

		$rank = 0;

		foreach ($hps_array as $key => $val) {
			if ($rank < $numOfRankings) {
				$hps_array[$key] = $rank_array[$rank];
			} else {
				$hps_array[$key] = 0;
			}

			$rank++;
		}

		$rank = 0;

		foreach ($idps_array as $key => $val) {
			if ($rank < $numOfRankings) {
				$idps_array[$key] = $rank_array[$rank];
			} else {
				$idps_array[$key] = 0;
			}

			$rank++;
		}

		$rank = 0;

		foreach ($time_array as $key => $val) {
			if ($rank < $numOfRankings) {
				$time_array[$key] = $rank_array[$rank];
			} else {
				$time_array[$key] = 0;
			}

			$rank++;

			$overall_array[$key] = $dps_array[$key] + $hps_array[$key] + $idps_array[$key] + $time_array[$key];
		}

		$rank_dps			= array_key_index($dps_array,$encid) + 1;
		$rank_hps			= array_key_index($hps_array,$encid) + 1;
		$rank_idps			= array_key_index($idps_array,$encid) + 1;
		$rank_time			= array_key_index($time_array,$encid) + 1;
		$rank_overall		= array_key_index($overall_array,$encid) + 1;

		array_push($final_array, $rank_time, $rank_dps, $rank_hps, $rank_idps, $rank_overall);

		return $final_array;
	}

	function array_key_index($arr, $key) {
	    $i = 0;
	    foreach(array_keys($arr) as $k) {
	        if($k == $key)
	        	return $i;
	        $i++;
	    }
	}
?>