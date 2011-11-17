<?php

  require_once('config.php');
  include("notification.php");

  $db_conn = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $db_conn);

  if (!$db_conn) {
	  error_log('Unable to connect to server: ' . mysql_error());
	  die ('Unable to connect to server: ' . mysql_error());
  }

  $result_events = mysql_query("SELECT RefIDServer, IDMetric, OID, Threshold_min1, Threshold_min2, Threshold_max1, Threshold_max2, Ack FROM eventlogs", $db_conn);

  if (!$result_events) {
	  error_log('Unable to query database server: ' . mysql_error());
	  die ('Unable to query database server');
  }

  // For all events at eventslog
  while($event = mysql_fetch_object($result_events)) {

    // If the event has not been acknowlegde yet
    // the notification(s) defined for the associated server are sent.
    if($event->Ack == 0)
    {
      $result_server = mysql_query("SELECT Id, Name, IP FROM servers WHERE Id=$event->RefIDServer");
      $server = mysql_fetch_object($result_server);

      if($event->IDMetric > -1)
      {
        $result_metric = mysql_query("SELECT Name FROM metrics WHERE Id=$event->IDMetric");
        $metric = mysql_fetch_object($result_metric);
        $metric_name = $metric->Name;
      }
      else
        $metric_name = "Trap";

      $oid = $event->OID;
      $min1 = $event->Threshold_min1;
      $min2 = $event->Threshold_min2;
      $max1 = $event->Threshold_max1;
      $max2 = $event->Threshold_max2;

      sendNotification($server->Id, $server->IP, $server->Name, $metric_name, $oid, $min1, $min2, $max1, $max2, $value);
    }
  }

?>
