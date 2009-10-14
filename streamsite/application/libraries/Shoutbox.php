<?php 
class Shoutbox
{

    function Shoutbox($data)
    {
    
    }
    
    function index($data)
    {
        $this->show($data);
    }
    
    function show($data)
    {
        $data['shout_list'] = $this->shoutbox_model->get_shoutbox_list();
        $this->load->view('shoutbox/_shoutbox', $data);
    }
    
    function ajax_add_shout($data)
    {
        if ($this->input->is_ajax_request()) {
            $this->load->library('validation');
            $rules['shout_message'] = "trim|xss_clean|htmlspecialchars|required";
            $rules['shout_author_user_name'] = "trim|xss_clean|htmlspecialchars|required";
            $fields['shout_message'] = "Message";
            $fields['shout_author_user_name'] = "Name";
            
            $this->validation->set_rules($rules);
            $this->validation->set_fields($fields);
            
            if ($this->validation->run() == TRUE) {
                $this->shoutbox_model->add_shout();
            }
            $this->ajax_update_shoutbox($data);
        }
        else {
            $this->index();
        }
    }
    
    function ajax_update_shoutbox($data)
    {
        if ($this->input->is_ajax_request()) {
            //  $data = array();
            $data['shout_list'] = $this->shoutbox_model->get_shoutbox_list();
            $this->load->view('_shout_list', $data);
        }
        else {
            $this->index();
        }
    }
}


?>
