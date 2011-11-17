#!/usr/bin/env php

<?php

  $oid = "1.3.6.1.4.1.2021.4.3.0";
  $ret = shell_exec("snmpget -O en -v 1 -c public $argv[1] $oid 2>&1");

  if(!empty($ret))
  {
    $total = explode(" ", $ret);
    $total = $total[sizeof($total) - 1];
  }

  $oid = "1.3.6.1.4.1.2021.4.4.0";
  $ret = shell_exec("snmpget -O en -v 1 -c public $argv[1] $oid 2>&1");

  if(!empty($ret))
  {
    $avail = explode(" ", $ret);
    $avail = $avail[sizeof($avail) - 1];
  }

  if(!is_null($total) && !is_null($avail))
    $value = intval($total) - intval($avail);

  echo "-1 $value";

?>
