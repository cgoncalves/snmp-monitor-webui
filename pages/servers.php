<?php

require_once('config.php');

$db_conn = mysql_connect($db_host, $db_user, $db_password);
mysql_select_db($db_name, $db_conn);

if (!$db_conn) {
	error_log('Unable to connect to server: ' . mysql_error());
	die ('Unable to connect to server: ' . mysql_error());
}

$result_servers = mysql_query("SELECT Id, Name, IP FROM servers", $db_conn);
$result_metrics = mysql_query("SELECT Id, Name FROM metrics", $db_conn);

if (!$result_servers && !$result_metrics && !$result_servers_metrics) {
	error_log('Unable to query database server: ' . mysql_error());
	die ('Unable to query database server');
}


echo "
<h2>SERVER STATUS</h1>
<table border='2' cellspacing='1'>
	<tr>
		<td><strong>ID</strong></td>
		<td><strong>Name</strong></td>
		<td><strong>IP</strong></td>";

	while ($row_metrics = mysql_fetch_object($result_metrics)) {
		echo "<td><strong>$row_metrics->Name</strong></td>";
	}

echo "
	</tr>
";

mysql_data_seek($result_metrics, 0);

while ($row = mysql_fetch_object($result_servers)) {
	echo "
		<tr>
			<td>$row->Id</td>
			<td>$row->Name</td>
			<td>$row->IP</td>";

	while ($row_metrics = mysql_fetch_object($result_metrics)) {
		$result_server_metric = mysql_query("SELECT Status, RefIDMetric FROM servers_metrics WHERE RefIDServer=$row->Id");
		while ($row_server_metric = mysql_fetch_object($result_server_metric)) {
			if ($row_server_metric->RefIDMetric == $row_metrics->Id) {
				echo "<td>$row_server_metric->Status</td>";
			}
			else {
				echo "<td>N/A</td>";
			}
		}
	}

	echo "
		</tr>
	";
}
echo "</table>";


mysql_close($db_conn);

?>
