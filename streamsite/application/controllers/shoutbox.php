<?php

class Shoutbox extends Controller {

    function Shoutbox()
    {
        parent::Controller();
        $this->load->model('Shoutbox_model');
    }

    function index()
    {
        $this->show();
    }

    function show()
    {
        $data['shout_list'] = $this->Shoutbox_model->get_shoutbox_list();
        $this->load->view('shoutbox', $data);
    }

    function ajax_add_shout()
    {
        if($this->input->is_ajax_request())
        {
            $this->load->library('validation');
            $rules['shout_message'] = "trim|xss_clean|htmlspecialchars|required";
            $rules['shout_author_user_name'] = "trim|xss_clean|htmlspecialchars|required";
            $fields['shout_message'] = "Message";
            $fields['shout_author_user_name'] = "Name";

            $this->validation->set_rules($rules);
            $this->validation->set_fields($fields);

            if ($this->validation->run() == TRUE)
            {
                $this->Shoutbox_model->add_shout();
            }

            $this->ajax_update_shoutbox();
        }
        else
        {
            $this->index();
        }
    }

    function ajax_update_shoutbox()
    {
        if($this->input->is_ajax_request())
        {
            $data = array();
            $data['shout_list'] = $this->Shoutbox_model->get_shoutbox_list();
            $this->load->view('shout_list', $data);
        }
        else
        {
            $this->index();
        }
    }

}

?>
 */