<?
  require_once('config.php');

  $db_conn = mysql_connect($db_host, $db_user, $db_password);
  mysql_select_db($db_name, $db_conn);

  if (!$db_conn) {
          error_log('Unable to connect to server: ' . mysql_error());
          die ('Unable to connect to server: ' . mysql_error());
  }

  $checked = $_GET['chkYesNo'];
  $id = $_GET['eventlog_id'];
  mysql_query("UPDATE eventlogs SET Ack='$checked' WHERE Id='$id'", $db_conn);

  mysql_close($db_conn);
?>
