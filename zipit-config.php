<?php 
###############################################################
# Zipit Backup Utility
###############################################################
# Developed by Jereme Hancock for Cloud Sites
# Visit http://zipitbackup.com for updates
############################################################### 

// Zipit Backup Utility -- Be sure to change the password!!
$zipituser = "admin";
$password = "medulla";

// Cloud Files API -- Required!!
$username = "mhancoc7";
$key = "5a5dbf1b433ee3c6fd8db2d31433a9fb";

// Zipit Auto Hash
$auto_hash = "a1fa5d3649a0";

// determine datacenter for storage
$string = $_SERVER["PHP_DOCUMENT_ROOT"];
    $pos = strpos($string, "dfw");
    if ($pos == false) {
        $datacenter = "ORD";
    } else {
        $datacenter = "DFW";
    }

?>
