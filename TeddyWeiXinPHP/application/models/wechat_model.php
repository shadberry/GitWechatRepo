<?php
class Wechat_model extends CI_Model {
	
	function __construct()
    {
        parent::__construct();
		log_message('debug', 'Wechat_model __construct.');
		$this->load->database();
    }
	
	public function saveMsg($msgData) {
		log_message('debug', 'saveMsg Called');

		return $this->db->insert('wechat_msg', $msgData);
	}
	
}

?>