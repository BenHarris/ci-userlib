<form action="<?php echo site_url('user/login'); ?>" method="post">
  <?php
    if(isset($incorrect) && $incorrect) {
      ?>
        <div class="error">
          The username or password you provided was incorrect. Please try again.
        </div>
      <?php
    }
  ?>
  <input type="hidden" name="action" value="validate" />
  <input type="text" name="username" /><br />
  <input type="password" name="password" /><br /><br />
  <input type="submit" value="Login" />
</form>
