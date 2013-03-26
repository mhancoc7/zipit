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

?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Zipit Backup Utility -- File Backup</title>

<link href="./css/style_auto.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
function SelectAll(id)
{
    document.getElementById(id).focus();
    document.getElementById(id).select();
}
</script>

<link href="css/lightbox.css" rel="stylesheet" />

<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/lightbox.js"></script>

</head>
<body>
	<center><ul class="tabs group">
	  <li><a href="zipit-files.php" onfocus="this.blur();">Files</a></li> 
	  <li><a href="zipit-db.php" onfocus="this.blur();">Databases</a></li> 
          <li><a href="zipit-logs.php" onfocus="this.blur();">Logs</a></li> 
          <li class="active"><a href="#" onfocus="this.blur();">Auto Backups</a></li> 
          <li><a href="zipit-settings.php" onfocus="this.blur();">Settings</a></li>
	</ul></center>
<div class="wrapper">
<center>
<?php


// include update checker
    include("zipit-update-footer.php");

?>
<h2>Automatic Backup Instructions</h2></center>
<br />
<div style="float:left;">
You can easily automate Zipit via a Scheduled Task (cronjob) via the Cloud Sites Control Panel. <br /><br /> For more info on setting up a Scheduled Task (cronjob) in Cloud Sites <a href="http://www.rackspace.com/knowledge_center/article/how-do-i-schedule-a-cron-job-for-cloud-sites" target="_blank">click here</a>.<br /><br />
Below you will find the "Commands" to use for the Scheduled Task (cronjob). <br /><br />
</div>
<div style="float:left;">
<h3>Files:</h3>
<input type="text" id="files" name="files" value="<?php echo 'http://'.$url.'/zipit/zipit-zip-files-auto.php?id='.$auto_hash; ?>" readonly style="text-align:center; width:500px" onClick="SelectAll('files');">
<br /><br />
<h3>Databases:</h3>
<input type="text" id="db" name="db" value="<?php echo 'http://'.$url.'/zipit/zipit-zip-db-auto.php?id='.$auto_hash; ?>" readonly style="text-align:center; width:500px" onClick="SelectAll('db');"><br /><br />
</div>
<div style="float:right">
<h3>Click Image for Example</h3>
<a href="images/zipit_auto_large.png" rel="lightbox"><img src="images/zipit_auto_large.png" width="200px" /></a>
</div>
<div style="float:left;"><em><strong>
*** You will need to edit the zipit-zip-db-auto.php with your Database credentials ***<br /><br /></strong></em>
</div>
</div>

</body>
</html>
