<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class TeddyIndex extends CI_Controller {

	/**
	 * helloword
	 */
	public function index()
	{
		log_message('debug', '@@@index called.');
		echo '56a5';
		$this->load->view('teddy_index');
	}
}
