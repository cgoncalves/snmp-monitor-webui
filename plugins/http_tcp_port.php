<?php

  $port = 80;
  $ret = shell_exec("nmap -p $port www.ua.pt 2>&1");

  if(!empty($ret))
  {
    $counter = 0;
    $value = 0;
    $results = explode("\n", $ret);
    
    if(sizeof($results) >= 7)
    {
      $state = explode(" ", $results[6]);
      if(sizeof($state) >=2)
      {
        $state = $state[1];

        if($state == "open")
          $state_value = 1;
        elseif($state == "closed")
          $state_value = 0;
        else
          $state_value = 2;
      }
    }
  }

  echo $state_value;

?>
