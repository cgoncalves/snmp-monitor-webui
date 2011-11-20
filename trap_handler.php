#!/usr/bin/env php
<?php

require_once('config.php');

$ip = readline('');
$value = '';

// Concatenate all OID-value pairs
do {
	$line = readline('');

	if (empty($line))
		break;

	if (empty($value))
		$value = $line;
	else
		$value = $value . ', ' . $line;
} while (!empty($line));

$db_conn = mysql_connect($db_host, $db_user, $db_password);

if (!$db_conn) {
	error_log('Unable to connect to server: ' . mysql_error());
	die ('Unable to connect to server: ' . mysql_error());
}

mysql_select_db($db_name, $db_conn);

// Get server ID
$result_server = mysql_query("SELECT Id FROM servers WHERE IP='$ip'");

// If we received a trap from an unknown server, drop it
if (mysql_num_rows($result_server) < 1)
	return;

$server_id = mysql_fetch_object($result_server)->Id;

// Log trap
$sql = "INSERT INTO eventlogs (RefIDServer, IDMetric, OID, Value, Ack) VALUES ($server_id, -1, -1, '$value', 0)";
$result = mysql_query($sql);

if (!$result) {
	echo 'Invalid query: ' . mysql_error();
}

// Close DB connection
mysql_close($db_conn);
?>
