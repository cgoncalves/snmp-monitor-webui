<?
require_once('config.php');
if (isset($_POST["submit"])) {

	$db_conn = mysql_connect($db_host, $db_user, $db_password);

	if (!$db_conn) {
		error_log('Unable to connect to server: ' . mysql_error());
		die ('Unable to connect to server: ' . mysql_error());
	}

	mysql_select_db($db_name, $db_conn);

	$parameters = $_POST['metric_file'] . ' ' . $_POST['metric_parameters'];

	$sql="INSERT INTO metrics (Name, Parameters, DataType, Unit) VALUES
		('$_POST[metric_name]', '$parameters', '$_POST[metric_datatype]', '$_POST[metric_unit]')";

	if (!mysql_query($sql,$db_conn)) {
		die('Error: ' . mysql_error());
	}

	mysql_close($db_conn);
	header("Location: index.php");
}

// List all available plugins
$array_scripts = array();

foreach (scandir($plugins_dir) as $filename)
{
	$file = $plugins_dir.'/'.$filename;
	if (is_file($file))
		array_push($array_scripts, $filename);
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
			<label class="description" for="element_2">Metric</label>
			<div>
				<select name="metric_file">
				<?php foreach ($array_scripts as $script) {
						echo "<option value=$script>$script</option>";
				} ?>
				</select>
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
				<select name="metric_datatype">
					<option value="ABSOLUTE">ABSOLUTE</option>
					<option value="COUNTER">COUNTER</option>
					<option value="DERIVE">DERIVE</option>
					<option value="GAUGE">GAUGE</option>
				</select>
			</div> 
		</li>
		<li id="li_4" >
			<label class="description" for="element_4">Unit</label>
			<div>
				<input id="metric_unit" name="metric_unit" class="element text medium" type="text" maxlength="18" value=""/> 
			</div>
		</li>

		<li class="buttons">
			<input type="hidden" name="form_id" value="287843" />
			<input id="saveForm" class="button_text" type="submit" name="submit" value="Submit" />
		</li>
	</ul>
</form>	
