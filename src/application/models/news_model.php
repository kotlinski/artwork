<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:12
 * To change this template use File | Settings | File Templates.
 */

class News_model extends CI_Model {

    public function __construct()
    {
        $this->load->database();
    }

    public function get_news($slug = FALSE)
    {

        $this->db->order_by("id", "desc");
        if ($slug === FALSE)
        {
            $query = $this->db->get('news');
            return $query->result_array();
        }

        $query = $this->db->get_where('news', array('slug' => $slug));
        return $query->row_array();
    }

    public function set_news()
    {
        $this->load->helper('url');

        $slug = url_title($this->input->post('title'), 'dash', TRUE);

        $data = array(
            'title' => $this->input->post('title'),
            'slug' => $slug,
            'text' => $this->input->post('text')
        );

        return $this->db->insert('news', $data);
    }

    public function hide_news($id)
    {
        $data = array(
            'show' => 0
        );

        $this->db->where('id', $id);
        $this->db->update('news', $data);
    }
    public function show_news($id)
    {
        $data = array(
            'show' => 1
        );

        $this->db->where('id', $id);
        $this->db->update('news', $data);
    }

	public function delete($id){
		$this->db->delete('news', array('id' => $id));
	}

	public function update($id, $title, $text){
		$data = array(
			'title' => $title,
			'text' => $text
		);
		$this->db->where('id', $id);
		$this->db->update('news', $data);
	}
}