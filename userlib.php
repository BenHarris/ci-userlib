<?php

/**
 * CodeIgniter Simple User Authentication
 *
 * @author    Alexander Baldwin <http://github.com/mynameiszanders>
 * @link      http://github.com/mynameiszanders/matts_homework_project
 * @license   MIT/X11 <http://j.mp/mit-license>
 */

  if(!defined('BASEPATH')) {
    headers_sent() || header('HTTP/1.1 404 Not Found', true, 404);
    exit('No direct script access allowed.');
  }

  class Userlib {

    protected $realm = 'Your Application Name',
              $user = null,
              $id = null,
              $CI;

    /**
     * Constructor Method
     *
     * @access public
     * @return void
     */
    public function __construct() {
      $this->CI =& get_instance();
      $this->CI->load->database();
      // Remove any non-alphanumeric characters from the realm.
      $this->realm = preg_replace('/[^a-zA-Z0-9]/', '', $this->realm);
      if(isset($_COOKIE[$this->realm])) {
        $this->id = $this->validate();
      }
      else {
        $this->id = false;
      }
    }

    public static function tableSQL() {
      return "CREATE TABLE `users` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                `hash` CHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                `hashnonce` CHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                `cookienonce` CHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                `first` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
                `last` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
                `title` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
                `admin` BIT(1) NOT NULL,
                UNIQUE (`name`)
              ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;";
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
      $from = is_int($unique) ? 'id' : 'name';
      $dbq = "SELECT * FROM users WHERE `{$from}` = '{$unique}' LIMIT 1;";
      $result = $this->CI->db->query($dbq);
      if($result->num_rows() != 1) {
        return false;
      }
      $this->user = $result->row();
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
     * @access public
     * @param string $username
     * @param string $password
     * @param string $first
     * @param string $last
     * @param string $title
     * @param boolean $admin
     * @return boolean
     */
    public function create($username, $password, $first = '', $last = '', $title = '', $admin = false) {
      if(!is_string($username) || !preg_match('/^[a-zA-Z0-9]{1,64}$/', $username) || !is_string($password)) {
        return false;
      }
      $username = strtolower($username);
      $cookienonce = sha1(microtime());
      $hashnonce = sha1(microtime());
      $password = $this->hash($password, $hashnonce);
      $admin = $admin ? 1 : 0;
      // Urgh! We're having to use a depreciated function! But we can't use the
      // alternative as we don't have access to the connection handler in this
      // class.
      $first = is_string($first) ? mysql_escape_string(substr($first, 0, 64)) : '';
      $last = is_string($last) ? mysql_escape_string(substr($last, 0, 64)) : '';
      $title = is_string($title) ? mysql_escape_string(substr($title, 0, 64)) : '';
      $dbq = "INSERT INTO `users` (`name`, `hash`, `hashnonce`, `cookienonce`, `first`, `last`, `title`, `admin`) VALUES ('{$username}', '{$password}', '{$hashnonce}', '{$cookienonce}', '{$first}', '{$last}', '{$title}', b'{$admin}');";
      $result = $this->CI->db->query($dbq);
      return (boolean) $result;
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
