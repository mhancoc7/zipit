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
    $dir = "./zipit-backups/databases";

foreach (glob($dir."*") as $file) {
if (filemtime($file) < time() - 86400) {
    shell_exec("rm -rf ./zipit-backups/databases/*");
    }
}

// create local backups folders if they are not there
if (!is_dir('./zipit-backups')) {
mkdir('./zipit-backups');
}
if (!is_dir('./zipit-backups/databases')) {
mkdir('./zipit-backups/databases');
}

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Zipit Backup Utility -- Database Backup</title>

<link href="./css/style_db.css" rel="stylesheet" type="text/css" />


<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<!-- Hide Password Field -->
<script language="Javascript">
$(document).ready(function() {
	$('#password-clear').show();
	$('#password-password').hide();
	$('#password-clear').focus(function() {
		$('#password-clear').hide();
		$('#password-password').show();
		$('#password-password').focus();
	});
	$('#password-password').blur(function() {
		if($('#password-password').val() == '') {
			$('#password-clear').show();
			$('#password-password').hide();
		}
	});

	$('.default-value').each(function() {
		var default_value = this.value;
		$(this).focus(function() {
			if(this.value == default_value) {
				this.value = '';
			}
		});
		$(this).blur(function() {
			if(this.value == '') {
				this.value = default_value;
			}
		});
	});
});
</script>

</head>
<body>
	<center><ul class="tabs group">
	  <li><a href="zipit-files.php" onfocus="this.blur();">Files</a></li> 
	  <li class="active"><a href="#" onfocus="this.blur();">Databases</a></li> 
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
<h2>Available Database Backups</h2></center>
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
$cont->Create(array('name'=>"zipit-backups-databases-$url"));

$list = $cont->ObjectList();

while($o = $list->Next())
	echo $o->name ."<br/><br/>";


// inputs for database login credentials with automatic wordpress detection
$wordpress = '../wp-config.php';
if (file_exists($wordpress)) {
include($wordpress);
echo "<center><font color=red>Wordpress Install Detected!</font> <br /><br />Database Credentials:<form name=\"zipit-db\" method=\"post\" action=\"zipit-zip-db.php\">";
echo "<input type=\"text\" name=\"db_host\" value=";if(DB_HOST)
  {echo DB_HOST;} echo " autocomplete=\"off\" required=\"required\"><br />";
echo "<input type=\"text\" name=\"db_user\" value=";if(DB_USER)
  {echo DB_USER;} echo " autocomplete=\"off\" required=\"required\"><br />";
echo "<input type=\"text\" name=\"db_name\" value=";if(DB_NAME)

  {echo DB_NAME;} echo " autocomplete=\"off\" required=\"required\"><br />";
echo "<input type=\"password\" name=\"db_pass\" value=";if(DB_PASSWORD)
  {echo DB_PASSWORD;} echo " autocomplete=\"off\" required=\"required\"><br /><br />";
} else {
echo "<center><br /><br />Database Credentials:<form name=\"zipit-db\" method=\"post\" action=\"zipit-zip-db.php\">";
echo "<input type=\"text\" name=\"db_host\" value=\"Enter Hostname\" autocomplete=\"off\" onclick=\"this.value='';\" required=\"required\"><br />";
echo "<input type=\"text\" name=\"db_user\" value=\"Enter Username\" autocomplete=\"off\"/ onclick=\"this.value='';\" required=\"required\"><br />";
echo "<input type=\"text\" name=\"db_name\" value=\"Enter Database\" autocomplete=\"off\"/ onclick=\"this.value='';\" required=\"required\"><br />";
echo "<div>
    <input id=\"password-clear\" type=\"text\" value=\"Enter Password\" autocomplete=\"off\" required=\"required\">
    <input id=\"password-password\" type=\"password\" name=\"db_pass\" value=\"\" autocomplete=\"off\">
</div>
<br />";
} 
echo "You can manage your backups via the <a href='https://mycloud.rackspace.com/a/$username/files' target='_blank'>Cloud Files control panel</a>";
echo "<br/><br/>";
echo "</center></em>";	
echo "<br/><br/>";
echo "<input type=\"submit\" name=\"submit\" value=\"Backup\" class=\"backup\" style=\"border: 1px solid #818185; background-color:#ccc; -moz-border-radius: 15px; border-radius: 15px; text-align:center; width:100px; color:#000; padding:3px; margin-left:243px; margin-right:50px;\"/> <input class=\"logout\" readonly type=\"button\" style=\"border: 1px solid #818185; background-color:#ccc; -moz-border-radius: 15px; border-radius: 15px; text-align:center; width:100px; color:#000; padding:3px;\" value=\"Logout\" onclick='location = \"zipit-files.php?logout=1\";'/></form>";

?>
</center>
</div>
</body>
</html>
