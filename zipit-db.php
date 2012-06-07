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

// require Cloud Files API
   require('./api/cloudfiles.php');

// define zipit log file
    $zipitlog = "../../../logs/zipit.log";
    $logsize = filesize($zipitlog);

if ($logsize > 52428800) {
shell_exec("mv ../../../logs/zipit.log ../../../logs/zipit_old.log");
}

// clean up local backups
shell_exec("rm -rf ./zipit-backups/databases/*");

// create local backups folders if they are not there
if (!is_dir('./zipit-backups')) {
mkdir('./zipit-backups');
}
if (!is_dir('./zipit-backups/databases')) {
mkdir('./zipit-backups/databases');
}

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
<title>Zipit Backup Utility -- Database Backup</title>

<link href="./css/style_db.css" rel="stylesheet" type="text/css" />


<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<!-- Hide Password Field -->
<script language="Javascript">
$(document).ready(function() {
	$('#password-clear').show();
	$('#password-password').hide();
	$('#password-clear').focus(function() {
		$('#password-clear').hide();
		$('#password-password').show();
		$('#password-password').focus();
	});
	$('#password-password').blur(function() {
		if($('#password-password').val() == '') {
			$('#password-clear').show();
			$('#password-password').hide();
		}
	});

	$('.default-value').each(function() {
		var default_value = this.value;
		$(this).focus(function() {
			if(this.value == default_value) {
				this.value = '';
			}
		});
		$(this).blur(function() {
			if(this.value == '') {
				this.value = default_value;
			}
		});
	});
});
</script>

<script type="text/javascript">
function check(){
var r = confirm("Are you sure you want to delete this backup? \n\nThis will remove your backup from your Cloud Files account permanantly!\n\nBe sure that you are not currently downloading this backup before proceeding.");
if(r){
return true;
}
else{
return false;
}
}
</script>
</head>
<body>
	<center><ul class="tabs group">
	  <li><a href="zipit-files.php" onfocus="this.blur();">Files</a></li> 
	  <li class="active"><a href="#" onfocus="this.blur();">Databases</a></li> 
      <li><a href="zipit-logs.php" onfocus="this.blur();">Logs</a></li> 
	</ul></center>
<div class="wrapper">
<center>
<div class="head">Zipit Backup Utility</div>
<h2>Available Database Backups</h2></center>
<?php

$url = $_SERVER['SERVER_NAME'];
echo "<center><em>";
echo str_trim($url, CHARS, 143, '...');
echo "<br /><br />";

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

// write to log
   $logtimestamp =  date("M-d-Y_H-i-s");
   $fh = fopen($zipitlog, 'a') or die("can't open file");
   $stringData = "$logtimestamp Zipit started\n$logtimestamp -- Cloud Files API connection could not be established.\n$logtimestamp Zipit completed\n\n";
   fwrite($fh, $stringData);
   fclose($fh);
   echo "<script>location.href='zipit-db.php?logout=1'</script>";
   die();
}
$container = $conn->create_container("zipit-backups-databases");
$files = $container->list_objects();
  
reset($files);

if(empty($files)) echo 'No Backups Available';

$i = 1;

foreach ($files as $url) {

 if ($i % 2 != 0) # An odd row
    $rowColor = "#ccc";
  else # An even row
    $rowColor = "#ddd"; 

echo "<div class=\"hidden\">$i</div>";
echo "<div class=\"hidden\">$i</div>";
echo "<div class=\"tablediv\">";
echo "<div class=\"leftdiv\" style=\"background-color:$rowColor\"><a href='zipit-download-db.php?file=$url' title=\"Download $url\">"; echo str_trim($url, CHARS, 135, '...'); echo "</a></div>";

echo "<div class=\"rightdiv\" style=\"background-color:$rowColor\"><a href='zipit-delete-db.php?file=$url' onclick='return check();' title=\"Delete $url\"><img src=\"./images/delete.png\" border=\"0\"/></a></div></div>";

$i++;
echo "<div class=\"clear\"></div>";
}

echo "</em></center><br /><br />";
// inputs for database login credentials with automatic wordpress detection
$wordpress = '../wp-config.php';
if (file_exists($wordpress)) {
include($wordpress);
echo "<center><font color=red>Wordpress Install Detected!</font> <br /><br />Database Credentials:<form name=\"zipit-db\" method=\"post\" action=\"zipit-zip-db.php\">";
echo "<input type=\"text\" name=\"db_host\" value=";if(DB_HOST)
  {echo DB_HOST;} echo " autocomplete=\"off\" required=\"required\"><br />";
echo "<input type=\"text\" name=\"db_user\" value=";if(DB_USER)
  {echo DB_USER;} echo " autocomplete=\"off\" required=\"required\"><br />";
echo "<input type=\"text\" name=\"db_name\" value=";if(DB_NAME)

  {echo DB_NAME;} echo " autocomplete=\"off\" required=\"required\"><br />";
echo "<input type=\"password\" name=\"db_pass\" value=";if(DB_PASSWORD)
  {echo DB_PASSWORD;} echo " autocomplete=\"off\" required=\"required\"><br /><br />";
} else {
echo "<center><br /><br />Database Credentials:<form name=\"zipit-db\" method=\"post\" action=\"zipit-zip-db.php\">";
echo "<input type=\"text\" name=\"db_host\" value=\"Enter Hostname\" autocomplete=\"off\" onclick=\"this.value='';\" required=\"required\"><br />";
echo "<input type=\"text\" name=\"db_user\" value=\"Enter Username\" autocomplete=\"off\"/ onclick=\"this.value='';\" required=\"required\"><br />";
echo "<input type=\"text\" name=\"db_name\" value=\"Enter Database\" autocomplete=\"off\"/ onclick=\"this.value='';\" required=\"required\"><br />";
echo "<div>
    <input id=\"password-clear\" type=\"text\" value=\"Enter Password\" autocomplete=\"off\" required=\"required\">
    <input id=\"password-password\" type=\"password\" name=\"db_pass\" value=\"\" autocomplete=\"off\">
</div>
<br /><br />";
} 
echo "<input type=\"submit\" name=\"submit\" value=\"Backup\" class=\"backup\" style=\"border: 1px solid #818185; background-color:#ccc; -moz-border-radius: 15px; border-radius: 15px; text-align:center; width:100px; color:#000; padding:3px; margin-left:243px; margin-right:50px;\"/> <input class=\"logout\" readonly type=\"button\" style=\"border: 1px solid #818185; background-color:#ccc; -moz-border-radius: 15px; border-radius: 15px; text-align:center; width:100px; color:#000; padding:3px;\" value=\"Logout\" onclick='location = \"zipit-files.php?logout=1\";'/></form>";

?>
<br><br><br>
<font size="1em">Developed by <a href="http://www.cloudsitesrock.com" target="_blank">CloudSitesRock.com</a> for Rackspace Cloud Sites</font></center>
</div>
</body>
</html>
