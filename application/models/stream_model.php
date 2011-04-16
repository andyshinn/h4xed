<?php
class Stream_model extends CI_Model {
	
	function __construct() {
		parent::__construct();
	}
	
	function last_hours($hours = '24') {
		
		$this->db->select('DATE_FORMAT(date_played, \'%l:00+%p\') AS hour', false);
		$this->db->select_max('listeners');
		$this->db->from('historylist');
		$this->db->where('DATE_SUB(NOW(),INTERVAL ' . ($hours - 1) . ' HOUR) <= date_played');
		$this->db->group_by('DATE_FORMAT(date_played, \'%d%H\')');
		$this->db->order_by('DATE_FORMAT(date_played, \'%d%H\')');
		$query = $this->db->get();
		
		return $query;
	}
}
?>