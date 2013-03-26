<?php
###############################################################
# Zipit Backup Utility
###############################################################
# Developed by Jereme Hancock for Cloud Sites
# Visit http://zipitbackup.com for updates
###############################################################

$remote_version = file_get_contents('zipit-remote_version.php');

$local_version = file_get_contents('version.php');

if ($remote_version > $local_version) {
echo "There is a new version of Zipit!<br/><br/>
Check it out here: <a href='http://zipitbackup.com' target='_blank'>Zipitbackup.com</a>
<br/><br/>
";
}

?>



