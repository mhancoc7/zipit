<?php 
###############################################################
# Zipit Backup Utility
###############################################################
# Developed by Jereme Hancock for Cloud Sites
# Visit http://zipitbackup.com for updates
###############################################################

// set default timezone
    date_default_timezone_set('America/Chicago');
    $snaptime = date("H.i");

// include password protection
    include("zipit-login.php"); 

// require zipit configuration
    require('zipit-config.php');

// set working directory
    chdir("../../..");

// clean up local backups
    shell_exec("rm -rf ./web/content/zipit/zipit-backups/databases/*");

// create local backups folders if they are not there
if (!is_dir('./web/content/zipit/zipit-backups')) {
    mkdir('./web/content/zipit/zipit-backups');
}
if (!is_dir('./web/content/zipit/zipit-backups/databases')) {
    mkdir('./web/content/zipit/zipit-backups/databases');
}
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Zipit Backup Utility -- Databases</title>

<link href="./css/style_zip_db.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
function goBack()
  {
  window.history.back()
  }
</script>
</head>
<body>
<div class="wrapper">
	<center><ul class="tabs group">
	</ul></center>
<div class="head">Zipit Backup Utility</div>
<?php

//if (($snaptime > 11.00) && ($snaptime < 12.05) )  
//{
 //  echo '<script type="text/javascript">';
  // echo 'alert("Due to server constraints Zipit Backup cannot be run at this time.\n\n Please try again later.")';
 //  echo '</script>'; 
 //  echo "<script>location.href='zipit-files.php'</script>"; 
//}

//elseif (($snaptime > 15.00) && ($snaptime < 16.05) ) 

//{
  // echo '<script type="text/javascript">';
  // echo 'alert("Due to server constraints Zipit Backup cannot be run at this time.\n\n Please try again later.")';
  // echo '</script>'; 
  // echo "<script>location.href='zipit-files.php'</script>"; 
//}

//elseif (($snaptime > 19.00) && ($snaptime < 20.05) ) 

//{
  // echo '<script type="text/javascript">';
  // echo 'alert("Due to server constraints Zipit Backup cannot be run at this time.\n\n Please try again later.")';
  // echo '</script>'; 
  // echo "<script>location.href='zipit-files.php'</script>"; 
//}

//elseif (($snaptime > 23.00) && ($snaptime < 24.05) ) 

//{
  // echo '<script type="text/javascript">';
  // echo 'alert("Due to server constraints Zipit Backup cannot be run at this time.\n\n Please try again later.")';
  // echo '</script>'; 
   //echo "<script>location.href='zipit-files.php'</script>"; 
//}
 
//else 

//{

// define zipit log file
    $zipitlog = "logs/zipit.log";

if ($logsize > 52428800) {
shell_exec("mv logs/zipit.log logs/zipit_old.log");
}

// require Cloud Files API
   require('./web/content/zipit/api/cloudfiles.php');

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
   echo "<script>location.href='zipit-db.php'</script>"; 

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp Zipit started\n$logtimestamp -- Cloud Files API connection could not be established.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   echo "<script>location.href='zipit-db.php'</script>";
   die();
}

// get database login credentials from form
    $db_host = $_POST['db_host'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $db_name = $_POST['db_name'];

// check database connection
    $link = mysql_connect($db_host,$db_user,$db_pass);
        if (!$link) {
// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp Zipit started\n$logtimestamp -- Database connection failed.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);

        die('<center><font color=red>Connection Failed!</font><br /><br /><input style="border: 1px solid #818185; background-color:#ccc; -moz-border-radius: 15px; border-radius: 15px; text-align:center; width:350px; color:#000; padding:3px;" type="submit" value="Check Connection Settings -- Click To Continue" onclick="goBack()"/></center>');
}

// check for database existence
    $db_selected = mysql_select_db($db_name, $link);
        if (!$db_selected) {
// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp Zipit started\n$logtimestamp -- Database connection failed.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);

        die('<center><font color=red>Connection Failed!</font><br /><br /><input style="border: 1px solid #818185; background-color:#ccc; -moz-border-radius: 15px; border-radius: 15px; text-align:center; width:350px; color:#000; padding:3px;" type="submit" value="Check Connection Settings -- Click To Continue" onclick="goBack()"/></center>');
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
		//print ($GLOBALS['CONTENT']);
		//$GLOBALS['CONTENT'] = '';
		print($this->getContent());
		$this->flush();
		//$this->setProgressBarProgress(0);
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
    $stringData = "$logtimestamp Zipit started\n$logtimestamp -- Zipit creation for $db_name-$timestamp.zip\n";
    fwrite($fh, $stringData);
    fclose($fh);

// set the command to run
    $cmd = "mysqldump -h $db_host -u $db_user --password='$db_pass' $db_name > ./web/content/zipit/zipit-backups/databases/$db_name-$timestamp.sql; zip -9prj ./web/content/zipit/zipit-backups/databases/$db_name-$timestamp.zip ./web/content/zipit/zipit-backups/databases/$db_name-$timestamp.sql";
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

// check file size
if (filesize('$url-$timestamp.zip') > 5261334937) { 
// alert Cloud Files object size exceeded
   echo '<script type="text/javascript">';
   echo 'alert("Backup Failed!\n\nCloud Files max object size of 5GB exceeded.\n\nPlease reduce the size of your database and try again.")';
   echo '</script>';  

// clean up local backups
   shell_exec("rm -rf ./web/content/zipit/zipit-backups/databases/*");

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Zip Failed, Cloud Files max object size of 5GB exceeded.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   echo "<script>location.href='zipit-db.php'</script>";
   die();
}

// get file to transfer to Cloud Files
    $res  = fopen("./web/content/zipit/zipit-backups/databases/$db_name-$timestamp.zip", "rb");
    $temp = tmpfile();
    $size = 0.0;
    while (!feof($res))
    {
        $bytes = fread($res, 1024);
        fwrite($temp, $bytes);
        $size += (float) strlen($bytes);
    }

    fclose($res);
    fseek($temp, 0);

// create zipit-backups-files Cloud Files container if it does exist and send file to zipit-backups-files container
    $container = $conn->create_container("zipit-backups-databases");
    $container->make_private();

// set zipit object
    $object = $container->create_object("$db_name-$timestamp.zip");
    $object->content_type = "application/zip";
    $object->write($temp, $size);

// end progress bar
   $p->setProgressBarProgress(100);
    pclose($pipe);

// get etag(md5)
    $etag = $object->getETag();
    fclose($temp); 

// generate md5 hash
    $md5file = "./web/content/zipit/zipit-backups/databases/$db_name-$timestamp.zip";
    $md5 = md5_file($md5file);

// compare md5 with etag
if ($md5 == $etag) {

// clean up local backups
    shell_exec("rm -rf ./web/content/zipit/zipit-backups/databases/*");
}

else {
// remove file from Cloud Files
    $container->delete_object("$db_name-$timestamp.zip");
   
// remove local file
    shell_exec("rm -rf ./web/content/zipit/zipit-backups/databases/*");

// alert MD5 mismatch
    echo '<script type="text/javascript">';
    echo 'alert("Backup Failed!\n\nFile integrety check failure.\n\nPlease try again.")';
    echo '</script>';  
    echo "<script>location.href='zipit-db.php'</script>";

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Zip Failed, MD5 Hash did not match on $db_name-$timestamp.zip\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   echo "<script>location.href='zipit-files.php'</script>";
   die();
}

echo "<center><input readonly style='border: 1px solid #818185; background-color:#ccc; -moz-border-radius: 15px; border-radius: 15px; text-align:center; width:300px; color:#000; padding:3px;' type='submit' value='Backup Complete -- Click To Continue' onclick='location = \"zipit-db.php\";'/></center>";
echo "<script>location.href='zipit-download-db.php?file=$db_name-$timestamp.zip'</script>";

// write to log 
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Zip Completed Successfully for $db_name-$timestamp.zip\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);

//}

?>
</div>
</body>
</html>
