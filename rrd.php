<?php

	/* Creates a new RRD (Round Robin Database) for the pair (server, metric).
		 Returns TRUE on sucess or FALSE on failure. */
	function createRRD($server_id, $metric_id, $data_type, $heartbeat)
	{
	  // Filename is <serverID_metricID>.rdd
		$filename = $server_id . "_" . $metric_id . ".rrd";
		
		// Heartbeat (in seconds)
		$options[0] = "--step";
		$options[1] = $heartbeat;
		
		// Data Source (min and max Unknown)	
		$options[2] = "DS:metric:" . $data_type . ":" . $heartbeat * 2 . ":U:U";
		
		// Steps and rows for RRA's of 1 hour, 1 day, 1 month and 1 year
		$steps_rows = array(array("1", "12"), array("12", "24"), array("288", "30"), array("8640", "12"));
		
		// Round Robin Archives (CF = AVERAGE, xff = 0.5, steps and rows dfined in array $steps_rows)
		for($i = 0; $i < sizeof($steps_rows); $i++)
		{
			$options[$i + 3] = "RRA:AVERAGE:0.5:" . $steps_rows[$i][0] . ":" . $steps_rows[$i][1];

		}
		
		return rrd_create($filename, $options, count($options));
	}
	
	
	/* Stores a new value into the RRD of the pair (server, metric).
		 Returns TRUE on sucess or FALSE on failure. */
	function updateRRD($server_id, $metric_id, $value, $timestamp)
	{
	  // Filename is <serverID_metricID>.rdd
		$filename = $server_id . "_" . $metric_id . ".rrd";
		
		// N -> current time
		$options = $timestamp . ":" . $value;
		
		print $options . "<br>";
		
		return rrd_update($filename, $options);
	}


	/* Creates an image for a particular data from the RRD of the pair (server, metric).
		 Returns an array with information about the generated image or FALSE on failure. */
	function graphRRD($server_id, $metric_id, $metric_name, $start, $units, $colour, $heartbeat)
	{
	  // Filename is <serverID_metricID>.rdd
		$filename = $server_id . "_" . $metric_id . ".png";
		
		/*
		if($periodicity == "daily")
			$period = "-1d";
		else if($periodicity == "weekly")
			$period = "-1w";
		else if($periodicity == "monthly")
			$period = "-1m";
			*/
		
		$options = array("--start", $start, "--vertical-label", $units, "--step", $heartbeat,
										 "DEF:variable=" . $server_id . "_" . $metric_id . ".rrd:metric:AVERAGE",
										 "LINE1:variable" . $colour  . ":" . $metric_name . "\\r",
										 "COMMENT:\\n\\n",
										 "GPRINT:variable:AVERAGE:Avg " . $metric_name . "\: %.2lf %S" . $units . "\\r",
										 "GPRINT:variable:MIN:Min " . $metric_name . "\: %.2lf %S" . $units . "\\r",
										 "GPRINT:variable:MAX:Max " . $metric_name . "\: %.2lf %S" . $units . "\\r",
										);
		
		return rrd_graph($filename, $options, count($options));
	}
	
?>
