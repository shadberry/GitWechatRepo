<?php

class Test_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
		log_message('debug', 'Test_model __construct.');
		$this->load->database();
	}

	public function getAll() {
		log_message('debug', 'Test_model getAll.');
		$query = $this->db->get('wechat_msg');
		return $query->result_array();
			
	}

	public function insert() {
		log_message('debug', 'Test_model insert.');
		
		$shall = 'shall';
		$teddy = 'teddy';
		$hi = 'hi';
		
		$data = array(
		    'tousername' => $shall,
		    'fromusername' => $teddy,
		    'content' => $hi,
			'msgtype' => null
		);

		return $this->db->insert('wechat_msg', $data);
	}
}
