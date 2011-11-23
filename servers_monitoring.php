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

      $n = 0;
      for($i = 0; $i < sizeof($params); $i++)
      {
        if((strcasecmp(substr($params[0], 0, strlen("snmp")), "snmp") == 0) && ($i == 1))
        {
          if(strcasecmp(substr($params[$i], 0, strlen("%IP")), "%IP") != 0)
            $args[$n] = $params[$i];
          else
          {
            $args[$n] = "public";
            $n++;
          }
        }

        if(strcasecmp($params[$i], "%IP") == 0)
          $args[$n] = $server->IP;
        elseif(strcasecmp(substr($params[$i], 0, strlen("%IP")), "%IP") == 0)
          $args[$n] = $server->IP . ":" . substr($params[$i], strlen("%IP") * -1);
        else
          $args[$n] = $params[$i];

        $n++;
      }

      $command = $plugins_dir . $args[0] . " ";
      for($i = 1; $i < sizeof($args); $i++)
      { 
        $command .= $args[$i] . " ";
        if($i == sizeof($args) - 1)
          $command .= "2>&1";
      }

      // Executes the plugin
      $ret = shell_exec($command);

      if(strcasecmp(substr($params[0], 0, strlen("snmp")), "snmp") == 0)
      {
        $ret = explode(" ", $ret);

        $oid = $ret[0];
        if($ret[1] != NULL)
          $value = floatval($ret[1]);
        else
          $value = NULL;
      }
      else
        $value = $ret;

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

        if((!is_null($max2) && $value > $max2) || (!is_null($min2) && $value < $min2) || (!is_null($max1) && $value > $max1) || (!is_null($min1) && $value < $min1))
        {
          // Status is updated to CRITICAL if value exceeds threshold 2 max or is lower than the threshold 2 min
          if((!is_null($max2) && $value > $max2) || (!is_null($min2) && $value < $min2))
          {
            if($server_metric->Status != "CRITICAL")
              updateStatus($server_metric->Id, "CRITICAL");
          }
          // Status is updated to WARNING if value exceeds threshold 1 max or is lower than the threshold 1 min
          elseif((!is_null($max1) && $value > $max1) || (!is_null($min1) && $value < $min1))
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
    $sql_result = mysql_query("SELECT Ack FROM eventlogs WHERE RefIDServer=$server_id AND IDMetric=$metric_id AND OID=$oid AND Threshold_min1=$min1 AND Threshold_min2=$min2 AND Threshold_max1=$max1 AND Threshold_max2=$max2 AND Value=$value");
   
    $insert = true;

    if(mysql_num_rows($sql_result) >= 1)
    {
      while($result = mysql_fetch_object($sql_result))
      {
        if($result->Ack == 0)
          $insert = false;
      }
    } 

    if(insert == true)
    {
      $ret = mysql_query("INSERT INTO eventlogs (RefIDServer, IDMetric, OID, Threshold_min1, Threshold_min2, Threshold_max1, Threshold_max2, Value, Ack) VALUES ('$server_id', '$metric_id', '$oid', '$min1', '$min2', '$max1', '$max2', $value, '0')");
    }
  }

?>
