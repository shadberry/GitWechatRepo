<?php

class Test extends CI_Controller {
	
	function Test() {
		parent::__construct();
		$this->load->model("Test_model");
		$this->load->model('Wechat_model');
	}
	
	public function index() {
		echo $this->Test_model->getAll();
	}
	
	public function insert() {
$this->Test_model->insert();
		$msgData = array();
//		$this->Wechat_model->saveMsg($msgData);
		echo 'ok';
	}
}