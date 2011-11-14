<?
require_once('config.php');
include("rrd.php");

$db_conn = mysql_connect($db_host, $db_user, $db_password);

if (!$db_conn) {
	error_log('Unable to connect to server: ' . mysql_error());
	die ('Unable to connect to server: ' . mysql_error());
}

mysql_select_db($db_name, $db_conn);

if (isset($_POST["submit"])) {
	$sql = "INSERT INTO servers_metrics (RefIDServer, RefIDMetric, Threshold_max1, Threshold_max2, Threshold_min1, Threshold_min2, Status)
		VALUES ('$_POST[refidserver]', '$_POST[refidmetric]', '$_POST[threshold_max1]', '$_POST[threshold_max2]', '$_POST[threshold_min1]', '$_POST[threshold_min2]', 'UNKNOWN')";

	if (!mysql_query($sql,$db_conn)) {
		die('Error: ' . mysql_error());
	}
  
  $metric = mysql_query("SELECT DataType FROM metrics WHERE Id=$_POST[refidmetric]");
  $metric = mysql_fetch_object($metric);
  $server = mysql_query("SELECT Periodicity FROM servers WHERE Id=$_POST[refidserver]");
  $server = mysql_fetch_object($server);

  createRRD($_POST[refidserver], $_POST[refidmetric], $metric->DataType, $server->Periodicity);

	mysql_close($db_conn);
	
	header("Location: index.php");
}

$sql_servers = "SELECT Id, Name FROM servers";
$sql_metrics = "SELECT Id, Name FROM metrics";

$result_servers = mysql_query($sql_servers);
$result_metrics = mysql_query($sql_metrics);

?>

<form id="form_287843" class="appnitro"  method="post" action="">
	<div class="form_description">
		<h2>Server-Metric</h2>
		<p>Associate a metric to a server</p>
	</div>
	<ul>
		<li id="li_1" >
			<label class="description" for="element_1">Server</label>
			<div>
				<select name ="refidserver">
				<?php while ($row = mysql_fetch_object($result_servers)) {
					echo "<option value=$row->Id>$row->Name</option>";
				} ?>
				</select>
			</div> 
		</li>
		<li id="li_2" >
			<label class="description" for="element_2">Metric</label>
			<div>
				<select name="refidmetric">
				<?php while ($row = mysql_fetch_object($result_metrics)) {
					echo "<option value=$row->Id>$row->Name</option>";
				} ?>
				</select>
			</div> 
		</li>
		<li id="li_3" >
			<label class="description" for="element_3">Threshold Max 1</label>
			<div>
				<input id="threshold_max1" name="threshold_max1" class="element text medium" type="text" maxlength="255" value=""/> 
			</div> 
		</li>
		<li id="li_4" >
			<label class="description" for="element_3">Threshold Max 2</label>
			<div>
				<input id="threshold_max2" name="threshold_max2" class="element text medium" type="text" maxlength="255" value=""/> 
			</div> 
		</li>
		<li id="li_5" >
			<label class="description" for="element_4">Threshold Min 1</label>
			<div>
				<input id="threshold_min1" name="threshold_min1" class="element text medium" type="text" maxlength="255" value=""/> 
			</div> 
		</li>
		<li id="li_6" >
			<label class="description" for="element_4">Threshold Min 2</label>
			<div>
				<input id="threshold_min2" name="threshold_min2" class="element text medium" type="text" maxlength="255" value=""/> 
			</div> 
		</li>
		<li class="buttons">
			<input type="hidden" name="form_id" value="287843" />
			<input id="saveForm" class="button_text" type="submit" name="submit" value="Submit" />
		</li>
	</ul>
</form>	
