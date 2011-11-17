#!/usr/bin/env php

<?php

  $count = 5;
  $ret = shell_exec("ping -c $count www.ua.pt 2>&1");

  if(!empty($ret))
  {
    $counter = 0;
    $value = 0;
    $results = explode("\n", $ret);

    if(sizeof($results) >= 10)
    {
      $avg = explode("/", $results[9]);
      if(sizeof($avg) >=5)
      {
        $avg = intval($avg[4]);
      }
    }
  }

  echo $avg;

?>
