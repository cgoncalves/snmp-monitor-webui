<?php

	/* Creates a new RRD (Round Robin Database) for the pair (server, metric).
		 Returns TRUE on sucess or FALSE on failure. */
	function createRRD($server_id, $metric_id, $data_type, $step_value)
	{
		if(is_null($server_id) || intval($server_id) < 1 || is_null($metric_id) || intval($metric_id) < 1 || empty($data_type) || !is_string($data_type))
			return FALSE;

		if(!empty($step_value))
		{
			$options[0] = "--step";
			$options[1] = $step_value;
		}
		else
      $step_value = 300;  // default step is 300 seconds (5 min)

		// Heartbeat (in seconds)
		$heartbeat = intval($step_value) * 2;	
		
		// Data Source (min and max Unknown)	
		$options[2] = "DS:metric:$data_type:$heartbeat:U:U";
		
		// Steps and rows for RRA's of 1 hour, 1 day, 1 month and 1 year
		$steps_1h = 1;
    $rows_1h = 60*60/$step_value;
    $steps_1day = $rows_1h;
    $rows_1day = 24*60*60/($steps_1day*$step_value);
    $steps_1month = $rows_1day;
    $rows_1month = 30*24*60*60/($steps_1month*$step_value);
    $steps_1year = $rows_1day;
    $rows_1year = 365*30*24*60*60/($steps_1year*$step_value);

    $steps_rows = array(array($steps_1h, $rows_1h), array($steps_1day, $rows_1day), array($steps_1month, $rows_1month), array($steps_1year, $rows_1year));
		
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
		if(is_null($server_id) || intval($server_id) < 1 || is_null($metric_id) || intval($metric_id) < 1 || is_null($value))
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
		if(is_null($server_id) || $server_id < 1 || is_null($metric_id) || $metric_id < 1 || empty($colour) || !is_string($colour) || $colour[0] != '#' || strlen($colour) != 7)
			return FALSE;

		$i = 0;
		
		if(!is_null($start))
		{
			$options[0] = "--start";
			$options[1] = $start;
			$i = 2;
		}
		
		$options[0 + $i] = "--vertical-label=$units";
		$options[1 + $i] = "DEF:variable=" . filenameRRD($server_id, $metric_id, $start) . ":metric:AVERAGE";
		$options[2 + $i] = "LINE1:variable$colour:$metric_name\\r";
		$options[3 + $i] = "COMMENT:\\r";

    if($units == "%")
      $units = "%%";

		$options[4 + $i] = "GPRINT:variable:AVERAGE:Avg $metric_name\: %.2lf %S$units\\r";
		$options[5 + $i] = "GPRINT:variable:MIN:Min $metric_name\: %.2lf %S$units\\r";
		$options[6 + $i] = "GPRINT:variable:MAX:Max $metric_name\: %.2lf %S$units\\r";

		return rrd_graph(filenameGraph($server_id, $metric_id, $start), $options, count($options));
  } 
	
	function filenameRRD($server_id, $metric_id)
	{
		require('config.php');
	
		// Filename is "rrd/<serverID_metricID>.rdd"
		return $rrd_dir . $server_id . "_$metric_id.rrd";
	}
	
	function filenameGraph($server_id, $metric_id, $start)
	{
		require('config.php');
	
		// Filename is "graphs/<serverID_metricID>.png"
		return $graphs_dir . $server_id . "_" . $metric_id . "_" . $start . ".png";
	}
?>
