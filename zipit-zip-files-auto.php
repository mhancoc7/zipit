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

// set working directory
    chdir("../../..");

// define url
    $url = $_SERVER['SERVER_NAME'];

// define zipit log file
    $zipitlog = "logs/zipit.log";
    $logsize = filesize($zipitlog);

if ($logsize > 52428800) {
shell_exec("mv logs/zipit.log logs/zipit_old.log");
}

// check auto hash
   $id = $_GET['id'];

   if ($auto_hash == $id) {

echo $id;
// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp Zipit Auto started\n$logtimestamp -- Zipit Auto hash id correct.\n";
   echo "$logtimestamp Zipit Auto started\n$logtimestamp -- Zipit Auto hash id correct.\n";
   fwrite($fh, $stringData);
   fclose($fh);

}

else {

// hash incorrect
// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp Zipit Auto started\n$logtimestamp -- Zipit Auto Failed, Auto hash id incorrect.\n$logtimestamp Zipit Auto completed\n\n";
   echo "$logtimestamp Zipit Auto started\n$logtimestamp -- Zipit Auto Failed, Auto hash id incorrect.\n$logtimestamp Zipit Auto completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   die();

}

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
   $stringData = "$logtimestamp -- Zipit Auto connected to Cloud Files successful.\n";
   echo "$logtimestamp -- Zipit Auto connected to Cloud Files successful.\n";
   fwrite($fh, $stringData);
   fclose($fh);
}
catch (HttpUnauthorizedError $e) {

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Cloud Files API connection could not be established.\n$logtimestamp Zipit Auto completed\n\n";
   echo "$logtimestamp -- Cloud Files API connection could not be established.\n$logtimestamp Zipit Auto completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   die();
}

// set timestamp format
    $timestamp =  date("M-d-Y_H-i-s"); 

// write to log
    $logtimestamp =  date("M-d-Y_H-i-s"); 
    $fh = fopen($zipitlog, 'a') or die("can't open file");
    $stringData = "$logtimestamp -- Zipit Auto creation for $url-$timestamp.zip\n";
    echo "$logtimestamp -- Zipit Auto creation for $url-$timestamp.zip\n";
    fwrite($fh, $stringData);
    fclose($fh);

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

// Cloud Files object size exceeded  

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Zipit Auto Failed, Cloud Files max object size of 5GB exceeded.\n$logtimestamp Zipit Auto completed\n\n";
   echo "$logtimestamp -- Zipit Auto Failed, Cloud Files max object size of 5GB exceeded.\n$logtimestamp Zipit Auto completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   die();
}

// set the command to run
    $cmd = "zip -9pr ./web/content/zipit/zipit-backups/files/$url-$timestamp.zip lib web logs";

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

// create container if it doesn't already exist
$cont = $ostore->Container();
$cont->Create(array('name'=>"zipit-backups-files-$url"));
    
// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Cloud Files container successfully created or already exists.\n";
   echo "$logtimestamp -- Cloud Files container successfully created or already exists.\n";
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
   $stringData = "$logtimestamp -- Zipit Auto backup moved to Cloud Files successful. MD5 Hash check passed.\n";
   echo "$logtimestamp -- Zipit Auto backup moved to Cloud Files successful. MD5 Hash check passed.\n";
   fwrite($fh, $stringData);
   fclose($fh);
}

else {

// remove file from Cloud Files
   $obj->Delete(array('name'=>"$url-$timestamp.zip"));

// remove local file
   shell_exec("rm -rf ./web/content/zipit/zipit-backups/files/*");

// MD5 mismatch  

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Zipit Auto Failed, MD5 Hash did not match on $url-$timestamp.zip\n$logtimestamp Zipit Auto completed\n\n";
   echo "$logtimestamp -- Zipit Auto Failed, MD5 Hash did not match on $url-$timestamp.zip\n$logtimestamp Zipit Auto completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   die();
}
   
// write to log 
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Zipit Auto Completed Successfully for $url-$timestamp.zip\n$logtimestamp Zipit Auto completed\n\n";
   echo "$logtimestamp -- Zipit Auto Completed Successfully for $url-$timestamp.zip\n$logtimestamp Zipit Auto completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);

?>
