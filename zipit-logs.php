<?php 
###############################################################
# Zipit Backup Utility
###############################################################
# Developed by Jereme Hancock for Cloud Sites
# Visit http://zipitbackup.com for updates
###############################################################

// include password protection
    include("zipit-login.php"); 

// require zipit configuration
    require('zipit-config.php');

// define zipit log file
    $zipitlog = "../../../logs/zipit.log";
    $logsize = filesize($zipitlog);


// truncate function
define('CHARS', null);
define('WORDS', null);

function str_trim($string, $method = 'WORDS', $length = 25, $pattern = '...')
{
    if(!is_numeric($length))
    {
        $length = 25;
    }
    
    if(strlen($string) <= $length)
    {
        return $string;
    }
    else
    {

        switch($method)
        {
            case CHARS:
                return substr($string, 0, $length) . $pattern;    
            break;
        
            case WORDS:
                if (strstr($string, ' ') == false) 
                {
                    return str_trim($string, CHARS, $length, $pattern);
                }
            
                $count = 0;
                $truncated = '';
                $word = explode(" ", $string);

                
                foreach($word AS $single)
                {            
                    if($count < $length)
                    {
                        if(($count + strlen($single)) <= $length)
                        {
                            $truncated .= $single . ' ';
                            $count = $count + strlen($single);
                            $count++;
                        }
                        else if(($count + strlen($single)) >= $length)
                        {
                            break;
                        }
                    }
                }
                        
                return rtrim($truncated) . $pattern;
            break;
        }
    }
} 

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Zipit Backup Utility -- Logs</title>

<link href="./css/style_files.css" rel="stylesheet" type="text/css" />


</head>
<body>
    <center><ul class="tabs group">
      <li><a href="zipit-files.php" onfocus="this.blur();">Files</a></li> 
      <li><a href="zipit-db.php" onfocus="this.blur();">Databases</a></li> 
      <li class="active"><a href="zipit-logs.php" onfocus="this.blur();">Logs</a></li> 
	</ul></center>
<div class="wrapper">
<center><div class="head">Zipit Backup Utility</div>
<h2>Available Logs</h2></center>
<?php
     $url = $_SERVER['SERVER_NAME'];
echo "<center><em>";
echo str_trim($url, CHARS, 43, '...');
echo "</em></center><br />";

?>
<div class="logs">
<?php
   // Include & Call Class 
   include_once("lib/class.displaylogs.php"); 
   $lfDispl = new displayLogfile; 

   // Path/Name of Logfile 
   // Choose a short one for example b (!)  
   $filename = "$zipitlog"; 


?> 
<pre style="font-size:12px;"> 
<?php
   $lfDispl->setRowsToRead(100);    // Read 100 rows 
   $lfDispl->setAlign("bottom");       // Last row on top 
   $lfDispl->setFilepath($filename); // from this logfile 
   $lfDispl->setLineBreak(150);  // Break the row after 150 chars 
   $lfDispl->returnFormated();   // Output  
?> 
</pre> 
</div>
<br>
<center><font size="1em">Developed by <a href="http://www.cloudsitesrock.com" target="_blank">CloudSitesRock.com</a> for Rackspace Cloud Sites</font></center>
</div>
</body>
</html>
