<?php

  $oid = "1.3.6.1.2.1.2.2.1.10.$argv[2]";
  $ret = shell_exec("snmpget -v 1 -c public $argv[1] $oid 2>&1");

  if(!empty($ret))
  {
    $value = explode(" ", $ret);
    $value = $value[sizeof($value) - 1];
  }

  echo "$oid $value";

?>