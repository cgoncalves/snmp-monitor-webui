<?php

  require_once('config.php');
  include("rrd.php");
  include("notification.php");

  $db_conn = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $db_conn);

  if (!$db_conn) {
	  error_log('Unable to connect to server: ' . mysql_error());
	  die ('Unable to connect to server: ' . mysql_error());
  }

  $result_servers = mysql_query("SELECT Id, Name, IP FROM servers", $db_conn);

  if (!$result_servers) {
	  error_log('Unable to query database server: ' . mysql_error());
	  die ('Unable to query database server');
  }

  // For all servers
  while($server = mysql_fetch_object($result_servers)) {

    $server_metrics = mysql_query("SELECT Id, RefIDMetric, Threshold_max1, Threshold_max2, Threshold_min1, Threshold_min2, Status FROM servers_metrics WHERE RefIDServer=$server->Id");

    // For all metrics of the server
	  while ($server_metric = mysql_fetch_object($server_metrics)) {
      
      $metric = mysql_query("SELECT Id, Name, Parameters FROM metrics WHERE Id=$server_metric->RefIDMetric");
      $metric = mysql_fetch_object($metric);

      // Gets the parameters for this metric
      $params = explode(" ", $metric->Parameters);
      $plugin_name = explode("_", $params[0]);

      // Executes the plugin for this metric
      if($plugin_name[0] == "snmp")
      {
        for($i = 0; $i < sizeof($params); $i++)
        {
          if($params[$i] == "%IP")
            $args[$i] = $server->IP;
          else
            $args[$i] = $params[$i];
        }        
  
        $command = "php $plugins_dir" . $args[0] . ".php ";
        for($i = 0; $i < sizeof($args); $i++)
        { 
          $command .= $args[$i] . " ";
          if($i == sizeof($args) - 1)
            $command .= "2>&1";
        }

        $ret = shell_exec($command);
        $ret = explode(" ", $ret);

        $oid = $ret[0];
        $value = intval($ret[1]);
      }
      else
      {
        $value = shell_exec("php $plugins_dir" . $params[0] . ".php 2>&1");
      }

      // Updates the RRD with the value returned by the plugin
      // and checks the thresholds, updating the status if necessary
      if(!is_null($value))
      {
        // Adds the obtained value to the RRD of the pair (server, metric)
        $ret = updateRRD($server->Id, $metric->Id, time(), $value);

        // Checks the thresholds
        $max1 = $server_metric->Threshold_max1;
        $max2 = $server_metric->Threshold_max2;
        $min1 = $server_metric->Threshold_min1;
        $min2 = $server_metric->Threshold_min2;

        if(($max2 > -1 && $value > $max2) || ($min2 > -1 && $value < $min2) || ($max1 > -1 && $value > $max1) || ($min1 > -1 && $value < $min1))
        {
          // Status is updated to CRITICAL if value exceeds threshold 2 max or is lower than the threshold 2 min
          if(($max2 > -1 && $value > $max2) || ($min2 > -1 && $value < $min2))
          {
            if($server_metric->Status != "CRITICAL")
              updateStatus($server_metric->Id, "CRITICAL");
          }
          // Status is updated to WARNING if value exceeds threshold 1 max or is lower than the threshold 1 min
          elseif(($max1 > -1 && $value > $max1) || ($min1 > -1 && $value < $min1))
          {
            if($server_metric->Status != "WARNING")
              updateStatus($server_metric->Id, "WARNING");
          } 

          // Adds the event to the events log
          addEventLog($server->Id, $server_metric->RefIDMetric, $oid, $min1, $min2, $max1, $max2, $value);

          // Sends a notification to the admin about this event
          sendNotification($server->Id, $server->IP, $server->Name, $metric->Name, $oid, $min1, $min2, $max1, $max2, $value);
        }
        else
        {
          if($server_metric->Status != "OK")
            updateStatus($server_metric->Id, "OK");  
        }
      }
      // If no value is returned by the plugin, updates the status to ERROR
      else
      {
        if($server_metric->Status != "ERROR")
          updateStatus($server_metric->Id, "ERROR");
      }
	  }
  }
  mysql_close($db_conn);

  // Updates the status of the pair (server, metric)
  function updateStatus($id, $new_status)
  {
    mysql_query("UPDATE servers_metrics SET Status = '$new_status' WHERE Id = '$id'");
  }

  // Adds the event to the eventlogs table of the DB
  function addEventLog($server_id, $metric_id, $oid, $min1, $min2, $max1, $max2, $value)
  {
    $ret = mysql_query("INSERT INTO eventlogs (RefIDServer, IDMetric, OID, Threshold_min1, Threshold_min2, Threshold_max1, Threshold_max2, Value, Ack) VALUES ('$server_id', '$metric_id', '$oid', '$min1', '$min2', '$max1', '$max2', $value, '0')");
  }

?>
