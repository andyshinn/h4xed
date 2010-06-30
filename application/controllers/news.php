<?php

class News extends Controller {
	
	var $view_data = array();
	
	function News() {

		parent::Controller();
		$this->load->model('news_model', 'news');
		$this->load->helper(array('form', 'url', 'text', 'date'));
		$this->load->library(array('form_validation', 'parser', 'table'));
	}
	
	function index() {

		$this->output->set_output('Nothing to see here!');
	}
	
	function listing() {

		$query = $this->news->listing();
		$news = $query->result();
		
		$this->table->set_heading('', 'ID', 'Visible', 'Title', 'Poster');
		
		foreach($news as $news_item) {
			$this->table->add_row(anchor("news/edit/$news_item->id", 'Edit'), $news_item->id, ($news_item->visible == 1) ? 'Yes' : 'No', $news_item->title, $news_item->poster);
		}
		
		$this->view_data['news_item_table'] = $this->table->generate();
		
		$this->load->view('news/listing', $this->view_data);
	}
	
	function add() {

		$view_data = array('standard_date' => standard_date());
		$this->form_validation->set_rules('title', 'Title', 'trim|required');
		$this->form_validation->set_rules('body', 'Body', 'required');
		$this->form_validation->set_rules('poster', 'Poster', 'trim|required');
		//        $this->form_validation->set_rules('timestamp', 'Date', 'trim|required');
		

		if ($this->form_validation->run() == FALSE) {
			$this->load->view('news/add', $view_data);
		}
		else {
			$post_items = array();
			$post_items['title'] = $this->input->post('title');
			$post_items['body'] = $this->input->post('body');
			$post_items['poster'] = $this->input->post('poster');
			
			$this->news->insert($post_items);
			$this->load->view('news/add_success');
		}
	}
	
	function edit($news_id = NULL) {

		if ($news_id) {
			$query = $this->news->listing(1, $news_id);
			$news = $query->row_array();
			
			foreach($news as $key => $value) {
				$this->view_data[$key] = $value;
			}
			
			$this->view_data['visible'] = ($news['visible'] == 1) ? true : false;
			

			$this->view_data['news_id'] = $news_id;	
			
			$this->form_validation->set_rules('title', 'Title', 'trim|required');
			$this->form_validation->set_rules('body', 'Body', 'required');
			$this->form_validation->set_rules('poster', 'Poster', 'trim|required');
			$this->form_validation->set_rules('visible', 'Visible', 'trim|required|is_numeric');
			//        $this->form_validation->set_rules('timestamp', 'Date', 'trim|required');
			

			if ($this->form_validation->run() == FALSE) {
				$this->load->view('news/edit', $this->view_data);
			}
			else {
				$post_array = array();
				
				$post_array['title'] = $this->input->post('title');
				$post_array['body'] = $this->input->post('body');
				$post_array['poster'] = $this->input->post('poster');
				$post_array['visible'] = $this->input->post('visible');
				$post_array['id'] = $news_id;
				
				$this->news->update($post_array);
				
				$this->load->view('news/edit_success');
			}
		}
		else {
			$this->output->set_output('Missing ID');
		}
	

	}

}

?>
