<?php

/**
 * Simple User Authentication
 *
 * @author    Alexander Baldwin <http://github.com/mynameiszanders>
 * @link      http://github.com/mynameiszanders/matts_homework_project
 * @license   MIT/X11 <http://j.mp/mit-license>
 */

  class Userlib {

    protected $realm;
    protected $settings = array(
                'realm' => '',
                'table' => 'users',
                'id' => 'id',
                'username' => 'username',
                'password' => 'hash',
                'hashnonce' => false,
                'cookienonce' => false,
                'admin' => false,
              );
    protected $user = null;
    protected $id = null;

    /**
     * Constructor Method
     *
     * @access public
     * @return void
     */
    public function __construct($user_config = false) {
      if(is_array($user_config) && $user_config) {
        foreach($user_config as $key => $value) {
          if(array_key_exists($key, $this->settings)) {
            $this->settings[$key] = $value;
          }
        }
      }

      // Remove any non-alphanumeric characters from the realm. If that results
      // in an empty string, set a default realm.
      $this->realm = is_string($this->settings['realm'])
                  && ($realm = preg_replace('/[^a-zA-Z0-9]/', '', $this->settings['realm'])) != ''
                   ? $realm
                   : 'CodeIgniterUserLib';

      // Check that the table name and the id, username and password column names
      // are valid. These are the minimum requirements.
      $checks = array(
        $this->settings['table'],
        $this->settings['id'],
        $this->settings['username'],
        $this->settings['password']
      );

      foreach($checks as $check) {
        if(!is_string($check) || strlen($check) > 64 || strlen($check) < 1) {
          $this->id = false;
          return false;
        }
      }

      // If the realm cookie exists, validate it!
      if(isset($_COOKIE[$this->realm])) {
        $this->id = $this->validate();
      } else {
        $this->id = false;
      }
    }

    /**
     * Settings
     *
     * Provide settings in the controller to override the settings automatically
     * fetched from the userlib configuration file.
     *
     * @access public
     * @param array $user_config
     * @return void
     */
    public function settings($user_config) {
      if(is_array($user_config) && $user_config) {
        $this->__construct($user_config);
      }
    }

    /**
     * Fetch User Details from Database
     *
     * @access protected
     * @param string|integer $unique
     * @return boolean
     */
    protected function fetch($unique) {
      if(!(is_int($unique) || (is_string($unique) && preg_match('/^[a-zA-Z0-9]{1,64}$/', $unique)))) {
        return false;
      }
      $from = is_int($unique) ? $this->settings['id'] : $this->settings['username'];

      // Generate the SQL Query for grabbing the required data from the database.
      $dbq = "SELECT `{$this->settings['id']}` AS `id`, `{$this->settings['username']}` AS `name`, `{$this->settings['password']}` AS `hash`";
      if(is_string($this->settings['hashnonce'])) {
        $dbq .= ", `{$this->settings['hashnonce']}` AS `hashnonce`";
      }
      if(is_string($this->settings['cookienonce'])) {
        $dbq .= ", `{$this->settings['cookienonce']}` AS `cookienonce`";
      }
      if(is_string($this->settings['admin'])) {
        $dbq .= ", `{$this->settings['admin']}` AS `admin`";
      }
      $dbq .= " FROM `{$this->settings['table']}` WHERE `{$from}` = '{$unique}' LIMIT 1;";

      // Query the database and make sure we have a usable result.
      $result = $this->CI->db->query($dbq);
      if($result->num_rows() != 1) {
        return false;
      }
      $this->user = $result->row();

      // If the optional column are not present, fill them with default data.
      if(!isset($this->user->cookienonce)) {
        $this->user->cookienonce = '';
      }
      if(!isset($this->user->hashnonce)) {
        $this->user->hashnonce = '';
      }
      $this->user->admin = isset($this->user->admin) && $this->user->admin
                         ? true
                         : false;
      return true;
    }

    /**
     * Validate User Authenticity via Cookie
     *
     * @access protected
     * @return integer|false
     */
    private function validate() {
      if(!isset($_COOKIE[$this->realm])) {
        return false;
      }
      $cookie = $_COOKIE[$this->realm];
      if(!preg_match('/^([0-9]+)' . preg_quote('-') . '([a-f0-9]{40})$/', $cookie, $matches)) {
        return false;
      }
      $id = (int) $matches[1];
      $cookie = $matches[2];
      if(!$this->fetch($id)) {
        return false;
      }
      $sha1 = sha1($this->user->name . ':' . $this->user->hash . ':' . $this->user->cookienonce);
      return $cookie == $sha1 ? (int) $this->user->id : false;
    }

    /**
     * Hash a Password for Database Storage
     *
     * @access protected
     * @param string $password
     * @param string $nonce
     * @return string|false
     */
    protected function hash($password, $nonce = '') {
      if(!is_string($password) || !is_string($nonce)) {
        return false;
      }
      // Using an oddbit (hashing repeated various times) and the use of a
      // different nonce generated for each user will reduce the chance of a
      // cracker gaining your password from a rainbow (reverse hash lookup) table.
      // Although saying that, if someone has access to the database to find your
      // password hash, you're pretty much screwed anyway...
      $oddbit = preg_replace('/[^ace13579]/', '', $nonce);
      $oddbit = strlen($oddbit) % 2 ? 13 : 17;

      // State which hashing algorithm to use, after determining which algorithms
      // we want exist on this installation of PHP.
      $algos = hash_algos();
      switch(true) {
        case in_array('whirlpool', $algos):
          $algo = 'whirlpool';
          break;
        case in_array('sha512', $algos):
          $algo = 'sha512';
          break;
        case in_array('sha256', $algos):
          $algo = 'sha256';
          break;
        case in_array('sha1', $algos):
          $algo = 'sha1';
          break;
        default:
          $algo = 'md5';
          break;
      }
      for($i = 0; $i < $oddbit; $i++) {
        $password = hash_hmac($algo, $password, $nonce);
      }
      // Hash it one last time with the SHA1 algorithm to make sure the output is
      // 40 characters.
      return sha1($password);
    }

    /**
     * Set Cookie
     * Passing a string as the first parameter will set that cookie for two weeks.
     * If you don't pass a first parameter, it will unset the cookie and the user.
     *
     * @access protected
     * @param string|void $value
     * @return boolean
     */
    protected function cookie($value = false) {
      if(!headers_sent()) {
        if(!is_string($value)) {
          $this->id = null;
          $timeout = 94668480;
        }
        else {
          $timeout = time() + 1209600;
        }
        $value = (string) $value;

        // Top tip! If you are using a local domain (like "localhost" or
        // "devserver"), the host must be an empty string rather than the domain.
        // Tooks me HOURS to figure that out :@
        $host = strpos($_SERVER['SERVER_NAME'], '.') !== false
              ? '.' . $_SERVER['SERVER_NAME']
              : false;
        setcookie($this->realm, $value, $timeout, '/', $host);
        return true;
      }
      return false;
    }

    /**
     * Login
     * Check a user has submitted the correct password, and if they have, set the
     * cookie and flag them as logged in.
     *
     * @access public
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function login($username, $password) {
      // Check the function argument values, make sure they are usable.
      if(!is_string($username) || !is_string($password) || !preg_match('/^[a-zA-Z0-9]{1,64}$/', ($username = strtolower($username))) || !$this->fetch($username) || $this->logged_in()) {
        return false;
      }

      // Generate the password hash and check against the value returned from the database.
      $password = $this->hash($password, $this->user->hashnonce);
      if($password != $this->user->hash) {
        return false;
      }

      // If the user supplied the correct password, set a cookie so that we can
      // validate the user accross different HTTP requests.
      $cookie = $this->user->id . '-' . sha1($this->user->name . ':' . $this->user->hash . ':' . $this->user->cookienonce);
      $this->cookie($cookie);
      // Set the user ID for the rest of the script, and return true.
      $this->id = $this->user->id;
      return true;
    }

    /**
     * Logout
     *
     * @access public
     * @return boolean
     */
    public function logout() {
      $this->id = false;
      return $this->cookie();
    }

    /**
     * Create User
     *
     * Returns an array of key-value pairs ready to be inserted into the users
     * table. The format is: column name => field value.
     *
     * @access public
     * @param string $username
     * @param string $password
     * @param boolean $admin
     * @return array
     */
    public function create($username, $password, $admin = false) {
      if(!is_string($username) || !preg_match('/^[a-zA-Z0-9]{1,64}$/', $username) || !is_string($password)) {
        return false;
      }
      $details = array();
      $details[$this->settings['username']] = strtolower($username);
      $cookienonce = is_string($this->settings['cookienonce']) ? sha1(microtime()) : '';
      if($cookienonce) {
        $details[$this->settings['cookienonce']] = $cookienonce;
      }
      $hashnonce = is_string($this->settings['hashnonce']) ? sha1(microtime()) : '';
      if($hashnonce) {
        $details[$this->settings['hashnonce']] = $hashnonce;
      }
      $details[$this->settings['password']] = $this->hash($password, $hashnonce);
      if(is_string($this->settings['admin'])) {
        $details[$this->settings['admin']] = (boolean) $admin;
      }
      return $details;
    }

    /**
     * Logged In
     * Check wether a user is currently logged in.
     *
     * @access public
     * @return boolean
     */
    public function logged_in() {
      return (boolean) $this->id;
    }

    /**
     * Get Full Name
     *
     * @access public
     * @return string|false
     */
    public function fullname() {
      return $this->logged_in() ? $this->user->first . ' ' . $this->user->last : false;
    }

    /**
     * Get Job Title
     *
     * @access public
     * @return string|false
     */
    public function title() {
      return $this->logged_in() ? $this->user->title : false;
    }

    /**
     * Is User an Admin?
     *
     * @access public
     * @return boolean
     */
    public function admin() {
      return $this->logged_in() ? (boolean) $this->user->admin : false;
    }

    /**
     * What is the User's login name?
     *
     * @access public
     * @return string
     */
    public function username() {
      return $this->logged_in() ? $this->user->name : false;
    }

  }
