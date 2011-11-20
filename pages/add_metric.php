<?
if (isset($_POST["submit"])) {
	require_once('config.php');

	$db_conn = mysql_connect($db_host, $db_user, $db_password);

	if (!$db_conn) {
		error_log('Unable to connect to server: ' . mysql_error());
		die ('Unable to connect to server: ' . mysql_error());
	}

	mysql_select_db($db_name, $db_conn);

	$sql="INSERT INTO metrics (Name, Parameters, DataType, Unit) VALUES
		('$_POST[metric_name]', '$_POST[metric_parameters]', '$_POST[metric_datatype]', '$_POST[metric_unit]')";

	if (!mysql_query($sql,$db_conn)) {
		die('Error: ' . mysql_error());
	}

	mysql_close($db_conn);
	header("Location: index.php");
}
?>

<form id="form_287843" class="appnitro" method="post" action="">
	<div class="form_description">
		<h2>Metric</h2>
		<p>Add a new metric.</p>
	</div>
	<ul>
		<li id="li_1" >
			<label class="description" for="element_1">Name</label>
			<div>
				<input id="metric_name" name="metric_name" class="element text medium" type="text" maxlength="255" value=""/> 
			</div> 
		</li>
		<li id="li_2" >
			<label class="description" for="element_2">Parameters</label>
			<div>
				<input id="metric_parameters" name="metric_parameters" class="element text medium" type="text" maxlength="255" value=""/> 
			</div> 
		</li>
		<li id="li_3" >
			<label class="description" for="element_3">RRD Data Type</label>
			<div>
				<input id="metric_datatype" name="metric_datatype" class="element text medium" type="text" maxlength="50" value=""/> 
			</div> 
		</li>
		<li id="li_4" >
			<label class="description" for="element_4">Unit</label>
			<div>
				<input id="metric_unit" name="metric_unit" class="element text medium" type="text" maxlength="40" value=""/> 
			</div>
		</li>

		<li class="buttons">
			<input type="hidden" name="form_id" value="287843" />
			<input id="saveForm" class="button_text" type="submit" name="submit" value="Submit" />
		</li>
	</ul>
</form>	
