<?php
###############################################################
# Zipit Backup Utility
###############################################################
# Developed by Jereme Hancock for Cloud Sites
# Visit http://zipitbackup.com for updates
###############################################################

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

// set working directory
   chdir("../../..");

   shell_exec("zip -9pr ./web/content/zipit/zipit-backups/files/backup.zip lib web logs");


?>
