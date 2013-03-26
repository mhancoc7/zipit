<?php 
###############################################################
# Zipit Backup Utility
###############################################################
# Developed by Jereme Hancock for Cloud Sites
# Visit http://zipitbackup.com for updates
###############################################################

// specify namespace
   namespace OpenCloud;

// set default timezone
    date_default_timezone_set('America/Chicago');

// Set timestamp
   $timestamp =  date("M-d-Y_H-i-s"); 

// require zipit configuration
    require('zipit-config.php');

// check auto hash
   $id = $argv[1];

   if ($auto_hash == $id) {
      $fp = fopen("zipit-hash-check-files.php", "w");
      fwrite($fp, 'pass');
      fclose($fp);
}

else {
      $fp = fopen("zipit-hash-check-files.php", "w");
      fwrite($fp, 'fail');
      fclose($fp);
      echo "<script>location.href='zipit-files.php?logout=1'</script>";
      die();
}

$site_size = file_get_contents('site-size.php');

if ($site_size > 4608) {

die();

}

// define zipit log file
    $zipitlog = "../../../logs/zipit.log";

?>

<?php

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

$fp = fopen("zipit-api-check-files.php", "w");

fwrite($fp, 'pass');

fclose($fp);

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Connection to Cloud Files successful.\n";
   fwrite($fh, $stringData);
   fclose($fh);
    
}
catch (HttpUnauthorizedError $e) {
$fp = fopen("./zipit-api-check-files.php", "w");

fwrite($fp, 'fail');

fclose($fp);

// clean up local backups
   shell_exec('rm -rf ./zipit-backups/files/*');

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Cloud Files Authentication Failed Cleaned up local backups.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   die();
}

// create container if it doesn't already exist
$cont = $ostore->Container();
$cont->Create(array('name'=>"zipit-backups-files-$url"));

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Cloud Files container created or already exists.\n";
   fwrite($fh, $stringData);
   fclose($fh);

// set zipit object
$obj = $cont->DataObject();

$obj->Create(array('name' => "$url-$timestamp.zip", 'content_type' => 'application/zip'), $filename="./zipit-backups/files/backup.zip");

// get etag(md5)
   $etag = $obj->hash;

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Transfer to Cloud Files complete.\n";
   fwrite($fh, $stringData);
   fclose($fh);

// generate md5 hash
    $md5file = "./zipit-backups/files/backup.zip";
    $md5 = md5_file($md5file);

// compare md5 with etag
    if ($md5 == $etag) {

$fp = fopen("zipit-md5-check-files.php", "w");
fwrite($fp, 'pass');
fclose($fp);

// clean up local backups
   shell_exec('rm -rf ./zipit-backups/files/*');

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Cleaned up local backups.\n";
   fwrite($fh, $stringData);
   fclose($fh);
   
}

else {

$fp = fopen("zipit-md5-check-files.php", "w");
fwrite($fp, 'fail');
fclose($fp);

// remove file from Cloud Files
   $obj->Delete(array('name'=>"$url-$timestamp.zip"));

// remove local file
   shell_exec("rm -rf ./zipit-backups/files/*");

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Integrity check for backup failed. Cleaned up local backups.\n";
   fwrite($fh, $stringData);
   fclose($fh);

}

?>

