<?php

if (!isset($_REQUEST["p"])) {
	$p = 'pages/home.php';
}
else {
	$p = $_REQUEST['p'];

	if (empty($p) || $p == "home")
		$p = 'pages/home.php';
	elseif($p == "add_server")
		$p = 'pages/add_server.php';
	elseif($p == "add_metric")
		$p = 'pages/add_metric.php';
	elseif($p == "add_server_metric")
		$p = 'pages/add_server_metric.php';
  elseif($p == "add_server_notification")
		$p = 'pages/add_server_notification.php';
  elseif($p == "events_log")
		$p = 'pages/events_log.php';
  elseif($p == "graphs")
		$p = 'pages/graphs.php';
  elseif($p == "status")
		$p = 'pages/status.php';
  elseif($p == "servers_monitoring")
		$p = 'servers_monitoring.php';
	else
		$p = 'pages/404.php';
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>MGIRS</title>
<link rel="stylesheet" type="text/css" href="resources/view.css" media="all">
<script type="text/javascript" src="resources/view.js"></script>

</head>
<body id="main_body" >
  <center>
    <ul id="list-nav">
      <li><a href="?p=home">Home</a></li>
      <li><a href="?p=status">Status</a></li>
      <li><a href="?p=graphs">Graphs</a></li>
      <li><a href="?p=events_log">Events log</a></li>
    </ul>
  </center>
  <img id="top" src="images/top.png" alt="" />
  <div id="form_container">
    <h1>Untitled Form</h1>
    <?php require_once($p) ?>
  </div>
  <img id="bottom" src="images/bottom.png" alt="" />
</body>
</html>
