<?php

  require_once('config.php');

  if(isset($_POST['submit']))
     update();

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

  echo "
  <h2>Events log</h1>
  <table border='2' cellspacing='1'>
    <tr>
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
      $value = $row->Value;
      $max2 = $row->Threshold_max2;
      $max1 = $row->Threshold_max1;
      $min2 = $row->Threshold_min2;
      $min1 = $row->Threshold_min1;    

	    echo "
		    <tr>
			    <td>$server_name<br>($server_ip)</td>
          <td>$metric_name<br>($row->OID)</td>
          <td>$value</td>";

      if($value > $max2)
        echo "<td>Max 2 = $max2</td>";
      elseif($value > $max1)
        echo "<td>Max 1 = $max1</td>";
      elseif($value < $min2)
        echo "<td>Min 2 = $min2</td>";
      elseif($value < $min1)
        echo "<td>Min 1 = $min1</td>";
      
      if($row->Ack == 0)
        echo "<td><input type=\"checkbox\" name=\"ack$row->Id\" /></td>";
      elseif($row->Ack == 1)
        echo "<td><input type=\"checkbox\" name=\"ack$row->Id\" checked=\"checked\" /></td>";

		  echo "</tr>";
    }
  }
  echo "</table>";

  mysql_close($db_conn);

  function update()
  {

  }

?>

<form action="" method="post">
  <input type="submit" name="submit" value="Update">
</form>