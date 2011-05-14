<?php
class Admin extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->library(array('template', 'form_validation', 'ion_auth', 'encrypt'));
        $this->load->helper(array('url', 'form', 'string', 'date', 'breadcrumb'));
        $this->template->set_master_template('template_admin');
    }

    function index()
    {
    if (! $this->ion_auth->logged_in())
        {
            //redirect them to the login page
            redirect('auth/login/' . urlencode($this->uri->uri_string(), 'refresh'));
        }
        elseif (! $this->ion_auth->is_admin())
        {
            //redirect them to the home page because they must be an administrator to view this
            redirect($this->config->item('base_url'), 'refresh');
        }
        else
        {
            //set the flash data error message if there is one
            $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
            
            //list the users
            $user = $this->ion_auth->get_user();
            $this->template->write('user', $user->first_name);
            $this->template->render();
        }
        
    }

    function users()
    {
        if (! $this->ion_auth->logged_in())
        {
            redirect('auth/login/' . urlencode($this->uri->uri_string()), 'refresh');
        }
        elseif (! $this->ion_auth->is_admin())
        {
            echo "Sorry, you do not have permissions to view this page.";
        }
        else
        {
            $user = $this->ion_auth->get_user();
            $this->template->write('user', $user->first_name);
            $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
            $this->data['users'] = $this->ion_auth->get_users_array();
            $this->template->write_view('main', 'auth/index', $this->data);
            $this->template->render();
        }
    }
}