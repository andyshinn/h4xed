<?php 
class Shout extends Controller
{

    function Shout()
    {
        parent::Controller();
        $this->load->model('shoutbox_model', 'shoutbox');
//        $this->load->library('shoutbox');
        $this->load->library('template');
        $this->load->helper('form');
    }
    
    function index()
    {
    
        $this->template->add_js('assets/js/prototype.js');
        
        $data['shout_list'] = $this->shoutbox->get_list();
        $data['shoutbox'] = $this->load->view('public/shoutbox/_shout_box', $data, TRUE);
        
//        $this->template->write_view('hlinks', 'include/hlinks');
//        $this->template->write_view('right', 'include/right');
//        $this->template->parse_view('main', 'public/shoutbox/test', $data);
//        $this->template->render();

		$this->load->view('public/shoutbox/test', $data);
    }
    
    function add($data = null)
    {
        $this->ajax_add_shout($data);
    }
    
//    function index($data)
//    {
//        $this->show($data);
//    }
    
    function show($data)
    {
        $data['shout_list'] = $this->shoutbox->get_shoutbox_list();
        $this->load->view('public/shoutbox/_shout_list', $data);
    }
    
    function ajax_add_shout($data)
    {
        if ($this->input->is_ajax_request())
        {
            $this->load->library('validation');
            $rules['message'] = "trim|xss_clean|htmlspecialchars|required";
            $rules['name'] = "trim|xss_clean|htmlspecialchars|required";
            $fields['message'] = "Message";
            $fields['name'] = "Name";
            
            $this->validation->set_rules($rules);
            $this->validation->set_fields($fields);
            
            if ($this->validation->run() == TRUE)
            {
                $this->shoutbox->add_shout();
            }
            $this->ajax_update_shoutbox($data);
        }
        else
        {
            $this->show($data);
        }
    }
    
    function ajax_update_shoutbox($data)
    {
        if ($this->input->is_ajax_request())
        {
            //  $data = array();
            $data['shout_list'] = $this->shoutbox->get_shoutbox_list();
            $this->load->view('public/shoutbox/_shout_list', $data);
        }
        else
        {
            $this->index();
        }
    }
    
}

?>
