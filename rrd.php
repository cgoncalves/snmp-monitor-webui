<?php

	/* Creates a new RRD (Round Robin Database) for the pair (server, metric).
		 Returns TRUE on sucess or FALSE on failure. */
	function createRRD($server_id, $metric_id, $data_type, $step_value)
	{
		if(is_null($server_id) || !is_integer($server_id) || $server_id < 1 || is_null($metric_id) || !is_integer($metric_id) || $metric_id < 1 || empty($data_type) || !is_string($data_type))
			return FALSE;

		// Heartbeat (in seconds)
		if(!empty($step_value) && is_integer($step_value))
		{
			$options[0] = "--step";
			$options[1] = $step_value;
			$heartbeat = $step_value * 2;
		}
		else
		{
			$heartbeat = 300 * 2;	// default step is 300 seconds (5 min)
		}
		
		// Data Source (min and max Unknown)	
		$options[2] = "DS:metric:$data_type:$heartbeat:U:U";
		
		// Steps and rows for RRA's of 1 hour, 1 day, 1 month and 1 year
		$steps_rows = array(array("1", "12"), array("12", "24"), array("288", "30"), array("8640", "12"));
		
		// Round Robin Archives (CF = AVERAGE, xff = 0.5, steps and rows dfined in array $steps_rows)
		for($i = 0; $i < sizeof($steps_rows); $i++)
		{
			$options[$i + 3] = "RRA:AVERAGE:0.5:" . $steps_rows[$i][0] . ":" . $steps_rows[$i][1];
		}
		
		return rrd_create(filenameRRD($server_id, $metric_id), $options, count($options));
	}
	
	
	/* Stores a new value into the RRD of the pair (server, metric).
		 Returns TRUE on sucess or FALSE on failure. */
	function updateRRD($server_id, $metric_id, $timestamp, $value)
	{
		if(is_null($server_id) || !is_integer($server_id) || $server_id < 1 || is_null($metric_id) || !is_integer($metric_id) || $metric_id < 1 || is_null($value) || !is_integer($value))
			return FALSE;
		
		if(is_null($timestamp) || (is_integer($timestamp) && $timestamp < 0) || (is_string($timestamp) && $timestamp != "N"))
			$timestamp = "N"; 		// N -> current time
		
		$options = "$timestamp:$value";
		
		return rrd_update(filenameRRD($server_id, $metric_id), $options);
	}


	/* Creates an image for a particular data from the RRD of the pair (server, metric).
		 Returns an array with information about the generated image or FALSE on failure. */
	function graphRRD($server_id, $metric_id, $metric_name, $start, $units, $colour)
	{
		if(is_null($server_id) || !is_integer($server_id) || $server_id < 1 || is_null($metric_id) || !is_integer($metric_id) || $metric_id < 1 || empty($colour) || !is_string($colour) || $colour[0] != '#' || strlen($colour) != 7)
			return FALSE;
		
		$i = 0;
		
		if(!is_null($start) && is_integer($start))
		{
			$options[0] = "--start";
			$options[1] = $start;
			$i = 2;
		}
		
		$options[0 + $i] = "--vertical-label";
		$options[1 + $i] = $units;
		$options[2 + $i] = "DEF:variable=" . filenameRRD($server_id, $metric_id) . ":metric:AVERAGE";
		$options[3 + $i] = "LINE1:variable$colour:$metric_name\\r";
		$options[4 + $i] = "COMMENT:\\r";
		$options[5 + $i] = "GPRINT:variable:AVERAGE:Avg $metric_name\: %.2lf %S$units\\r";
		$options[6 + $i] = "GPRINT:variable:MIN:Min $metric_name\: %.2lf %S$units\\r";
		$options[7 + $i] = "GPRINT:variable:MAX:Max $metric_name\: %.2lf %S$units\\r";
		
		return rrd_graph(filenameGraph($server_id, $metric_id), $options, count($options));
	}
	
	function filenameRRD($server_id, $metric_id)
	{
		require('config.php');
	
		// Filename is "rrd/<serverID_metricID>.rdd"
		return $rrd_dir . $server_id . "_$metric_id.rrd";
	}
	
	function filenameGraph($server_id, $metric_id)
	{
		require('config.php');
	
		// Filename is "graphs/<serverID_metricID>.png"
		return $graphs_dir . $server_id . "_$metric_id.png";
	}
?>
