<?php

require_once('config.php');

$db_conn = mysql_connect($db_host, $db_user, $db_password);
mysql_select_db($db_name, $db_conn);

if (!$db_conn) {
	error_log('Unable to connect to server: ' . mysql_error());
	die ('Unable to connect to server: ' . mysql_error());
}

$result_servers = mysql_query("SELECT Id, Name, IP FROM servers", $db_conn);
$result_services = mysql_query("SELECT Id, Name FROM services", $db_conn);

if (!$result_servers && !$result_services && !$result_servers_services) {
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

	while ($row_services = mysql_fetch_object($result_services)) {
		echo "<td><strong>$row_services->Name</strong></td>";
	}

echo "
	</tr>
";

mysql_data_seek($result_services, 0);

while ($row = mysql_fetch_object($result_servers)) {
	echo "
		<tr>
			<td>$row->Id</td>
			<td>$row->Name</td>
			<td>$row->IP</td>";

	while ($row_services = mysql_fetch_object($result_services)) {
		$result_server_service = mysql_query("SELECT Status, RefIDService FROM servers_services WHERE RefIDServer=$row->Id");
		while ($row_server_service = mysql_fetch_object($result_server_service)) {
			if ($row_server_service->RefIDService == $row_services->Id) {
				echo "<td>$row_server_service->Status</td>";
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
