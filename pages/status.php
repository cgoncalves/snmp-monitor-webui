<META HTTP-EQUIV=Refresh CONTENT="300">

<script>
  function selectOnclickServer(form)
  {
    location = "?p=status&sid="+form.server.value;
  }
</script>
<?php

  require_once('config.php');

  $db_conn = mysql_connect($db_host, $db_user, $db_password);

  if (!$db_conn) {
	  error_log('Unable to connect to server: ' . mysql_error());
	  die ('Unable to connect to server: ' . mysql_error());
  }

  mysql_select_db($db_name, $db_conn);

  $result_servers = mysql_query("SELECT Id, Name FROM servers ORDER BY Name");
  $result_metrics = mysql_query("SELECT Id, Name FROM metrics");

?>

<form id="form_287843" class="appnitro" method="post" action="">
	<div class="form_description">
		<h2>Servers/Metrics Status</h2>
	</div>
	<ul>
		<li id="li_1" >
			<label class="description" for="element_1">Server:</label>
			<div>
				<select name="server" onclick="selectOnclickServer(this.form);">
				<?php while ($row = mysql_fetch_object($result_servers)) {
					if (isset($_GET['sid']) && $_GET['sid'] == $row->Id)
						echo "<option value=$row->Id selected=\"yes\">$row->Name</option>";
					else
						echo "<option value=$row->Id>$row->Name</option>";
				} ?>
				</select>
			</div> 
		</li>
    <li class="buttons">
			<input type="hidden" name="form_id" value="287843" />
      <input id="showAll" class="button_text" type="submit" name="submitAll" value="Show All Servers"/>
		</li>
	</ul>
  <br/>

<?php
  if(isset($_POST["submitAll"]))
  {
      showAll($result_servers, $result_metrics);
  }
  else if ( isset($_REQUEST['sid']) )
  {
    $server_id = $_GET['sid'];
    $metric_id = -1;

    if (isset($_POST["server"]) || isset($_POST["metric"])) {

      if (isset($_POST["server"]))
        $server_id = $_POST["server"];
      if ( isset($_POST["metric"]))
        $metric_id = $_POST["metric"];
    }
    
    if($server_id == -1)
    {
      $result_servers = mysql_query("SELECT Id, Name FROM servers");
      $row = mysql_fetch_object($result_servers);
      $server_id = $row->Id;
    }
    if($metric_id == -1)
      $metric_id = 0;

    $result_metrics = mysql_query("SELECT metrics.Id, metrics.Name FROM metrics
                                   INNER JOIN servers_metrics AS SM ON SM.RefIDMetric=metrics.Id
                                   WHERE SM.RefIDServer=$server_id
                                   ORDER BY metrics.name"
                                 );

    showServer($server_id, $result_metrics);
  }
  
  mysql_close($db_conn);

  function showAll($result_servers, $result_metrics)
  {
    mysql_data_seek($result_servers, 0);

    while($server = mysql_fetch_object($result_servers))
    {
      $result_metrics = mysql_query("SELECT metrics.Id, metrics.Name FROM metrics
                                     INNER JOIN servers_metrics AS SM ON SM.RefIDMetric=metrics.Id
                                     WHERE SM.RefIDServer=$server->Id"
                                   );

      echo "<p><strong>" . $server->Name . "</strong></p>";
      metrics_status($server->Id, $result_metrics);
    }

    echo "</form>";
  }

  function showServer($server_id, $result_metrics)
  {
    $server = mysql_query("SELECT Name FROM servers WHERE Id=$server_id");
    $server = mysql_fetch_object($server);

    echo "<p><strong>" . $server->Name . "</strong></p>";

    metrics_status($server_id, $result_metrics);

    echo "</form>";
  }

  function metrics_status($server_id, $result_metrics)
  {
      if(mysql_num_rows($result_metrics) < 1)
        echo "<p>No metrics associated.</p>";
      else
      {
        echo "<table border='2' cellspacing='1' cellpadding='5'>";
        //mysql_data_seek($result_metrics, 0);

				$hostalive = true;
        while($metric = mysql_fetch_object($result_metrics))
        {
          echo "<tr>";
          $server_metric = mysql_query("SELECT Status, HostAlive FROM servers_metrics WHERE RefIdServer=$server_id AND RefIdMetric=$metric->Id");
          $server_metric = mysql_fetch_object($server_metric);

					if ($server_metric->HostAlive == '1' && $server_metric->Status == "ERROR")
					{
						$hostalive = false;
						$td_class = "clean-error";
					}
					elseif ($server_metric->Status == "OK")
						$td_class = "clean-ok";
					elseif ($server_metric->Status == "WARNING")
						$td_class = "clean-warning";
					elseif ($server_metric->Status == "ERROR" || $server_metric->Status == "CRITICAL")
						$td_class = "clean-error";
					else
						$td_class = "clean-neutral";

          echo "<td class=\"$td_class\"><a href=\"?p=graphs&sid=$server_id&mid=$metric->Id\">" . $metric->Name . "</a></td>";

          if(!is_null($server_metric->Status))
            echo"<td class=\"$td_class\">" . $server_metric->Status ."</td>";
          else
            echo "<td>N/A</td>;

          </tr>";
        }

        echo "</table>";

				if (!$hostalive)
					echo "<br/><li class=\"clean-error\">HOST IS DOWN!</li>";
      }
  }

?>
