<?php
###############################################################
# Zipit Backup Utility
###############################################################
# Developed by Jereme Hancock for Cloud Sites
# Visit http://zipitbackup.com for updates
###############################################################

    $file = $_GET['file'];

// include password protection
    include("zipit-login.php"); 

// require zipit configuration
    require('zipit-config.php');

// define url
    $url = $_SERVER['SERVER_NAME'];

// require Cloud Files API
   require('./api/cloudfiles.php');

// define zipit log file
    $zipitlog = "../../../logs/zipit.log";
    $logsize = filesize($zipitlog);

if ($logsize > 52428800) {
shell_exec("mv ../../../logs/zipit.log ../../../logs/zipit_old.log");
}

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Zipit Backup Utility -- Files</title>

<link href="./css/style_zip_files.css" rel="stylesheet" type="text/css" />

</head>
<body>
<div class="wrapper">
	<center><ul class="tabs group">
	</ul></center>
<div class="head">Zipit Backup Utility</div>
<?php

// authenticate to Cloud Files
try {
    $auth = new CF_Authentication($username,$key);
    $auth->authenticate();
    $auth->ssl_use_cabundle();
    $conn = new CF_Connection($auth,$servicenet=false);
}
catch (Exception $e) {
   echo '<script type="text/javascript">';
   echo 'alert("Cloud Files API connection could not be established.\n\nBe sure to check your API credentials in the zipit-config.php file.")';
   echo '</script>'; 

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp Zipit started\n$logtimestamp -- Cloud Files API connection could not be established.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   echo "<script>location.href='zipit-files.php?logout=1'</script>"; 
   die();

}
    $container = $conn->get_container("zipit-backups-files-$url");
    $container->delete_object($file);
echo "<center><input readonly style='border: 1px solid #818185; background-color:#ccc; -moz-border-radius: 15px; border-radius: 15px; text-align:center; width:300px; color:#000; padding:3px;' type= 'submit' value='Backup Deleted -- Click To Continue' onclick='location = \"zipit-files.php\";'/></a></center>";

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp Zipit started\n$logtimestamp -- Zipit backup, $file, successfully deleted.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   
?>
</div>
</body>
</html>
