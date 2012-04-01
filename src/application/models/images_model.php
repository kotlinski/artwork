<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:12
 * To change this template use File | Settings | File Templates.
 */

class Images_model extends CI_Model {

    public function __construct()
    {
        $this->load->database();
    }

    public function get_images()
    {
        $this->db->order_by("id", "desc");
        $query = $this->db->get('images');
        return $query->result();
    }

    public function insert_image($data = array())
    {
		$this->db->insert('images', $data);
		return $this->db->insert_id();
	}

	public function delete($id){
		$this->db->delete('images', array('id' => $id));
	}

	public function get_image($id){
		$query = $this->db->get_where('images', array('id' => $id));
		return $query->row();
	}
	public function update($id, $title){
		$data = array(
			'title' => $title
		);
		$this->db->where('id', $id);
		$this->db->update('images', $data);
	}
}