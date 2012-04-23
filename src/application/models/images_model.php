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
		$this->db->order_by("artwork_filters.id", "desc");
		$this->db->order_by("images.order", "asc");
		$this->db->order_by("images.id", "desc");
		$this->db->join('images', 'images.artwork_filter = artwork_filters.id');
		$query = $this->db->get('artwork_filters');
		return $query->result();
	}


	public function get_filtered_images($filter_id = null)
	{
		$this->db->order_by("artwork_filters.id", "desc");
		$this->db->order_by("images.order", "asc");
		if($filter_id){
			$this->db->where('artwork_filters.id', $filter_id);
		}
		$this->db->order_by("images.id", "desc");
		$this->db->join('images', 'images.artwork_filter = artwork_filters.id');
		$query = $this->db->get('artwork_filters');
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
	public function update($id, $data){
		$this->db->where('id', $id);
		$this->db->update('images', $data);
	}


	public function get_artwork_filters()
	{
		$this->db->order_by("id", "ASC");
		$query = $this->db->get('artwork_filters');
		return $query->result();
	}
}