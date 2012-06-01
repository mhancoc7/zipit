<?php 
###############################################################
# Zipit Backup Utility
###############################################################
# Developed by Jereme Hancock for Cloud Sites
# Visit http://zipitbackup.com for updates
###############################################################

// include password protection
    include("zipit-login.php"); 

// require zipit configuration
    require('zipit-config.php');

// define zipit log file
    $zipitlog = "../../../logs/zipit.log";
    $logsize = filesize($zipitlog);

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Zipit Backup Utility -- Logs</title>

<link href="./css/style_files.css" rel="stylesheet" type="text/css" />


</head>
<body>
    <center><ul class="tabs group">
      <li class="active"><a href="#" onfocus="this.blur();">Files</a></li> 
      <li><a href="zipit-db.php" onfocus="this.blur();">Databases</a></li> 
	</ul></center>
<div class="wrapper">
<center><div class="head">Zipit Backup Utility</div>
<h2>Logs</h2></center>

<div class="logs">
<?  
   // Include & Call Class 
   include_once("lib/class.displaylogs.php"); 
   $lfDispl = new displayLogfile; 

   // Path/Name of Logfile 
   // Choose a short one for example b (!)  
   $filename = "$zipitlog"; 


?> 
<pre style="font-size:12px;"> 
<? 
   $lfDispl->setRowsToRead(100);    // Read 100 rows 
   $lfDispl->setAlign("bottom");       // Last row on top 
   $lfDispl->setFilepath($filename); // from this logfile 
   $lfDispl->setLineBreak(150);  // Break the row after 150 chars 
   $lfDispl->returnFormated();   // Output  
?> 
</pre> 
</div>
<br>
<center><font size="1em">Developed by <a href="http://www.cloudsitesrock.com" target="_blank">CloudSitesRock.com</a> for Rackspace Cloud Sites</font></center>
</div>
</body>
</html>
