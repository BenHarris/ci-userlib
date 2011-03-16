<?php

  if(!defined('BASEPATH')) {
    headers_sent() || header('HTTP/1.1 404 Not Found', true, 404);
    exit('No direct script access allowed.');
  }

  class Admin extends CI_Controller {

    public function __construct() {
      parent::__construct();
      $this->load->library('userlib', false, 'user');
      // If no user has logged in, redirect straight to the login form.
      if(!$this->user->logged_in()) {
        redirect('user/login');
        exit;
      }
      // If the currently logged in user is not an administrator, redirect them
      // to a page that informs them of not having the correct permissions.
      if(!$this->user->admin()) {
        redirect('user/noperms');
        exit;
      }
    }

    public function index() {
      echo 'Welcome to the Administration Area!';
    }

  }
