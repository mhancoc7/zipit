<?php 
###############################################################
# Zipit Backup Utility
###############################################################
# Developed by Jereme Hancock for Cloud Sites
# Visit http://zipitbackup.com for updates
###############################################################

// specify namespace
   namespace OpenCloud;

// include password protection
    include("zipit-login.php"); 

// require zipit configuration
    require('zipit-config.php');

// define zipit log file
    $zipitlog = "../../../logs/zipit.log";
    $logsize = filesize($zipitlog);

// create zipit log file if it doesn't exist
if(!file_exists("$zipitlog")) 
{ 
   $fp = fopen("$zipitlog","w");  
   fwrite($fp,"----Zipit Logs----\n\n");  
   fclose($fp); 
}

if ($logsize > 52428800) {
shell_exec("mv ../../../logs/zipit.log ../../../logs/zipit_old.log");
}

// require Cloud Files API
   require_once('./api/lib/rackspace.php');

// clean up local backups if files are older than 24 hours (86400 seconds)
    $dir = "./zipit-backups/files";

foreach (glob($dir."*") as $file) {
if (filemtime($file) < time() - 86400) {
    shell_exec("rm -rf ./zipit-backups/files/*");
    }
}

// create local backups folders if they are not there
if (!is_dir('./zipit-backups')) {
mkdir('./zipit-backups');
}
if (!is_dir('./zipit-backups/files')) {
mkdir('./zipit-backups/files');
} 

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Zipit Backup Utility -- File Backup</title>

<link href="./css/style_files.css" rel="stylesheet" type="text/css" />

</head>
<body>
	<center><ul class="tabs group">
	  <li class="active"><a href="#" onfocus="this.blur();">Files</a></li> 
	  <li><a href="zipit-db.php" onfocus="this.blur();">Databases</a></li> 
          <li><a href="zipit-logs.php" onfocus="this.blur();">Logs</a></li> 
          <li><a href="zipit-auto.php" onfocus="this.blur();">Auto Backups</a></li> 
          <li><a href="zipit-settings.php" onfocus="this.blur();">Settings</a></li> 
	</ul></center>
<div class="wrapper">
<center>
<?php


// include update checker
    include("zipit-update-footer.php");

?>
<h2>Available File Backups</h2></center>
<?php

echo "<center><em>";
echo "<br />";

// authenticate to Cloud Files
try {
// my credentials
define('AUTHURL', 'https://identity.api.rackspacecloud.com/v2.0/');
$mysecret = array(
    'username' => $username,
    'apiKey' => $key
);

// establish our credentials
$connection = new Rackspace(AUTHURL, $mysecret);
// now, connect to the ObjectStore service
$ostore = $connection->ObjectStore('cloudFiles', "$datacenter");

}

catch (HttpUnauthorizedError $e) {
   echo '<script type="text/javascript">';
   echo 'alert("Cloud Files API connection could not be established.\n\nBe sure to check your API credentials.")';
   echo '</script>'; 

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp Zipit started\n$logtimestamp -- Cloud Files API connection could not be established.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   echo "<script>location.href='zipit-settings.php'</script>";
   die();
}

// create container if it doesn't already exist
$cont = $ostore->Container();
$cont->Create(array('name'=>"zipit-backups-files-$url"));

$list = $cont->ObjectList();

while($o = $list->Next())
	echo $o->name ."<br/>";
        echo"<br/>";
  
echo "You can manage your backups via the <a href='https://mycloud.rackspace.com/a/$username/files' target='_blank'>Cloud Files control panel</a>";	
echo "<br/>";
echo "</center></em>";


?>
<br/><br/>
<center><input class="backup" readonly style="border: 1px solid #818185; background-color:#ccc; -moz-border-radius: 15px; border-radius: 15px; text-align:center; width:100px; color:#000; padding:3px;" type="submit" value="Backup" onclick="location = 'zipit-zip-files.php';"/>
<input class="logout" readonly  style="border: 1px solid #818185; background-color:#ccc; -moz-border-radius: 15px; border-radius: 15px; text-align:center; width:100px; color:#000; padding:3px;" type="submit" value="Logout" onclick="location = 'zipit-files.php?logout=1';"/><br></center>

</div>

</body>
</html>
