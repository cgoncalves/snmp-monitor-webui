#!/usr/bin/env php

<?php

  $oid = "1.3.6.1.2.1.25.2.3.1.5.$argv[2]";
  $ret = shell_exec("snmpget -v 1 -c public $argv[1] $oid 2>&1");

  if(!empty($ret))
  {
    $size = explode(" ", $ret);
    $size = $size[sizeof($size) - 1];
  }

  $oid = "1.3.6.1.2.1.25.2.3.1.6.$argv[2]";
  $ret = shell_exec("snmpget -v 1 -c public $argv[1] $oid 2>&1");

  if(!empty($ret))
  {
    $used = explode(" ", $ret);
    $used = $size[sizeof($used) - 1];
  }

  $oid = "1.3.6.1.2.1.25.2.3.1.4.$argv[2]";
  $ret = shell_exec("snmpget -v 1 -c public $argv[1] $oid 2>&1");

  if(!empty($ret))
  {
    $units = explode(" ", $ret);
    $units = $units[sizeof($units) - 1];
  }

  if(!is_null($size) && !is_null($used) && !is_null($units))
    $value = (intval($size) - intval($used)) * intval($units);

  echo "-1 $value";

?>
