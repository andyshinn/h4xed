<?php

class Shoutbox_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	function online_users()
	{
	    $this->db->order_by('last_activity', 'desc');
	    return $this->db->get('sessions');
	}
	
	function messages($id = null, $limit = 20, $update = false)
	{
		$this->db->select('id, name, UNIX_TIMESTAMP(date) as timestamp, message');
		if (!empty($limit))
		{
			$this->db->limit($limit);
		}
		
		if (!empty($id))
		{
			$this->db->where('id >', $id);
		}

		$this->db->order_by('id', 'desc');
		$this->db->from('shouts');
		
		return $this->db->get();
	}
	
	function add_shout($name, $content)
	{

		if (! empty($name) & ! empty($content))
		{
			$this->db->set('name', $name);
			$this->db->set('message', $content);
			$this->db->set('date', "NOW()", FALSE);
			$this->db->set('time', time());
			$this->db->insert('shouts');
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function last_id()
	{
	    $this->db->select_max('id');
	    $id_query = $this->db->get('shouts');
	    $id_object = $id_query->row();
	    
	    return $id_object->id;
	}
}

?>
