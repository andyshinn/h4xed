<?php
class Shoutbox3 extends Controller
{
    function Shoutbox3 ()
    {
        parent::Controller();
        $this->load->library(array('template' , 'form_validation'));
        $this->load->helper(array('url' , 'form'));
        $this->load->model('shoutbox_model', 'shoutbox');
    }
    function index ()
    {
        redirect('radio/news');
    }
    function test ()
    {
        $this->template->add_js('assets/js/jquery-1.3.2.min.js');
        $this->template->add_js('assets/js/shoutbox3.js');
        $this->template->write_view('hlinks', 'include/hlinks');
        $this->template->write_view('right', 'include/right');
        $this->template->write_view('right', 'public/shoutbox/_shoutbox_list');
        $this->template->parse_view('main', 'public/shoutbox/_shoutbox_list');
        $this->template->render();
    }
    function add ()
    {
        $this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('message', 'Message', 'required');
        if ($this->form_validation->run() == FALSE) {
            $this->load->view('myform');
        } else {
            $this->load->view('formsuccess');
        }
    }
    function messages ($type = 'plaintext')
    {
        $messages = $this->shoutbox->messages();
        switch ($type) {
            case 'json':
                echo json_encode($messages->result());
                break;
            default:
                echo json_encode($messages->result());
                break;
        }
    }
    function backend ()
    {
        $store_num = 10;
        $display_num = 10;
        header("Content-type: text/xml");
        header("Cache-Control: no-cache");
        foreach ($_POST as $key => $value) {
            ${$key} = mysql_real_escape_string($value);
        }
        if (@$action == "postmsg") {
            $current = time();
            $this->db->query("INSERT INTO shouts SET name='$name', message='$message', time='$current' ");
            //			$this->db->query("INSERT INTO shouts SET name='$name', message='$message'");
            $delid = mysql_insert_id() - $store_num;
            $this->db->query("DELETE FROM shouts WHERE id <= $delid");
        }
        if (empty($time)) {
            $sql = "SELECT * FROM shouts ORDER BY id ASC LIMIT $display_num";
        } else {
            $sql = "SELECT * FROM shouts WHERE time > $time ORDER BY id ASC LIMIT $display_num";
        }
        $query = $this->db->query("$sql");
        if ($query->num_rows() == 0) {
            $status_code = 2;
        } else {
            $status_code = 1;
        }
        echo "<?xml version=\"1.0\"?>\n";
        echo "<response>\n";
        echo "\t<status>$status_code</status>\n";
        echo "\t<time>" . time() . "</time>\n";
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $escmsg = htmlspecialchars(stripslashes($row->message));
                echo "\t<message>\n";
                echo "\t\t<author>$row->name</author>\n";
                echo "\t\t<text>$escmsg</text>\n";
                echo "\t</message>\n";
            }
        }
        echo "</response>";
    }
}
?>
