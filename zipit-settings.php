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

if (isset($_POST["Submit"])) {

$string = '<?php 
###############################################################
# Zipit Backup Utility
###############################################################
# Developed by Jereme Hancock for Cloud Sites
# Visit http://zipitbackup.com for updates
############################################################### 

// Zipit Backup Utility -- Be sure to change the password!!
$zipituser = "'. $_POST["zipituser"]. '";
$password = "'. $_POST["password"]. '";

// Cloud Files API -- Required!!
$username = "'. $_POST["username"]. '";
$key = "'. $_POST["key"]. '";

// Datacenter
$datacenter = "'. $_POST["datacenter"]. '";

// URL
$url = "'. $_POST["url"]. '";

// Zipit Auto Hash
$auto_hash = "'. $_POST["auto_hash"]. '";

?>';

$fp = fopen("zipit-config.php", "w");

fwrite($fp, $string);

fclose($fp);

//redirect to login

echo '<script type="text/javascript">';
echo 'alert("Zipit Successfully Updated!\n\nIf you updated your Zipit Login Credentials you will be redirected to the login page.")';
echo '</script>';

echo "<script>location.href='./zipit-files.php'</script>";
}

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

<script language="javascript" type="text/javascript">
function removeSpaces(string) {
 return string.split(' ').join('');
}
</script>

<link href="css/lightbox.css" rel="stylesheet" />

<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/lightbox.js"></script>

<link rel="stylesheet" href="css/hint.css">

</head>
<body>
	<center><ul class="tabs group">
	  <li><a href="zipit-files.php" onfocus="this.blur();">Files</a></li> 
	  <li><a href="zipit-db.php" onfocus="this.blur();">Databases</a></li> 
          <li><a href="zipit-logs.php" onfocus="this.blur();">Logs</a></li> 
          <li><a href="zipit-auto.php" onfocus="this.blur();">Auto Backups</a></li> 
          <li class="active"><a href="#" onfocus="this.blur();">Settings</a></li> 
	</ul></center>
<div class="wrapper">
<center>
<?php


// include update checker
    include("zipit-update-footer.php");

?>
<h2>Configuration Settings</h2></center>
<br />
<div style="text-align:center">
<form action="" method="post" name="install" id="install">
<p>
     Zipit Username:<br />
    <input name="zipituser" type="text" id="zipituser" value="<?php echo $zipituser;?>" onblur="this.value=removeSpaces(this.value);" required="required"> <span class="hint--right" data-hint="This is the username for the Zipit Backup Utility."><img src="images/hint.png" /></span>
</p>

<p>
    Zipit Password:<br />
    <input name="password" type="password" id="password" value="<?php echo $password;?>" onblur="this.value=removeSpaces(this.value);" required="required"> <span class="hint--right" data-hint="This is the password for the Zipit Utility."><img src="images/hint.png" /></span>
</p>
<br />
<hr />
<br />
<p>
    API Username:<br />
    <input name="username" type="text" id="username" value="<?php echo $username;?>" onblur="this.value=removeSpaces(this.value);" required="required"> <span class="hint--right" data-hint="This is the username for your API access. This is the same username that you use to login to manage.rackspacecloud.com"><img src="images/hint.png" /></span>
</p>

<p>
    API Key:<br />
    <input name="key" type="text" id="key" value="<?php echo $key;?>" onblur="this.value=removeSpaces(this.value);" required="required"> <span class="hint--right" data-hint="This is your API Key."><img src="images/hint.png" /></span>
</p>
<br />
<hr />
<br />
<p>
    Datacenter:<br />
    <input name="datacenter" type="text" id="datacenter" value="<?php echo $datacenter ?>" onblur="this.value=removeSpaces(this.value);" required="required"> <span class="hint--right" data-hint="This is the Datacenter where your backups will be stored. Changing this can affect how much bandwidth Zipit uses."><img src="images/hint.png" /></span>
</p>

<p>
    URL:<br />
    <input name="url" type="text" id="url" value="<?php echo $url ?>" onblur="this.value=removeSpaces(this.value);" required="required"> <span class="hint--right" data-hint="This is the URL of your site. It is used to name the backups and the containers that they are stored in. Do not include http://"><img src="images/hint.png" /></span>
</p>

<p>
    Auto Hash:<br />
    <input name="auto_hash" type="text" id="auto_hash" value="<?php echo $auto_hash ?>" onblur="this.value=removeSpaces(this.value);" required="required"> <span class="hint--right" data-hint="This unique code is used for the Automated settings. Changing this will affect any previously configured Scheduled Tasks (cronjobs)."><img src="images/hint.png" /></span>
</p>

<p>
<br /><br />
    <input type="submit" name="Submit" value="Update" style="background-color:#ccc; -moz-border-radius: 15px; border-radius: 15px; text-align:center; width:250px; color:#000; padding:3px;">
</p>

</form>
</div>
</body>
</html>
