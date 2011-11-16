function chkit(eventlog_id, chk) {
   chk = (chk==true ? "1" : "0");
   var url = "change_ack.php?eventlog_id="+eventlog_id+"&chkYesNo="+chk;
   if(window.XMLHttpRequest) {
      req = new XMLHttpRequest();
   } else if(window.ActiveXObject) {
      req = new ActiveXObject("Microsoft.XMLHTTP");
   }
   // Use get instead of post.
   req.open("GET", url, true);
   req.send(null);
}
