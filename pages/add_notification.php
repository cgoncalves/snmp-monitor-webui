<?
if (isset($_POST["submit"])) {
	require_once('config.php');

	$db_conn = mysql_connect($db_host, $db_user, $db_password);

	if (!$db_conn) {
		error_log('Unable to connect to server: ' . mysql_error());
		die ('Unable to connect to server: ' . mysql_error());
	}

	mysql_select_db($db_name, $db_conn);

	$sql="INSERT INTO notifications (Name) VALUES	('$_POST[notification_name]')";

	if (!mysql_query($sql,$db_conn)) {
		die('Error: ' . mysql_error());
	}

	mysql_close($db_conn);
	header("Location: index.php");
}
?>

<form id="form_287843" class="appnitro"  method="post" action="">
	<div class="form_description">
		<h2>Notification</h2>
		<p>Add a new type of notification.</p>
	</div>
	<ul>
		<li id="li_1" >
			<label class="description" for="element_1">Name</label>
			<div>
				<input id="notification_name" name="notification_name" class="element text medium" type="text" maxlength="255" value=""/> 
			</div> 
		</li>

		<li class="buttons">
			<input type="hidden" name="form_id" value="287843" />
			<input id="saveForm" class="button_text" type="submit" name="submit" value="Submit" />
		</li>
	</ul>
</form>	
