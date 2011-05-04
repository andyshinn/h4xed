<?php
class Shoutbox extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('shoutbox_model', 'shoutbox');
        $this->load->library(array('template', 'parser', 'form_validation'));
        $this->load->helper(array('url', 'date', 'text', 'cookie', 'when', 'form'));
    }

    function ajax_online_users()
    {
        if ($this->input->is_ajax_request())
        {
            $my_name = $this->session->userdata('name');
            $timeout_idle = 30;
            $timeout_gone = 60;
            $online_users = array();
            $checked_users = array();
            $sessions = $this->shoutbox->online_users();
            foreach($sessions->result() as $session)
            {
                $user_data = array();
                $session_user_data = unserialize($session->user_data);
                if (((time() - $session_user_data['last_activity_shoutbox']) < $timeout_gone) & (! empty($session_user_data['name'])) & (! in_array($session_user_data['name'], $checked_users)))
                {
                    foreach(unserialize($session->user_data) as $user_data_key => $user_data_value)
                    {
                        $user_data[$user_data_key] = ($user_data_key == 'last_activity_shoutbox') ? when($user_data_value) : $user_data_value;
                    }
                    $time_passed = time() - $session_user_data['last_activity_shoutbox'];
                    $user_data['status'] = ($time_passed >= $timeout_idle) ? "idle" : "active";
                    $user_data['is_me'] = ($user_data['name'] == $my_name) ? true : false;
                    $user_data['seconds_passed'] = $time_passed;
                    $online_users[] = $user_data;
                    $checked_users[] = $user_data['name'];
                }
            }
            echo json_encode(array('users' => $online_users, 'count' => count($online_users)));
        }
        else
        {
            echo "But... you are not a browser, you are a person!";
        }
    }

    function history()
    {
        //        $this->template->add_js('assets/js/jquery-1.6.min.js');
        $this->template->add_css('assets/css/style.css');
        $this->template->add_css('assets/css/shoutbox.css');
        $shoutbox = $this->shoutbox->messages(NULL, NULL);
        $shoutbox_messages = $shoutbox->result();
        $view_data = array();
        $view_data['shouts'] = $shoutbox_messages;
        $this->template->write_view('main', 'public/shoutbox/shoutbox_history', $view_data);
        $this->template->write_view('head', 'include/head');
        $this->template->render();
    }

    function index()
    {
        echo "What are you doing here? :)";
    }

    function ajax_update()
    {
        if ($this->input->is_ajax_request())
        {
            $shouts_new = array();
            $last_id_new = $this->shoutbox->last_id();

//            $update = ($this->input->post('initial')) ? FALSE : TRUE;
            $shouts_query = $this->shoutbox->messages(null, 20);
            $shouts = $shouts_query->result_array();
            
            foreach($shouts as $shout)
            {
                $shouts_new[] = array_merge($shout, array('when' => when($shout['timestamp'])));
            }
            
            $row_count = $shouts_query->num_rows();
            $config = array(array('field' => 'id', 'label' => 'ID', 'rules' => 'trim|xss_clean|htmlspecialchars|integer'));
            $this->form_validation->set_rules($config);
            $this->form_validation->set_error_delimiters('', ':');
//            if ($this->form_validation->run() == FALSE)
//            {
//                echo json_encode(array('row_count' => $row_count, 'post_data' => $this->input->post(), 'errors' => explode(':', trim(rtrim(str_replace(array("\r\n", "\r", "\n"), '', validation_errors()), ':')))));
//            }
//            else
//            {
                $name = $this->input->cookie('h4xed_shoutname');
                $this->session->set_userdata('name', $name);
                $this->session->set_userdata('last_activity_shoutbox', time());
                echo json_encode(array('row_count' => $row_count, 'post_data' => $this->input->post(), 'lastid' => $last_id_new, 'shouts' => $shouts_new));
//            }
        }
        else
        {
            echo "Hmm... You don't look like an ajax request";
        }
    }

    function ajax_add()
    {
        $this->load->library('typography');
        if ($this->input->is_ajax_request())
        {
            $name = $this->input->post('name');
            $message = $this->typography->auto_typography(auto_link($this->input->post('message')));
            $config = array(array('field' => 'message', 'label' => 'Message', 'rules' => 'trim|xss_clean|htmlspecialchars|required|callback__check_defaults'), array('field' => 'name', 'label' => 'Name', 'rules' => 'trim|xss_clean|htmlspecialchars|required|callback__check_defaults'));
            $this->load->library('form_validation');
            $this->form_validation->set_rules($config);
            $this->form_validation->set_error_delimiters('', ':');
            $this->form_validation->set_message('required', '%s field required');
            if ($this->form_validation->run() == FALSE)
            {
                echo json_encode(array('status' => 'error', 'type' => 'validation', 'post_data' => $this->input->post(), 'errors' => explode(':', trim(rtrim(str_replace(array("\r\n", "\r", "\n"), '', validation_errors()), ':')))));
            }
            else
            {
                if ($this->shoutbox->add_shout($name, $message))
                {
                    $shouts_new = array();
                    $this->input->set_cookie("h4xed_shoutname", $name, "2678400");
                    $this->session->set_userdata('name', $name);
                    $this->session->set_userdata('last_activity_shoutbox', time());
                    $lastid = $this->input->post('lastid');
                    $id = $this->db->insert_id();
                    $shouts_query = $this->shoutbox->messages($lastid, 20, TRUE);
                    $shouts = $shouts_query->result_array();
                    $row_count = $shouts_query->num_rows();
                    $last_id_new = $this->shoutbox->last_id();
                    
                    foreach($shouts as $shout)
                    {
                        $shouts_new[] = array_merge($shout, array('when' => when($shout['timestamp'])));
                    }
                    echo json_encode(array('row_count' => $row_count, 'id' => $id, 'lastid' => $last_id_new, 'shouts' => $shouts_new));
                }
                else
                {
                    echo json_encode(array('row_count' => $row_count, 'type' => 'database', arrary('Database error')));
                }
            }
        }
        else
        {
            echo "This is a method to add shouts to the shoutbox. You shouldn't need to be here! Why don't you head over to " . anchor(site_url()) . " instead?";
        }
    }

    function backend()
    {
        $store_num = 10;
        $display_num = 20;
        //			header("Content-type: text/xml");
        //			header("Cache-Control: no-cache");
        foreach($_POST as $key => $value)
        {
            ${$key} = mysql_real_escape_string($value);
        }
        if ($action == "postmsg")
        {
            //			$this->_setname($name);
            $cookie = array('name' => 'h4xed_shoutname', 'value' => $name, 'expire' => '2678400', 'secure' => TRUE);
            $this->input->set_cookie("h4xed_shoutname", $name, "2678400");
            $current = time();
            $this->db->query("INSERT INTO shouts SET name='$name', message='$message', time='$current' ");
        }
        if (empty($time))
        {
            $sql = "SELECT `id`, `name`, `email`, `message`, `website`, `date`, `time`, UNIX_TIMESTAMP(date) AS date2 FROM shouts ORDER BY id DESC LIMIT $display_num";
        }
        else
        {
            $sql = "SELECT `id`, `name`, `email`, `message`, `website`, `date`, `time`, UNIX_TIMESTAMP(date) AS date2 FROM shouts WHERE time > $time ORDER BY id ASC LIMIT $display_num";
        }
        $query = $this->db->query("$sql");
        if ($query->num_rows() == 0)
        {
            $status_code = 2;
        }
        else
        {
            $status_code = 1;
        }
        echo "<?xml version=\"1.0\"?>\n";
        echo "<response>\n";
        echo "\t<status>$status_code</status>\n";
        echo "\t<time>" . time() . "</time>\n";
        if ($query->num_rows() > 0)
        {
            $shouts_reverse = array_reverse($query->result_array());
            foreach($shouts_reverse as $shout)
            {
                $escmsg = htmlspecialchars(stripslashes($shout['message']));
                echo "\t<message>\n";
                echo "\t\t<timestamp>" . when($shout['date2']) . "</timestamp>\n";
                echo "\t\t<author>" . $shout['name'] . "</author>\n";
                echo "\t\t<text>$escmsg</text>\n";
                echo "\t</message>\n";
            }
        }
        echo "</response>";
    }

    function _setname($name)
    {
        if (get_cookie('hr_name') == false)
        {
            set_cookie('hr_name', $name);
        }
    }

    function _check_defaults($data = FALSE)
    {
        $defaults = array('Your name', 'Your Name', 'Your Name!', 'Your name!', 'Your message', 'Your Message', 'Your Message!', 'Your message!');
        foreach($defaults as $default)
        {
            if ($data == $default)
            {
                $this->form_validation->set_message('_check_defaults', '%s field invalid');
                return FALSE;
            }
        }
        return TRUE;
    }
}
?>
