<?php

  require_once('config.php');
  include("rrd.php");

  $db_conn = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $db_conn);

  if (!$db_conn) {
	  error_log('Unable to connect to server: ' . mysql_error());
	  die ('Unable to connect to server: ' . mysql_error());
  }

  $result_servers = mysql_query("SELECT Id, IP FROM servers", $db_conn);

  if (!$result_servers) {
	  error_log('Unable to query database server: ' . mysql_error());
	  die ('Unable to query database server');
  }

  while($server = mysql_fetch_object($result_servers)) {

    $server_metrics = mysql_query("SELECT RefIDMetric FROM servers_metrics WHERE RefIDServer=$server->Id", $db_conn);

	  while ($server_metric = mysql_fetch_object($server_metrics)) {
      
      $metric = mysql_query("SELECT Id, Parameters FROM metrics WHERE Id=$server_metric->RefIDMetric", $db_conn);
      $metric = mysql_fetch_object($metric);

      // Gets the parameters for this metric
      $params = explode(" ", $metric->Parameters);
      $plugin_name = explode("_", $params[0]);

      // Executes the plugin for this metric
      if($plugin_name[0] == "snmp")
      {
        if($params[1] == "%IP")     
          $ret = shell_exec("./$plugins_dir" . $params[0] . " " . $row->IP . " 2>&1");
      }

      // Gets the metric value from the string returned by the plugin
      // and updates the RRD 
      if(!is_null($ret) && is_string($ret) && !empty($ret) )
      {
        $value = explode("\"", $ret);
        if(sizeof($value) > 1)
          $value = $value[1];
        else
        {
          $value = explode(" ", $ret);
          $value = $value[sizeof($value) - 1];
        }

        if(sizeof(explode(".", $value)) > 1)
          $value = floatval($value);
        else
          $value = intval($value);

        // Adds the obtained value to the RRD of the pair (server, metric)
echo "!!!";

//echo $server->Id . ", " . $metric->Id . ", " . time() . ", " . $value . "<br>";
        updateRRD($server->Id, $metric->Id, time(), $value);
      }
	  }
  }

  mysql_close($db_conn);

?>
