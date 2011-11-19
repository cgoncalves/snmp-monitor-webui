<META HTTP-EQUIV=Refresh CONTENT="300">

<script>
  function selectOnclickServer(form)
  {
    location = "?p=graphs&sid="+form.server.value;
  }
  
  function selectOnclickMetric(form)
  {
    location = "?p=graphs&sid="+getQuerystring('sid')+"&mid="+form.metric.value;
  }
</script>

<?php

  require_once('config.php');
  include("rrd.php");

  $db_conn = mysql_connect($db_host, $db_user, $db_password);

  if (!$db_conn) {
	  error_log('Unable to connect to server: ' . mysql_error());
	  die ('Unable to connect to server: ' . mysql_error());
  }

  mysql_select_db($db_name, $db_conn);

  $result_servers = mysql_query("SELECT Id, Name FROM servers");

?>

<form id="form_287843" class="appnitro" method="post" action="">
	<div class="form_description">
		<h2>Servers/Metrics Graphs</h2>
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
<?php

  if (isset($_GET['sid']))
  {
    $server_id = $_GET['sid'];
?>

    <li id="li_2" >
			<label class="description" for="element_2">Metric:</label>
			<div>
				<select name="metric" onclick="selectOnclickMetric(this.form);">
				<?php
                                  $result_metrics = mysql_query("SELECT metrics.Id, metrics.Name FROM metrics
                                                                 INNER JOIN servers_metrics AS SM ON SM.RefIDMetric=metrics.Id
                                                                 WHERE SM.RefIDServer=$server_id"
                                                               );
                                  while ($row = mysql_fetch_object($result_metrics)) {
					if (isset($_GET['mid']) && $_GET['mid'] == $row->Id)
						echo "<option value=$row->Id selected=\"yes\">$row->Name</option>";
					else
						echo "<option value=$row->Id>$row->Name</option>";
				  }
                                ?>
				</select>
			</div> 
		</li>
    <li class="buttons">
			<input type="hidden" name="form_id" value="287843" />
		</li>
	</ul>
</form>	

<?php
  }

  if ( !isset($_GET['sid']) || !isset($_GET['mid']) || empty($_GET['mid']) )
    return;

  $server_id = $_GET['sid'];
  $metric_id = $_GET['mid'];

  $metric = mysql_query("SELECT Name, Unit FROM metrics WHERE Id=$metric_id");
  $metric = mysql_fetch_object($metric);

  $server_metric = mysql_query("SELECT Threshold_max1, Threshold_max2, Threshold_min1, Threshold_min2 FROM servers_metrics WHERE RefIDServer=$server_id AND RefIDMetric=$metric_id");
  $server_metric = mysql_fetch_object($server_metric);

  mysql_close($db_conn);

  graphRRD($server_id, $metric_id, $metric->Name, "-1h", $metric->Unit, $server_metric->Threshold_max1, $server_metric->Threshold_max2, $server_metric->Threshold_min1, $server_metric->Threshold_min2);
  graphRRD($server_id, $metric_id, $metric->Name, "-1d", $metric->Unit, $server_metric->Threshold_max1, $server_metric->Threshold_max2, $server_metric->Threshold_min1, $server_metric->Threshold_min2);
  graphRRD($server_id, $metric_id, $metric->Name, "-1w", $metric->Unit, $server_metric->Threshold_max1, $server_metric->Threshold_max2, $server_metric->Threshold_min1, $server_metric->Threshold_min2);
  graphRRD($server_id, $metric_id, $metric->Name, "-1m", $metric->Unit, $server_metric->Threshold_max1, $server_metric->Threshold_max2, $server_metric->Threshold_min1, $server_metric->Threshold_min2);
?>

<div style="text-align:center;">
  <p>Last Hour</p>
  <img src="<?php echo $graphs_dir . $server_id . "_" . $metric_id . "_-1h.png"; ?>" />
  <p><br/>Last Day</p>
  <img src="<?php echo $graphs_dir . $server_id . "_" . $metric_id . "_-1d.png"; ?>" />
  <p><br/>Last Week</p>
  <img src="<?php echo $graphs_dir . $server_id . "_" . $metric_id . "_-1w.png"; ?>" />
  <p><br/>Last Month</p>
  <img src="<?php echo $graphs_dir . $server_id . "_" . $metric_id . "_-1m.png"; ?>" />
  <p><br/></p>
<div>
