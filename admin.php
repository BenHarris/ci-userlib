<?php

  if(!defined('BASEPATH')) {
    headers_sent() || header('HTTP/1.1 404 Not Found', true, 404);
    exit('No direct script access allowed.');
  }

  class Admin extends Controller {

    public function Admin() {
      parent::Controller();
      // Add the users library to the autoload, or set it here in each controller.
      $this->load->library('user');
    }

    public function index() {
      if($this->user->admin()) {
        // You're an admin!
        echo 'Welcome to the admin area, ' . $this->user->fullname() . '!';
      }
      else {
        redirect('home/index');
        exit;
      }
    }

  }