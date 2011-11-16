<?php

  $port = 80;
  $ret = shell_exec("nmap -p $port www.ua.pt 2>&1");

  if(!empty($ret))
  {
    $counter = 0;
    $value = 0;
    $results = explode("\n", $ret);
    
    if(sizeof($results) >= 4)
    {
      $latency = explode("(", $results[3]);
      if(sizeof($latency) >=2)
      {
        $latency = explode(" ", $latency[1]);
        if(sizeof($latency) >=1)
        {
          $latency = floatval($latency[0]);
          $latency = intval($latency * 1000);
        }
      }
    }
  }

  echo $latency;

?>
