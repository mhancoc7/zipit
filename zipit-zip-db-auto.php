<?php 
###############################################################
# Zipit Backup Utility
###############################################################
# Developed by Jereme Hancock for Cloud Sites
# Visit http://zipitbackup.com for updates
###############################################################

// specify namespace
   namespace OpenCloud;

###### Enter your database credentials here ######

    $db_host = "ENTER YOUR DATABASE HOST";
    $db_user = "ENTER YOUR DATABASE USER";
    $db_pass = "ENTER YOUR DATABASE PASSWORD";
    $db_name = "ENTER YOUR DATABASE NAME";

########## DO NOT EDIT BELOW THIS LINE! ##########

ini_set('max_execution_time', 3600); 

// require zipit configuration
    require('zipit-config.php');

// set working directory
    chdir("../../..");

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
    $dir = "./zipit-backups/databases";

foreach (glob($dir."*") as $file) {
if (filemtime($file) < time() - 86400) {
    shell_exec("rm -rf ./zipit-backups/databases/*");
    }
}

// create local backups folders if they are not there
if (!is_dir('./web/content/zipit/zipit-backups')) {
    mkdir('./web/content/zipit/zipit-backups');
}
if (!is_dir('./web/content/zipit/zipit-backups/databases')) {
    mkdir('./web/content/zipit/zipit-backups/databases');
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
   echo "$logtimestamp -- Cloud Files API connection could not be established.\n$logtimestamp Zipit Auto completed\n";
   fwrite($fh, $stringData);
   fclose($fh);
   die();
}

// check database connection
    $link = mysql_connect($db_host,$db_user,$db_pass);
        if (!$link) {
// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Database connection failed.\n$logtimestamp Zipit Auto completed\n\n";
   echo "$logtimestamp -- Database connection failed.\n$logtimestamp Zipit Auto completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);

        die();
}

// check for database existence
    $db_selected = mysql_select_db($db_name, $link);
        if (!$db_selected) {
// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Database connection failed.\n$logtimestamp Zipit Auto completed\n\n";
   echo "$logtimestamp -- Database connection failed.\n$logtimestamp Zipit Auto completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   die();
}

// set timestamp format
    $timestamp =  date("M-d-Y_H-i-s");
 
// write to log
    $logtimestamp =  date("M-d-Y_H-i-s"); 
    $fh = fopen($zipitlog, 'a') or die("can't open file");
    $stringData = "$logtimestamp -- Zipit creation for $db_name-$timestamp.zip\n";
    "$logtimestamp -- Zipit Auto creation for $db_name-$timestamp.zip\n";
    fwrite($fh, $stringData);
    fclose($fh);

// check database size
function file_size_info($filesize) { 
 $bytes = array('KB', 'KB', 'MB', 'GB', 'TB'); # values are always displayed  
 if ($filesize < 1024) $filesize = 1; # in at least kilobytes. 
 for ($i = 0; $filesize > 1024; $i++) $filesize /= 1024; 
 $file_size_info['size'] = ceil($filesize); 
 $file_size_info['type'] = $bytes[$i]; 
 return $file_size_info; 
} 

$db_link = @mysql_connect($db_host, $db_user, $db_pass) 
 or exit('Could not connect: ' . mysql_error()); 
$db = @mysql_select_db($db_name, $db_link) 
 or exit('Could not select database: ' . mysql_error()); 
// Calculate DB size by adding table size + index size: 
$rows = mysql_query("SHOW TABLE STATUS"); 
$dbSize = 0; 
while ($row = mysql_fetch_array($rows)) { 
 $dbSize += $row['Data_length'] + $row['Index_length']; 
} 

if ($dbSize > 4831838208) {

// Cloud Files object size exceeded

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Zipit Auto Failed, Cloud Files max object size of 5GB exceeded.\n$logtimestamp Zipit completed\n\n";
   echo "$logtimestamp -- Zipit Auto Failed, Cloud Files max object size of 5GB exceeded.\n$logtimestamp Zipit Auto completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   die();
}

// set the command to run
    $cmd = "mysqldump -h $db_host -u $db_user --password='$db_pass' $db_name > ./web/content/zipit/zipit-backups/databases/$db_name-$timestamp.sql; zip -9prj ./web/content/zipit/zipit-backups/databases/$db_name-$timestamp.zip ./web/content/zipit/zipit-backups/databases/$db_name-$timestamp.sql";

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
    flush();
    }
    echo ".";
    echo "\n";
    echo "\n";
    pclose($pipe);

// create container if it doesn't already exist
$cont = $ostore->Container();
$cont->Create(array('name'=>"zipit-backups-databases-$url"));
    
// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Cloud Files container successfully created or already exists.\n";
   echo "$logtimestamp -- Cloud Files container successfully created or already exists.\n";
   fwrite($fh, $stringData);
   fclose($fh);

// set zipit object
$obj = $cont->DataObject();

$obj->Create(array('name' => "$db_name-$timestamp.zip", 'content_type' => 'application/zip'), $filename="./web/content/zipit/zipit-backups/databases/$db_name-$timestamp.zip");

// get etag(md5)
    $etag = $obj->hash; 

// generate md5 hash
    $md5file = "./web/content/zipit/zipit-backups/databases/$db_name-$timestamp.zip";
    $md5 = md5_file($md5file);

// compare md5 with etag
if ($md5 == $etag) {

// clean up local backups
    shell_exec('rm -rf ./web/content/zipit/zipit-backups/databases/*');
    
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
   $obj->Delete(array('name'=>"$db_name-$timestamp.zip"));
   
// remove local file
    shell_exec("rm -rf ./web/content/zipit/zipit-backups/databases/*");

// MD5 mismatch

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Zipit Auto Failed, MD5 Hash did not match on $db_name-$timestamp.zip\n$logtimestamp Zipit Auto completed\n\n";
   echo "$logtimestamp -- Zipit Auto Failed, MD5 Hash did not match on $db_name-$timestamp.zip\n$logtimestamp Zipit Auto completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   die();
}

// write to log 
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp -- Zipit Auto Completed Successfully for $db_name-$timestamp.zip\n$logtimestamp Zipit Auto completed\n\n";
   echo "$logtimestamp -- Zipit Auto Completed Successfully for $db_name-$timestamp.zip\n$logtimestamp Zipit Auto completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);

?>
