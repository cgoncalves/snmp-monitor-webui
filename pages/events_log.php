<META HTTP-EQUIV=Refresh CONTENT="300">

<script type="text/javascript" src="resources/update_acks.js"></script>
<?php

  require_once('config.php');

  $db_conn = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $db_conn);

  if (!$db_conn) {
	  error_log('Unable to connect to server: ' . mysql_error());
	  die ('Unable to connect to server: ' . mysql_error());
  }

  $result_events = mysql_query("SELECT Id, RefIDServer, IDMetric, Date, OID, Threshold_min1, Threshold_min2, Threshold_max1, Threshold_max1, Threshold_max2, Value, Ack FROM eventlogs", $db_conn);

  if (!$result_events) {
	  error_log('Unable to query database server: ' . mysql_error());
	  die ('Unable to query database server');
  }

  echo "<form id=\"form_287843\" class=\"appnitro\" method=\"post\" action=\"\">
	        <div class=\"form_description\">
		        <h2>Events log</h2>
	        </div>";

  if(mysql_num_rows($result_events) < 1)
  {
    echo "No events ocurred.";
  }
  else
  {
    echo "
    <table border='2' cellspacing='1 cellpadding='5'>
      <tr>
        <td><strong>Date</strong></td>
		    <td><strong>Server Name (IP)</strong></td>
		    <td><strong>Metric (OID)</strong></td>
		    <td><strong>Value</strong></td>
		    <td><strong>Threshold</strong></td>
		    <td><strong>Acknowledged</strong></td>
      <tr>";

    while ($row = mysql_fetch_object($result_events)) {

      $server = mysql_query("SELECT Name, IP FROM servers WHERE Id=$row->RefIDServer", $db_conn);
      $server = mysql_fetch_object($server);
      $server_name = $server->Name;
      $server_ip = $server->IP;

      if($row->IDMetric > -1)
      {
        $metric = mysql_query("SELECT Name FROM metrics WHERE Id=$row->IDMetric", $db_conn);
        $metric = mysql_fetch_object($metric);
        $metric_name = $metric->Name;
      }

      if(!empty($metric_name))
      {
        $date = $row->Date;
        $value = $row->Value;
        $max2 = $row->Threshold_max2;
        $max1 = $row->Threshold_max1;
        $min2 = $row->Threshold_min2;
        $min1 = $row->Threshold_min1;    

	      echo "
		      <tr>
            <td>$date</td>
			      <td>$server_name<br>($server_ip)</td>
            <td>$metric_name";
            if($row->OID != -1)
              echo "<br>($row->OID)</td>";
            echo "<td>$value</td>";

        if(!is_null($max2) && ($value > $max2))
          echo "<td>Max 2 = $max2</td>";
        elseif(!is_null($max1) && ($value > $max1))
          echo "<td>Max 1 = $max1</td>";
        elseif(!is_null($min2) && ($value < $min2))
          echo "<td>Min 2 = $min2</td>";
        elseif(!is_null($min1) && ($value < $min1))
          echo "<td>Min 1 = $min1</td>";
        else
          echo "<td>N/A</td>";
        
        if ($row->Ack)
	  echo "<td><input name=\"chk\" type=\"checkbox\" id=\"chk_$row->Id\" value=\"$row->Id\" checked=\"yes\" onclick=\"chkit($row->Id, this.checked);\" /></td>";
        else
	  echo "<td><input name=\"chk\" type=\"checkbox\" id=\"chk_$row->Id\" value=\"$row->Id\" onclick=\"chkit($row->Id, this.checked);\" /></td>";

         echo "</tr>";
      }
    }
    echo "</table></form>";
  }

  mysql_close($db_conn);

?>
