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

// require zipit configuration
    require('zipit-config.php');

// define zipit log file
    $zipitlog = "../../../logs/zipit.log";
    $logsize = filesize($zipitlog);

if ($logsize > 52428800) {
shell_exec("mv ../../../logs/zipit.log ../../../logs/zipit_old.log");
}

// check hash
   $id = $_GET['id'];

   if ($auto_hash == $id) {

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp Zipit started\n$logtimestamp -- Zipit hash id correct.\n";
   echo "$logtimestamp Zipit started\n$logtimestamp -- Zipit hash id correct.\n";
   fwrite($fh, $stringData);
   fclose($fh);

}

else {

// hash incorrect
// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp Zipit started\n$logtimestamp -- Zipit Failed, hash id incorrect.\n$logtimestamp Zipit completed\n\n";
   echo "$logtimestamp Zipit started\n$logtimestamp -- Zipit Failed, hash id incorrect.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   die();

}

// remove left over local backups
   shell_exec("rm -rf ./zipit-backups/files/*");

// create local backups folders if they are not there
if (!is_dir('./zipit-backups')) {
    mkdir('./zipit-backups');
}
if (!is_dir('./zipit-backups/files')) {
    mkdir('./zipit-backups/files');
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
   $stringData = "$logtimestamp -- Zipit connected to Cloud Files successful.\n";
   echo "$logtimestamp -- Zipit connected to Cloud Files successful.\n";
   fwrite($fh, $stringData);
   fclose($fh);
}
catch (HttpUnauthorizedError $e) {

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Cloud Files API connection could not be established.\n$logtimestamp Zipit completed\n\n";
   echo "$logtimestamp -- Cloud Files API connection could not be established.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   die();
}

// set timestamp format
    $timestamp =  date("M-d-Y_H-i-s"); 

// write to log
    $logtimestamp =  date("M-d-Y_H-i-s"); 
    $fh = fopen($zipitlog, 'a') or die("can't open file");
    $stringData = "$logtimestamp -- Zipit creation for $url-$timestamp.zip\n";
    echo "$logtimestamp -- Zipit creation for $url-$timestamp.zip\n";
    fwrite($fh, $stringData);
    fclose($fh);

// set the command to run
    $cmd = "php zipit-check-site-size-worker.php $auto_hash && php zipit-zip-files-worker.php $auto_hash && php zipit-cf-files-worker.php $auto_hash";

    $pipe = popen($cmd, 'r');

    if (empty($pipe)) {
    throw new Exception("Unable to open pipe for command '$cmd'");
    }

    stream_set_blocking($pipe, false);
    echo "\n";

    while (!feof($pipe)) {
    fread($pipe, 1024);
    sleep(1);
    echo ".";
    echo "\n";
    echo "\n";
    flush();
    }

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
   echo "$logtimestamp -- Backup Failed! Authentication not verified.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   die();
}

elseif ($site_size > 4608) {

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Backup Failed! Cloud Files max object size of 5GB exceeded.\n$logtimestamp Zipit completed\n\n";
   echo "$logtimestamp -- Backup Failed! Cloud Files max object size of 5GB exceeded.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   die();

}

   elseif ($api_check == 'fail') {

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Backup Failed! Cloud Files Authentication Failed!\n$logtimestamp Zipit completed\n\n";
   echo "$logtimestamp -- Backup Failed! Cloud Files Authentication Failed!\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   die();

}

elseif ($md5_check != 'pass') {

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Backup Failed! Integrity check failed.\n$logtimestamp Zipit completed\n\n";
   echo "$logtimestamp -- Backup Failed! Integrity check failed.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   die();

}

else {

// write to log 
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Zipit Completed Successfully!\n$logtimestamp Zipit completed\n\n";
   echo "$logtimestamp -- Zipit Completed Successfully!\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);

}

?>
