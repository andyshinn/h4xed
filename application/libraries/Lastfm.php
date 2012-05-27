<?php
	
	class Lastfm {
		
		private $_obj = NULL;
		private $_base_url = 'http://ws.audioscrobbler.com/2.0/';
		private $_session_key = 'lastfm_token';
		
		private static $_curl_opts = array(
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_TIMEOUT        => 60,
			CURLOPT_USERAGENT      => 'codeigniter-lastfm-2.0'
		);
		
		private $_api_key = '';
		private $_api_secret = '';
		
		function __construct()
		{
			$this->_obj =& get_instance();
			
			$this->_obj->load->config('lastfm');
			$this->_obj->load->helper('url');
			
			$this->_api_key = $this->config->item('lastfm_api_key');
			$this->_api_secret = $this->config->item('lastfm_api_secret');
		}
		
		public function call($method, $data)
		{
			$data['method'] = $method;
			$data['format'] = 'json';
			$data['api_key'] = $this->_api_key;
			
			$ch 	= curl_init();
			$opts 	= self::$_curl_opts;
			
			$opts[CURLOPT_URL] = $this->_base_url.'?'.http_build_query($data, null, '&');;
			 
			curl_setopt_array($ch, $opts);
			
			$result = curl_exec($ch);
			curl_close($ch);
			
			return json_decode($result);
		}
	}