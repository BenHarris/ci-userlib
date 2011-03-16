<?php

  if(!defined('BASEPATH')) {
    headers_sent() || header('HTTP/1.1 404 Not Found', true, 404);
    exit('No direct script access allowed.');
  }

  class Welcome extends CI_Controller {

    public function __construct() {
      parent::__construct();
      $this->load->library('userlib', false, 'user');
    }

    public function index() {
      $this->load->view(
        'welcome_message',
        array(
          'logged_in' => $this->user->logged_in()
        )
      );
    }

  }
