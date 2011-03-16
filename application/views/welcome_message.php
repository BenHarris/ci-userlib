<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Welcome to CodeIgniter</title>
  <link href="<?php echo site_url(); ?>css/main.css" rel="stylesheet" media="screen, projection" type="text/css" />
</head>
<body>

<h1>
  Welcome to CodeIgniter!
  <?php
    echo $logged_in
       ? anchor('user/logout', 'Logout')
       : anchor('user/login', 'Login');
  ?>
</h1>

<p>The page you are looking at is being generated dynamically by CodeIgniter.</p>

<p>If you would like to edit this page you'll find it located at:</p>
<code>application/views/welcome_message.php</code>

<p>The corresponding controller for this page is found at:</p>
<code>application/controllers/welcome.php</code>

<p>If you are exploring CodeIgniter for the very first time, you should start by reading the <a href="user_guide/">User Guide</a>.</p>


<p><br />Page rendered in {elapsed_time} seconds</p>

</body>
</html>
