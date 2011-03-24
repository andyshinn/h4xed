<?php

class Shoutbox_model extends Model
{
    function Shoutbox_model()
    {
        parent::Model();
    }
    
    function messages($limit = 20)
    {
        $this->db->select('id, name, email, time, message');
	if ($limit != 0) {
        $this->db->limit($limit); }
        $this->db->order_by('date', 'desc');
        $this->db->from('shouts');
        
        return $this->db->get();
    }
    
    function add_shout()
    {
        $this->db->set('name', $this->input->post('namee'));
        $this->db->set('message', $this->input->post('message'));
        $this->db->set('date', "NOW()", FALSE);
        $this->db->insert('shouts');
		
        return TRUE; // needed?
    }
}

?>
