<?php

  if(!defined('BASEPATH')) {
    headers_sent() || header('HTTP/1.1 404 Not Found', true, 404);
    exit('No direct script access allowed.');
  }

  class User extends CI_Controller {

    public function __construct() {
      // Load the user library so we can detect when and which user is
      // logged in.
      parent::__construct();
      $this->load->library('userlib', false, 'user');
      $this->load->library('template');
    }

    /**
     * User Homepage.
     */
    public function index() {
      // If no user is logged in,
      if(!$this->user->logged_in()) {
        redirect('user/login');
        exit;
      }
      echo 'Welcome back, ' . anchor('user/profile/' . $this->user->username(), $this->user->fullname()) . '!';
      if($this->user->admin()) {
        echo ' ' . anchor('admin', 'Administration Area');
      }
    }

    /**
     * User Profile Page.
     */
    public function profile() {
      $this->template->create(
        'shell',
        'content' => 'userprofile',
      );
      $this->template->section('content')->add();
      $this->template->load('shell');
    }

    /**
     * Login Page
     */
    public function login() {
      $incorrect = false;
      // If form has been submitted, validate the user's credentials.
      if($this->input->post('action') == 'validate') {
        // If the credentials are correct, redirect to the user page.
        if($this->user->login($this->input->post('username'), $this->input->post('password'))) {
          redirect('user');
          exit;
        }
        // If the credentials are incorrect, flag this up.
        else {
          $incorrect = true;
        }
      }
      // Display the login form.
      $this->template->create(array(
        'shell',
        'content' => 'loginform',
      ));
      $this->template->section('content')->add('incorrect', $incorrect);
      $this->template->load('shell');
    }

    /**
     * Logout Page
     */
    public function logout() {
      // Log the user out.
      $this->user->logout();
      // Redirect to the homepage.
      redirect('');
      // Terminate the script.
      exit;
    }

    /**
     * User Registration page
     */
    public function signup() {
      // Here you may show a form to grab details off a potential user, ready to
      // pass to $this->user->create(); ... For example:
      /*
        $this->template->create(array(
          'shell',
          'content' => 'signupform',
        ));
        $this->template->load('shell');
      */
    }

    /**
     * "No Access Allowed to Admin Area" Page.
     */
    public function noperms() {
      $this->template->create(array(
        'shell',
        'content' => 'noperms',
      ));
      $this->template->load('shell');
    }

  }
