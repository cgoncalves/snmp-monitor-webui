#!/usr/bin/env php
<?php

  $oid = "1.3.6.1.2.1.25.3.3.1.2";
  $ret = shell_exec("snmpwalk -v 1 -c public $argv[1] $oid 2>&1");

  if(!empty($ret))
  {
    $counter = 0;
    $value = 0;
    $results = explode("\n", $ret);
    
    for($i = 0; $i < sizeof($results); $i++)
    {
      if(!empty($results[$i]))
      {
        $counter++;
        $values = explode(" ", $results[$i]);
        $value += intval($values[sizeof($values) - 1]);
      }
    }

    $value /= $counter;
  }

  echo "$oid $value";

?>
