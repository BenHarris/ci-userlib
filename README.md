CodeIgniter User Authentication Library ![Project Status](http://stillmaintained.com/mynameiszanders/ci-userlib.png "Project Status")
=====================================================================================================================================

Please view the [online documentation](http://mynameiszanders.github.com/ci-userlib/)
for more detailed coverage on this library. This README is *not* kept up-to-date.

Using the User Class
-----

You are required to load the library **before** any output is sent to the browser.
If output is sent before the initialization of the class, cookies will not be
able to be sent to the client.

    $this->load->library('userlib');

You will then be able to use any of the following public methods:

 - `login(username, password)`: Check that a user's credentials validate.
 - `logout()`: Logout the current user, if no user is logged in, it will have no effect.
 - `create(username, password, firstname, lastname, title, admin)`: Create a new user.
 - `logged_in()`: Specified whether a user is logged in (has been authenticated).
 - `admin()`: Specifiy whether the current user is an administrator.

Example Usage
-------------

The following is an example usage on a regular page.

    if($this->userlib->logged_in()) {
      echo 'Welcome back, ' . $user->fullname() . '!';
      if($this->userlib->admin()) {
        echo ' Visit the <a href="http://example.com/admin/">Admin Area</a>';
      }
    }
    else {
      // Redirect to the login page and exit script.
    }

Login is simple:

    if($login_form_has_been_submitted) {
      if($this->userlib->login($_POST['username'], $_POST['password'])) {
        // Hurrah! The user has been logged in! Carry on the rest of the page
        // like normal.
      }
      else {
        // The user did not provide the correct details, show them the login
        // form again.
      }
    }

Login is even simpler:

    $this->userlib->logout();
    // Maybe you'd like to redirect to the homepage, or login page at this point?

Additional Notes
----------------

**Please note:** All user details regarding login is filtered for you, you don't
have to worry about a thing. You do, however, have to filter the first name,
last name and job title yourself, as I'm not sure what specification you have
for those - I have by default limited them to 64 characters each. The username
can only contain alphanumeric characters.
