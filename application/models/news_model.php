<?php
class News_model extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    function insert($post_array)
    {
        if (is_array($post_array))
        {
            foreach ($post_array as $key=>$value)
            {
                $this->db->set($key, $value);
            }

            $this->db->insert('news');
        }
    }

    function update($post_array)
    {
        if (is_array($post_array))
        {
            $this->db->set('poster', $post_array['poster']);
            $this->db->set('title', $post_array['title']);
            $this->db->set('body', $post_array['body']);
            $this->db->set('visible', $post_array['visible']);
			
            $this->db->where('id', $post_array['id']);
			
            $this->db->update('news');
        }
    }

    function listing($news_limit = 5, $news_id = NULL)
    {
        if (is_numeric($news_id))
        {
            $this->db->where('id', $news_id);
        }

        $this->db->select("id, title, body, poster, visible, timestamp, DATE_FORMAT(timestamp, '%M %D, %Y') AS date", false);
		$this->db->order_by('id', 'DESC');
        $this->db->limit($news_limit);

        return $this->db->get('news');

    }
}
?>
