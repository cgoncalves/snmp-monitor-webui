<?php

  function sendNotification($server_id, $server_ip, $server_name, $metric_name, $oid, $threshold_min1, $threshold_min2, $threshold_max1, $threshold_max2, $value)
  {
    $server_notifications = mysql_query("SELECT RefIdNotification, Receiver FROM servers_notifications WHERE RefIDServer=$server_id");

    while($server_notification = mysql_fetch_object($server_notifications))
    {
      $notification = mysql_query("SELECT Name FROM notifications WHERE ID=$server_notification->RefIdNotification");

      $notification = mysql_fetch_object($notification);

      if( (strcmp($notification->Name, "E-mail")) || (strcmp($notification->Name, "Email")) )
      {
        if($metric_name == "Trap")
        {

        }
        else
        {
          $message = "Metric \"$metric_name\"";
          if($oid != -1)
            $message .= "($oid)";
          $message .= " of server \"$server_name\" ($server_ip)";
          if($value > $threshold_max2)
            $message .= " has exceeded threshold max 2 of $threshold_max2";
          elseif($value > $threshold_max1)
            $message .= " has exceeded threshold max 1 of $threshold_max1";
          elseif($value < $threshold_min2)
            $message .= " is lower than threshold min 2 of $threshold_min2";
          elseif($value > $threshold_min2)
            $message .= " is lower than threshold min 1 of $threshold_min1";
          $message .=  " (value = $value)";
        }

        sendEmail($server_notification->Receiver, "Monitoring Event", "aprocha@ua.pt", $message);
      }
    }   
  }

  function sendEmail($to, $subject, $from, $message)
  {
    if(valid_email($to))
    {
        $from = "girs";
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
