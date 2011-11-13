<?
require_once('config.php');

$db_conn = mysql_connect($db_host, $db_user, $db_password);

if (!$db_conn) {
	error_log('Unable to connect to server: ' . mysql_error());
	die ('Unable to connect to server: ' . mysql_error());
}

mysql_select_db($db_name, $db_conn);

if (isset($_POST["submit"])) {
	$sql = "INSERT INTO servers_notifications (RefIDServer, RefIDNotification, Receiver)
		VALUES ('$_POST[refidserver]', '$_POST[refidnotification]', '$_POST[receiver]')";

	if (!mysql_query($sql,$db_conn)) {
		die('Error: ' . mysql_error());
	}

	mysql_close($db_conn);
	
	header("Location: index.php");
}

$sql_servers = "SELECT Id, Name FROM servers";
$sql_notifications = "SELECT Id, Name FROM notifications";

$result_servers = mysql_query($sql_servers);
$result_notifications = mysql_query($sql_notifications);

?>

<form id="form_287843" class="appnitro"  method="post" action="">
	<div class="form_description">
		<h2>Server-Notification</h2>
		<p>Associate a notification to a server</p>
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
			<label class="description" for="element_2">Notification Type</label>
			<div>
				<select name="refidnotification">
				<?php while ($row = mysql_fetch_object($result_notifications)) {
					echo "<option value=$row->Id>$row->Name</option>";
				} ?>
				</select>
			</div> 
		</li>
		<li id="li_6" >
			<label class="description" for="element_4">Receiver</label>
			<div>
				<input id="receiver" name="receiver" class="element text medium" type="text" maxlength="255" value=""/> 
			</div> 
		</li>
		<li class="buttons">
			<input type="hidden" name="form_id" value="287843" />
			<input id="saveForm" class="button_text" type="submit" name="submit" value="Submit" />
		</li>
	</ul>
</form>	
