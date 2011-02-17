Matt's Homework Project
=======================

Using the User Class
-----

You are required to include the user library file, and create a new instance of
the class **before** any output has been sent.
If output is sent before the initialization of the class, cookies will not be
able to be sent to the client.

    <?php
      require_once '/path/to/user.php';
      $user = new User;

You will then be able to use any of the following public methods:

 - `login(username, password)`: Check that a user's credentials validate.
 - `logout()`: Logout the current user, if no user is logged in, it will have no effect.
 - `create(username, password, firstname, lastname, title, admin)`: Create a new user.
 - `logged_in()`: Specified whether a user is logged in (has been authenticated).
 - `fullname()`: Return the current user's full name.
 - `title()`: Return the job title of the current user.
 - `admin()`: Specifiy whether the current user is an administrator.

Example Usage
-------------

The following is an example usage on a regular page.

    if($user->logged_in()) {
      echo 'Welcome back, ' . $user->fullname() . '!';
      if($user->admin()) {
        echo ' Visit the <a href="http://example.com/admin/">Admin Area</a>';
      }
    }
    else {
      // Redirect to the login page and exit script.
    }

Login is simple:

    if($login_form_has_been_submitted) {
      if($user->login($_POST['username'], $_POST['password'])) {
        // Hurrah! The user has been logged in! Carry on the rest of the page
        // like normal.
      }
      else {
        // The user did not provide the correct details, show them the login
        // form again.
      }
    }

Login is even simpler:

    $user->logout();
    // Maybe you'd like to redirect to the homepage, or login page at this point?

Additional Notes
----------------

**Please note:** All user details regarding login is filtered for you, you don't
have to worry about a thing. You do, however, have to filter the first name,
last name and job title yourself, as I'm not sure what specification you have
for those - I have by default limited them to 64 characters each. The username
can only contain alphanumeric characters.