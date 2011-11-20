<?php

  function sendNotification($server_id, $server_ip, $server_name, $metric_name, $oid, $threshold_min1, $threshold_min2, $threshold_max1, $threshold_max2, $value)
  {
    $server_notifications = mysql_query("SELECT RefIdNotification, Receiver FROM servers_notifications WHERE RefIDServer=$server_id");

    while($server_notification = mysql_fetch_object($server_notifications))
    {
      $notification = mysql_query("SELECT Name FROM notifications WHERE ID=$server_notification->RefIdNotification");

      $notification = mysql_fetch_object($notification);

      if((strcmp($notification->Name, "E-mail")) || (strcmp($notification->Name, "Email")))
      {
        if($metric_name == "Trap")
        {

        }
        else
        {
          $message = "Server \"$server_name\" ($server_ip):\n\n";
          $message .= "Value of metric \"$metric_name\"";
          if($oid != -1)
            $message .= " (OID = $oid)";
          if(!is_null($threshold_max2) && ($value > $threshold_max2))
            $message .= " has exceeded Threshold Max 2.";
          elseif(!is_null($threshold_max1) && ($value > $threshold_max1))
            $message .= " has exceeded Threshold Max 1.";
          elseif(!is_null($threshold_min2) && ($value < $threshold_min2))
            $message .= " is lower than Threshold Min 2.";
          elseif(!is_null($threshold_min1) && ($value > $threshold_min1))
            $message .= " is lower than Threshold Min 1.";

          $message .=  "\n\nMetric value = $value";
          $message .=  "\nThreshold value = ";

          if(!is_null($threshold_max2) && ($value > $threshold_max2))
            $message .= "$threshold_max2";
          elseif(!is_null($threshold_max1) && ($value > $threshold_max1))
            $message .= "$threshold_max1";
          elseif(!is_null($threshold_min2) && ($value < $threshold_min2))
            $message .= "$threshold_min2";
          elseif(!is_null($threshold_min1) && ($value > $threshold_min1))
            $message .= "$threshold_min1";
        }

        sendEmail($server_notification->Receiver, "Monitoring Event", "girs", $message);
      }
    }   
  }

  function sendEmail($to, $subject, $from, $message)
  {
    if(valid_email($to))
    {
        $headers = "From: " . $from;

        if(!mail($to, $subject, wordwrap($message, 70), $headers))
          echo "E-mail not sent!";
        else
          echo "E-mail sent!";
    }
    else
      echo "E-mail address not valid!";
  }

  function valid_email($email)
	{
		$regexp = "^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$";
		$valid = FALSE;
		if (eregi($regexp, $email))
		{
			list($username,$domaintld) = explode("@",$email);
			if (getmxrr($domaintld,$mxrecords))
			  $valid = TRUE;
		}
		else
		{
			$valid = FALSE;
		}
		return $valid;
	}


?>
