<?php 
###############################################################
# Zipit Backup Utility
###############################################################
# Developed by Jereme Hancock for Cloud Sites
# Visit http://zipitbackup.com for updates
###############################################################

// specify namespace
   namespace OpenCloud;

ini_set('max_execution_time', 3600);

// set default timezone
    date_default_timezone_set('America/Chicago');

// include password protection
    include("zipit-login.php"); 

// require zipit configuration
    require('zipit-config.php');

// remove left over local backups
   shell_exec("rm -rf ./zipit-backups/files/*");

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
<title>Zipit Backup Utility -- Files</title>

<link href="./css/style_zip_files.css" rel="stylesheet" type="text/css" />

</head>
<body>
<div class="wrapper">
	<center><ul class="tabs group">
	</ul></center>
<?php

// define zipit log file
    $zipitlog = "../../../logs/zipit.log";
    $logsize = filesize($zipitlog);

if ($logsize > 52428800) {
shell_exec("mv ../../../logs/zipit.log ../../../logs/zipit_old.log");
}

define('RAXSDK_TIMEOUT', '3600');

// require Cloud Files API
   require_once('./api/lib/rackspace.php');

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
    
// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp Zipit started\n$logtimestamp -- Zipit connected to Cloud Files successful.\n";
   fwrite($fh, $stringData);
   fclose($fh);
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

// progress bar class/functions
class ProgressBar {
	var $percentDone = 0;
	var $pbid;
	var $pbarid;
	var $tbarid;
	var $textid;
	var $decimals = 1;

	function __construct($percentDone = 0) {
		$this->pbid = 'pb';
		$this->pbarid = 'progress-bar';
		$this->tbarid = 'transparent-bar';
		$this->textid = 'pb_text';
		$this->percentDone = $percentDone;
	}

	function render() {
		print($this->getContent());
		$this->flush();
	}

	function getContent() {
		$this->percentDone = floatval($this->percentDone);
		$percentDone = number_format($this->percentDone, $this->decimals, '.', '') .'%';
		$content .= '<div id="'.$this->pbid.'" class="pb_container">
			<div id="'.$this->textid.'" class="'.$this->textid.'">'.$percentDone.'</div><br><div style="position:relative; top:-10px;">Please wait...</div>
			<div class="pb_bar">
				<div id="'.$this->pbarid.'" class="pb_before"
				style="width: '.$percentDone.';"></div>
				<div id="'.$this->tbarid.'" class="pb_after"></div>
			</div>
			<br style="height: 1px; font-size: 1px;"/>
		</div>
		<style>
			.pb_container {
				position: relative;
			}
			.pb_bar {
				width: 100%;
				height: 1.3em;
				border: 1px solid silver;
				-moz-border-radius-topleft: 5px;
				-moz-border-radius-topright: 5px;
				-moz-border-radius-bottomleft: 5px;
				-moz-border-radius-bottomright: 5px;
				-webkit-border-top-left-radius: 5px;
				-webkit-border-top-right-radius: 5px;
				-webkit-border-bottom-left-radius: 5px;
				-webkit-border-bottom-right-radius: 5px;
			}
			.pb_before {
				float: left;
				height: 1.3em;
				background-color: #43b6df;
				-moz-border-radius-topleft: 5px;
				-moz-border-radius-bottomleft: 5px;
				-webkit-border-top-left-radius: 5px;
				-webkit-border-bottom-left-radius: 5px;
			}
			.pb_after {
				float: left;
				background-color: #FEFEFE;
				-moz-border-radius-topright: 5px;
				-moz-border-radius-bottomright: 5px;
				-webkit-border-top-right-radius: 5px;
				-webkit-border-bottom-right-radius: 5px;
			}
			.pb_text {
				padding-top: 0.1em;
				position: absolute;
				left: 48%;
                                display:none;
			}
		</style>'."\r\n";
		return $content;
	}

	function setProgressBarProgress($percentDone, $text = '') {
		$this->percentDone = $percentDone;
		$text = $text ? $text : number_format($this->percentDone, $this->decimals, '.', '').'%';
		print('
		<script type="text/javascript">
		if (document.getElementById("'.$this->pbarid.'")) {
			document.getElementById("'.$this->pbarid.'").style.width = "'.$percentDone.'%";');
		if ($percentDone == 100) {
			print('document.getElementById("'.$this->pbid.'").style.display = "none";');
		} else {
			print('document.getElementById("'.$this->tbarid.'").style.width = "'.(100-$percentDone).'%";');
		}
		if ($text) {
			print('document.getElementById("'.$this->textid.'").innerHTML = "'.htmlspecialchars($text).'";');
		}
		print('}</script>'."\n");
		$this->flush();
	}

	function flush() {
		print str_pad('', intval(ini_get('output_buffering')))."\n";
		flush();
	}
}
echo '<center>';

// start progress bar
    $p = new ProgressBar();
    echo '<div style="width: 300px;">';
    $p->render();

// set timestamp format
    $timestamp =  date("M-d-Y_H-i-s"); 

// write to log
    $logtimestamp =  date("M-d-Y_H-i-s"); 
    $fh = fopen($zipitlog, 'a') or die("can't open file");
    $stringData = "$logtimestamp -- Zipit zip started.\n";
    fwrite($fh, $stringData);
    fclose($fh);

// set the command to run
    $cmd = "php zipit-check-site-size-worker.php $auto_hash && php zipit-zip-files-worker.php $auto_hash && php zipit-cf-files-worker.php $auto_hash";
    $pipe = popen($cmd, 'r');

    if (empty($pipe)) {
    throw new Exception("Unable to open pipe for command '$cmd'");
    }

    stream_set_blocking($pipe, false);

    while (!feof($pipe)) {
    fread($pipe, 1024);

for ($i = 0; $i < ($size = 100); $i++) {
// keeps browser from timing out after 30 seconds
   $p->setProgressBarProgress($i*100/$size);
   usleep(100000*0.1);
}
    }

// end progress bar
   $p->setProgressBarProgress(100);
    pclose($pipe);
  
   $hash_check = file_get_contents('zipit-hash-check-files.php');
   $site_size = file_get_contents('zipit-site-size.php');
   $api_check = file_get_contents('zipit-api-check-files.php');
   $md5_check = file_get_contents('zipit-md5-check-files.php');

   if ($hash_check != 'pass') {

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Backup Failed! Authentication not verified.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);

   echo '<script type="text/javascript">';
   echo 'alert("Backup Failed! Unable to verify authentication.")';
   echo '</script>'; 

   echo "<script>location.href='zipit-files.php?logout=1'</script>";
   die();
}

elseif ($site_size > 4608) {

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Backup Failed! Cloud Files max object size of 5GB exceeded.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);

   echo '<script type="text/javascript">';
   echo 'alert("Backup Failed!\n\nCloud Files max object size of 5GB exceeded.\n\nPlease reduce the size of your content and try again.")';
   echo '</script>'; 

   echo "<script>location.href='zipit-files.php'</script>";
   die();

}

   elseif ($api_check == 'fail') {

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Backup Failed! Cloud Files Authentication Failed!\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);

   echo '<script type="text/javascript">';
   echo 'alert("Backup Failed!\n\nCloud Files Authentication Failed!")';
   echo '</script>';

   echo "<script>location.href='zipit-files.php'</script>";
   die();

}

elseif ($md5_check != 'pass') {

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Backup Failed! Integrity check failed.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);

   echo '<script type="text/javascript">';
   echo 'alert("Backup Failed!\n\nIntegrity check failed.")';
   echo '</script>'; 

   echo "<script>location.href='zipit-files.php'</script>";
   die();

}

else {

   echo "<center><input readonly style='border: 1px solid #818185; background-color:#ccc; -moz-border-radius: 15px; border-radius: 15px; text-align:center; width:300px; color:#000; padding:3px;' type= 'submit' value='Backup Complete -- Click To Continue' onclick='location = \"zipit-files.php\";'/></a></center>";

// write to log 
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Zipit Completed Successfully!\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);

}
    
?>

</div>
</body>
</html>
