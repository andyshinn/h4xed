<?php 
class Shoutbox_model extends Model
{
    function Shoutbox_model()
    {
        // Call the Model constructor
        parent::Model();
    }
    
    function get_shoutbox_list()
    {
        $this->db->limit(25, 0);
        $this->db->order_by("date", "desc");
        $this->db->from('shouts');
        $result = $this->db->get();
        
        if ($result->num_rows() > 0)
        {
            return $result->result_array();
        }
        else
        {
            return FALSE;
        }
    }
    
    function add_shout()
    {
        $this->db->set('name', $this->input->post('namee'));
        $this->db->set('message', $this->input->post('message'));
        $this->db->set('date', "NOW()", FALSE);
        $this->db->insert('shouts');
		
        return TRUE;
    }
}
?>
