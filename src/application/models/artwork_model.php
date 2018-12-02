<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:12
 * To change this template use File | Settings | File Templates.
 */

class Artwork_model extends CI_Model {

	public function __construct()
	{
		$this->load->database();
	}

	public function get_news($id = FALSE)
	{
		$this->db->order_by("id", "desc");
		if ($id === FALSE)
		{
			$query = $this->db->get('artwork');
			return $query->result_array();
		}

		$query = $this->db->get_where('artwork', array('id' => $id));
		return $query->row_array();
	}

	public function get_artwork_filters($id = FALSE)
	{
		$this->db->order_by("id", "asc");
		if ($id === FALSE)
		{
			$query = $this->db->get('artwork_filters');
			return $query->result_array();
		}

		$query = $this->db->get_where('artwork_filters', array('id' => $id));
		return $query->row_array();
	}



	public function set_artwork()
	{
		$this->load->helper('url');

		$id = url_title($this->input->post('title'), 'dash', TRUE);

		$data = array(
			'title' => $this->input->post('title'),
			'id' => $id,
			'text' => $this->input->post('text')
		);

		return $this->db->insert('artwork', $data);
	}

	public function hide_artwork($id)
	{
		$data = array(
			'show' => 0
		);

		$this->db->where('id', $id);
		$this->db->update('artwork', $data);
	}
	public function show_artwork($id)
	{
		$data = array(
			'show' => 1
		);

		$this->db->where('id', $id);
		$this->db->update('artwork', $data);
	}

	public function delete($id){
		$this->db->delete('artwork', array('id' => $id));
	}

	public function update($id, $title, $text){
		$data = array(
			'title' => $title,
			'text' => $text
		);
		$this->db->where('id', $id);
		$this->db->update('artwork', $data);
	}
}
