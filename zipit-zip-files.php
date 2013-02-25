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

// set working directory
    chdir("../../..");

// define zipit log file
    $zipitlog = "logs/zipit.log";
    $logsize = filesize($zipitlog);

// create zipit log file if it doesn't exist
if(!file_exists("$zipitlog")) 
{ 
   $fp = fopen("$zipitlog","w");  
   fwrite($fp,"----Zipit Logs----\n\n");  
   fclose($fp); 
}

// define url
    $url = $_SERVER['SERVER_NAME'];

// clean up local backups if files are older than 24 hours (86400 seconds)
    $dir = "./zipit-backups/files";

foreach (glob($dir."*") as $file) {
if (filemtime($file) < time() - 86400) {
    shell_exec("rm -rf ./zipit-backups/files/*");
    }
}

// create local backups folders if they are not there
if (!is_dir('./web/content/zipit/zipit-backups')) {
    mkdir('./web/content/zipit/zipit-backups');
}
if (!is_dir('./web/content/zipit/zipit-backups/files')) {
    mkdir('./web/content/zipit/zipit-backups/files');
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

// define zipit log file
    $zipitlog = "logs/zipit.log";
    $logsize = filesize($zipitlog);

if ($logsize > 52428800) {
shell_exec("mv logs/zipit.log logs/zipit_old.log");
}

define('RAXSDK_TIMEOUT', '3600');

// require Cloud Files API
   require_once('./web/content/zipit/api/lib/rackspace.php');

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

// check file size
function recursive_filesize($dir) 
{ 
        if (!($dh = opendir($dir))) return 0; 

        $total = 0; 
        while (($file = readdir($dh)) !== false) 
        { 
                if ($file != '.' && $file != '..') 
                { 
                        $file = $dir . '/' . $file; 
                        if (is_dir($file) && is_readable($file) && !is_link($file)) 
                                $total += recursive_filesize($file); 
                        else 
                                $total += filesize($file); 
                } 
        } 
        closedir($dh); 
        return $total; 
} 

$site_size = number_format((recursive_filesize(".")/1024/1024)); 

if ($site_size > 4608) {

// alert Cloud Files object size exceeded
   echo '<script type="text/javascript">';
   echo 'alert("Backup Failed!\n\nCloud Files max object size of 5GB exceeded.\n\nPlease reduce the size of your content and try again.")';
   echo '</script>';  


// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Zipit Failed, Cloud Files max object size of 5GB exceeded.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   echo "<script>location.href='zipit-files.php'</script>";
   die();

}

// set timestamp format
    $timestamp =  date("M-d-Y_H-i-s"); 

// write to log
    $logtimestamp =  date("M-d-Y_H-i-s"); 
    $fh = fopen($zipitlog, 'a') or die("can't open file");
    $stringData = "$logtimestamp -- Zipit creation for $url-$timestamp.zip\n";
    fwrite($fh, $stringData);
    fclose($fh);

// set the command to run
    $cmd = "zip -9pr ./web/content/zipit/zipit-backups/files/$url-$timestamp.zip lib web logs";
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

// create container if it doesn't already exist
$cont = $ostore->Container();
$cont->Create(array('name'=>"zipit-backups-files-$url"));
    
// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Cloud Files container successfully created or already exists.\n";
   fwrite($fh, $stringData);
   fclose($fh);

// set zipit object
$obj = $cont->DataObject();

$obj->Create(array('name' => "$url-$timestamp.zip", 'content_type' => 'application/zip'), $filename="./web/content/zipit/zipit-backups/files/$url-$timestamp.zip");

// get etag(md5)
   $etag = $obj->hash;

// generate md5 hash
    $md5file = "./web/content/zipit/zipit-backups/files/$url-$timestamp.zip";
    $md5 = md5_file($md5file);

// compare md5 with etag
    if ($md5 == $etag) {

// clean up local backups
   shell_exec('rm -rf ./web/content/zipit/zipit-backups/files/*');
   
// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Zipit backup moved to Cloud Files successful. MD5 Hash check passed.\n";
   fwrite($fh, $stringData);
   fclose($fh);
}

else {

// remove file from Cloud Files
   $obj->Delete(array('name'=>"$url-$timestamp.zip"));

// remove local file
   shell_exec("rm -rf ./web/content/zipit/zipit-backups/files/*");

// alert MD5 mismatch
   echo '<script type="text/javascript">';
   echo 'alert("Backup Failed!\n\nFile integrety check failure.\n\nPlease try again.")';
   echo '</script>';  

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Zipit Failed, MD5 Hash did not match on $url-$timestamp.zip\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   echo "<script>location.href='zipit-files.php'</script>";
   die();
}

// end progress bar
   $p->setProgressBarProgress(100);
    pclose($pipe);
    
   echo "<center><input readonly style='border: 1px solid #818185; background-color:#ccc; -moz-border-radius: 15px; border-radius: 15px; text-align:center; width:300px; color:#000; padding:3px;' type= 'submit' value='Backup Complete -- Click To Continue' onclick='location = \"zipit-files.php\";'/></a></center>";

// write to log 
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Zipit Completed Successfully for $url-$timestamp.zip\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);

?>

</div>
</body>
</html>
