<?php

  if(!defined('BASEPATH')) {
    headers_sent() || header('HTTP/1.1 404 Not Found', true, 404);
    exit('No direct script access allowed.');
  }

  class Home extends Controller {

    public function Home() {
      parent::Controller();
      // Add the users library to the autoload, or set it here in each controller.
      $this->load->library('user');
    }

    public function index() {
      if($this->user->logged_in()) {
        // You're logged in!
        echo 'Welcome back, ' . $this->user->fullname() . '!';
      }
      else {
        redirect('home/login');
        exit;
      }
    }

    public function login() {
      $login = $this->user->login(
        $this->input->post('username'),
        $this->input->post('password')
      );
      if($login) {
        redirect('home/index');
        exit;
      }
      else {
        // Wrong username or password!
      }
    }

    public function logout() {
      $this->user->logout();
      redirect('home/login');
    }

  }