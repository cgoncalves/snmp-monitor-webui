<?php

  $oid = "1.3.6.1.4.1.2021.4.15.0";
  $ret = shell_exec("snmpget -v 1 -c public $argv[1] $oid 2>&1");

  if(!empty($ret))
  {
    $value = explode(" ", $ret);
    $value = $value[sizeof($value) - 1];
  }

  echo "$oid $value";

?>
