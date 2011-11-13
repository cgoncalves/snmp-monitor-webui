<?php

  require_once('config.php');
  include("rrd.php");

  $db_conn = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $db_conn);

  if (!$db_conn) {
	  error_log('Unable to connect to server: ' . mysql_error());
	  die ('Unable to connect to server: ' . mysql_error());
  }

  $result_servers = mysql_query("SELECT Id, Name, IP FROM servers");

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
        if($params[1] == "%IP")     
          $ret = shell_exec("./$plugins_dir" . $params[0] . " " . $server->IP . " 2>&1");
      }

      // Gets the metric value from the string returned by the plugin,
      // updates the RRD and checks the thresholds, updating the status if necessary
      if(!is_null($ret) && is_string($ret) && !empty($ret) )
      {
        $value = explode(" ", $ret);
        $oid = $value[0];
        $value = $value[sizeof($value) - 1];

        if(sizeof(explode(".", $value)) > 1)
          $value = floatval($value);
        else
          $value = intval($value);

        // Adds the obtained value to the RRD of the pair (server, metric)
        updateRRD($server->Id, $metric->Id, time(), $value);

        // Checks the thresholds

        if($value > $server_metric->Threshold_max1 || $value > $server_metric->Threshold_max2 || $value < $server_metric->Threshold_min1 || $value < $server_metric->Threshold_min2)
        {

          // Status is updated to CRITICAL if value exceeds threshold 2 max or is lower than the threshold 2 min
          if($value > $server_metric->Threshold_max2 || $value > $server_metric->Threshold_min2)
          {
            if($server_metric->Status != "CRITICAL")
              updateStatus($server_metric->Id, "CRITICAL");
          }
          // Status is updated to WARNING if value exceeds threshold 1 max or is lower than the threshold 1 min
          elseif($value > $server_metric->Threshold_max1 || $value > $server_metric->Threshold_min1)
          {
            if($server_metric->Status != "WARNING")
              updateStatus($server_metric->Id, "WARNING");
          } 

          // Adds the event to the events log
          addEventLog($server->Id, $server_metric->RefIDMetric, $oid, $server_metric->Threshold_min1, $server_metric->Threshold_min2, $server_metric->Threshold_max1, $server_metric->Threshold_max2, $value);

          // Sends a notification to the admin about this event
          sendNotification($server->Id, $server->IP, $server->Name, $metric->Name, $oid, $server_metric->Threshold_min1, $server_metric->Threshold_min2, $server_metric->Threshold_max1, $server_metric->Threshold_max2, $value);
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

  function sendNotification($server_id, $server_ip, $server_name, $metric_name, $oid, $threshold_min1, $threshold_min2, $threshold_max1, $threshold_max2, $value)
  {
    $notifications = mysql_query("SELECT RefIdNotification, Receiver FROM servers_notifications WHERE RefIDServer=$server_id");
    while($notification = mysql_fetch_object($notifications))
    {
      $notification_name = mysql_query("SELECT Name FROM notifications WHERE ID=$notification->RefIdNotification");
      $name = mysql_fetch_object($notification_name);

      if($name->Name == "E-mail")
      {
        $to = $notification->Receiver;
        $subject = "Monitoring Event";

        $message = "Metric \"$metric_name\" ($oid) of server \"$server_name\" ($server_ip)";
        if($value > $threshold_max2)
          $message .= " has exceeded threshold max 2 of $threshold_max2";
        elseif($value > $threshold_max1)
          $message .= " has exceeded threshold max 1 of $threshold_max1";
        elseif($value < $threshold_min2)
          $message .= " is lower than threshold min 2 of $threshold_min2";
        elseif($value > $threshold_min2)
          $message .= " is lower than threshold min 1 of $threshold_min1";
        $message .=  " (value = $value)";

        $from = "monitor@girs.com";
        $headers = "From: " . $from;

        if(mail($to, $subject, $message, $headers))
          echo "<br><br>Mail sent!";
        else
          echo "<br><br>Mail not sent!";
      }
    }   
  } 

?>
