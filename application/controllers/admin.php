<?php
class Admin extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->library(array('table', 'template', 'sam', 'form_validation', 'xml', 'pagination', 'ion_auth'));
		$this->load->model('playlist_model', 'playlist');
		$this->load->model('stream_model', 'stream');
		$this->load->helper(array('url', 'form', 'string', 'date'));
		$this->template->add_js('assets/js/jquery-1.6.min.js');
		$this->template->add_css('assets/css/style.css');
		$this->template->add_js('assets/js/general.js');
    }
    
function index()
	{
		if (!$this->ion_auth->logged_in())
		{
			//redirect them to the login page
			redirect('auth/login', 'refresh');
		}
		elseif (!$this->ion_auth->is_admin())
		{
			//redirect them to the home page because they must be an administrator to view this
			redirect($this->config->item('base_url'), 'refresh');
		}
		else
		{
			//set the flash data error message if there is one
			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

			//list the users
			$this->data['users'] = $this->ion_auth->get_users_array();
			$this->load->view('auth/index', $this->data);
		}
	}
}