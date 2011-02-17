<?php

// BEGIN MYSQL FALLBACKS: The following is because I don't have MySQL installed. Ignore it.
if(!function_exists('mysql_connect')){function mysql_connect(){}}if(!function_exists('mysql_select_db')){function mysql_select_db(){}}if(!function_exists('mysql_num_rows')){function mysql_num_rows(){return 1;}}if(!function_exists('mysql_query')){function mysql_query(){}}if(!function_exists('mysql_fetch_object')){function mysql_fetch_object(){$obj=array('id'=>1,'name'=>'username','hash'=>'60004352880a18b3a80fd76532efff7db9bee0f1','hashnonce'=>sha1('hashnonce'),'cookienonce'=>sha1('cookienonce'),'first'=>'Alexander','last'=>'Baldwin','title'=>'Web Developer','admin'=>true,);return (object)$obj;}}
// END MYSQL FALLBACKS;

  require_once 'user.php';
  $user = new User;
?>

<h1>Matt's Homework Project, innit.</h1>

<?php

  $q = $_SERVER['QUERY_STRING'];
  if($q == 'logout') {
    echo $user->logout()
       ? 'You are now logged out.'
       : 'Could not log you out.';
    echo ' <a href="?">Back</a>.';
    exit;
  }
  elseif($q == 'login') {
    echo $user->login('username', 'password')
       ? 'You are now logged in.'
       : 'Could not log you in.';
    echo ' <a href="?">Back</a>.';
    exit;
  }
  elseif($q == 'admin') {
    if($user->admin()) {
      echo 'You are in the admin area. <a href="?">Back</a>.';
    }
    else {
      echo 'You are not allowed in the admin area. <a href="?">Back</a>.';
    }
    exit;
  }

  echo $user->logged_in()
     ? 'You are logged in. <a href="?logout">Logout</a>.'
     : 'You are not logged in. <a href="?login">Login</a>.';
  if($user->admin()) {
    echo '<br /><a href="?admin">Admin Area</a>.';
  }

?>