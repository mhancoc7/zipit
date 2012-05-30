<?php
###############################################################
# Zipit Backup Utility
###############################################################
# Developed by Jereme Hancock for Cloud Sites
# Visit http://zipitbackup.com for updates
###############################################################

error_reporting(E_ERROR | E_PARSE);

// require zipit configuration
require('zipit-config.php');

$LOGIN_INFORMATION = array(
  $zipituser => $password
);

define('USE_USERNAME', true);

define('LOGOUT_URL', 'zipit-files.php');

define('TIMEOUT_MINUTES', 0);

define('TIMEOUT_CHECK_ACTIVITY', true);

$timeout = (TIMEOUT_MINUTES == 0 ? 0 : time() + TIMEOUT_MINUTES * 60);

if(isset($_GET['logout'])) {
  setcookie("verify", '', $timeout, '/'); // clear password;
  header('Location: ' . LOGOUT_URL);
  exit();
}

if(!function_exists('showLoginPasswordProtect')) {

function showLoginPasswordProtect($error_msg) {
?>
<html>
<head>
  <title>Zipit Backup Utility -- Login</title>
  <META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">
  <META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">

<link href="./css/style_login.css" rel="stylesheet" type="text/css" />

</head>
<body>
<div class="wrapper">
<div class="head">Zipit Backup Utility</div>
  <div style="text-align:center">
  <form method="post">
    <font color="red"><?php echo $error_msg; ?></font><br />
<?php if (USE_USERNAME) echo 'Username: <input type="input" name="access_login" style="font-size:22px;" /><br /><br />Password: &nbsp;'; ?><input type="password" name="access_password" style="font-size:22px;" /><br /><br /><input type="submit" name="Submit" value="Submit" style="background-color:#ccc; -moz-border-radius: 15px; border-radius: 15px; text-align:center; width:100px; color:#000; padding:3px;"/>
  </form><br />
 <font size="1em">Developed by <a href="http://www.cloudsitesrock.com" target="_blank">CloudSitesRock.com</a> for Rackspace Cloud Sites</font>
  </div>  </div>
</body>
</html>

<?php

  die();
}
}

if (isset($_POST['access_password'])) {

  $login = isset($_POST['access_login']) ? $_POST['access_login'] : '';
  $pass = $_POST['access_password'];
  if (!USE_USERNAME && !in_array($pass, $LOGIN_INFORMATION)
  || (USE_USERNAME && ( !array_key_exists($login, $LOGIN_INFORMATION) || $LOGIN_INFORMATION[$login] != $pass ) ) 
  ) {
    showLoginPasswordProtect("Incorrect Login!<br>");
  }
  else {

    setcookie("verify", md5($login.'%'.$pass), $timeout, '/');
    
    unset($_POST['access_login']);
    unset($_POST['access_password']);
    unset($_POST['Submit']);
  }

}

else {

  if (!isset($_COOKIE['verify'])) {
    showLoginPasswordProtect("");
  }

  $found = false;
  foreach($LOGIN_INFORMATION as $key=>$val) {
    $lp = (USE_USERNAME ? $key : '') .'%'.$val;
    if ($_COOKIE['verify'] == md5($lp)) {
      $found = true;

      if (TIMEOUT_CHECK_ACTIVITY) {
        setcookie("verify", md5($lp), $timeout, '/');
      }
      break;
    }
  }
  if (!$found) {
    showLoginPasswordProtect("");
  }
}

?>
