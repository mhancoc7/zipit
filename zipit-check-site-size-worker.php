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

// set working directory
    chdir("../../..");

// check site size
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

$fp = fopen("./web/content/zipit/zipit-site-size.php", "w");
fwrite($fp, $site_size);
fclose($fp);

 
?>
