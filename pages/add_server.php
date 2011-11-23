<?
if (isset($_POST["submit"])) {
	require_once('config.php');

	$db_conn = mysql_connect($db_host, $db_user, $db_password);

	if (!$db_conn) {
		error_log('Unable to connect to server: ' . mysql_error());
		die ('Unable to connect to server: ' . mysql_error());
	}

	mysql_select_db($db_name, $db_conn);

	//$sql="INSERT INTO servers (Name, IP, Periodicity) VALUES
	//	('$_POST[server_name]', '$_POST[server_ip]', $_POST[server_periodicity])";
	$sql="INSERT INTO servers (Name, IP, Periodicity) VALUES
		('$_POST[server_name]', '$_POST[server_ip]', 300)";

	if (!mysql_query($sql,$db_conn)) {
		die('Error: ' . mysql_error());
	}

	mysql_close($db_conn);
	header("Location: index.php");
}
?>

<form id="form_287843" class="appnitro"  method="post" action="">
	<div class="form_description">
		<h2>Server</h2>
		<p>Add a new server.</p>
	</div>
	<ul>
		<li id="li_1" >
			<label class="description" for="element_1">Name </label>
			<div>
				<input id="server_name" name="server_name" class="element text medium" type="text" maxlength="255" value=""/> 
			</div> 
		</li>
		<li id="li_2" >
			<label class="description" for="element_2">IP </label>
			<div>
				<input id="server_ip" name="server_ip" class="element text medium" type="text" maxlength="255" value=""/> 
			</div> 
		</li>
<!--		<li id="li_3" >
			<label class="description" for="element_3">Periodicity (seconds) </label>
			<div>
				<input id="server_periodicity" name="server_periodicity" class="element text medium" type="text" maxlength="255" value=""/> 
			</div> 
		</li>
-->
		<li class="buttons">
			<input type="hidden" name="form_id" value="287843" />
			<input id="saveForm" class="button_text" type="submit" name="submit" value="Submit" />
		</li>
	</ul>
</form>	
