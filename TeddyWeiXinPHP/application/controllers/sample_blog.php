<?php
class sample_Blog extends CI_Controller {

 function __construct()
 {
  parent::__construct();
 }

 public function index()
 {
  echo 'Hello World!';
 }

 public function comments()
 {
  echo 'See!';
 }
}
?>