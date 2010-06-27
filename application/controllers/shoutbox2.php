<?php

class Shoutbox2 extends Controller {
	
	function Shoutbox2() {

		parent::Controller();
		$this->load->library(array('template', 'typography'));
		$this->load->helper('cookie');
		$this->load->helper('when');
		$this->load->model('shoutbox_model', 'shoutbox');
		define('NOW', time());
		define('ONE_MINUTE', 60);
		define('ONE_HOUR', 3600);
		define('ONE_DAY', 86400);
		define('ONE_WEEK', ONE_DAY * 7);
		define('ONE_MONTH', ONE_WEEK * 4);
		define('ONE_YEAR', ONE_MONTH * 12);
	}
	
	function index() {

		$this->load->view('public/shoutbox/_shoutbox_list');
	}
	
	function test() {

		$this->template->add_js('assets/js/jquery-1.3.2.min.js');
		$this->template->add_js('assets/js/shoutbox.js');
		$this->template->write_view('hlinks', 'include/hlinks');
		$this->template->write_view('right', 'include/right');
		$this->template->write_view('right', 'public/shoutbox/_shoutbox_list');
		$this->template->parse_view('main', 'public/shoutbox/_shoutbox_list');
		$this->template->render();
	}
	
	function backend() {

		$store_num = 10;
		$display_num = 10;
		
		header("Content-type: text/xml");
		header("Cache-Control: no-cache");
		
		foreach($_POST as $key => $value) {
			${$key} = mysql_real_escape_string($value);
		}
		
		if (@$action == "postmsg") {
			
			$this->_setname($name);
			//			setcookie('hr_name', $name, time()+86500, '/', false);
			$current = time();
			$this->db->query("INSERT INTO shouts SET name='$name', message='$message', time='$current' ");
			//			$this->db->query("INSERT INTO shouts SET name='$name', message='$message'");
		//	$delid = mysql_insert_id () - $store_num;
		//	$this->db->query ( "DELETE FROM shouts WHERE id <= $delid" );
		}
		
		if (empty($time)) {
			$sql = "SELECT `id`, `name`, `email`, `message`, `website`, `date`, `time`, UNIX_TIMESTAMP(date) AS date2 FROM shouts ORDER BY id DESC LIMIT $display_num";
		}
		else {
			$sql = "SELECT `id`, `name`, `email`, `message`, `website`, `date`, `time`, UNIX_TIMESTAMP(date) AS date2 FROM shouts WHERE time > $time ORDER BY id ASC LIMIT $display_num";
		}
		
		$query = $this->db->query("$sql");
		
		if ($query->num_rows() == 0) {
			$status_code = 2;
		}
		else {
			$status_code = 1;
		}
		
		echo "<?xml version=\"1.0\"?>\n";
		echo "<response>\n";
		echo "\t<status>$status_code</status>\n";
		echo "\t<time>" . time() . "</time>\n";
		
		if ($query->num_rows() > 0) {
			
			$shouts_reverse = array_reverse($query->result_array());
			
			foreach($shouts_reverse as $shout) {
				$escmsg = htmlspecialchars(stripslashes($shout['message']));
				$message = $this->typography->auto_typography($shout['message']);
				echo "\t<message>\n";
				echo "\t\t<timestamp>" . when($shout['date2']) . "</timestamp>\n";
				echo "\t\t<author>" . $shout['name'] . "</author>\n";
				echo "\t\t<text>$message</text>\n";
				echo "\t</message>\n";
			}
		}
		echo "</response>";
	
	}
	
	function _setname($name) {

		if (get_cookie('hr_name') == false) {
			set_cookie('hr_name', $name);
		}
	}

}
?>
