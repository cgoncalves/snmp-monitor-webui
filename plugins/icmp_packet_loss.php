<?php

  $count = 5;
  $ret = shell_exec("ping -c $count www.ua.pt 2>&1");

  if(!empty($ret))
  {
    $counter = 0;
    $value = 0;
    $results = explode("\n", $ret);

    if(sizeof($results) >= 9)
    {
      $loss = explode(" ", $results[8]);
      if(sizeof($loss) >=6)
      {
        $loss = explode("%", $loss[5]);
          if(sizeof($loss) >= 1)
            $loss = intval($loss[0]);
      }
    }
  }

  echo $loss;

?>