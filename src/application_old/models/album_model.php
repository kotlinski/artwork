<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:12
 * To change this template use File | Settings | File Templates.
 */

class Album_model extends CI_Model {

	public function __construct()
	{
		$this->load->database();
	}

	public function get_album($id = FALSE)
	{

		$this->db->order_by("year", "desc");
		if ($id === FALSE)
		{
			$query = $this->db->get('album');
			return $query->result_array();
		}

		$query = $this->db->get_where('album', array('id' => $id));
		return $query->row_array();
	}

	public function set_album()
	{

		$data = array(
			'title' => $this->input->post('title'),
			'year' => $this->input->post('year')
		);

		$this->db->insert('album', $data);
		return $this->db->insert_id();
	}

	public function hide_album($id)
	{
		$data = array(
			'show' => 0
		);

		$this->db->where('id', $id);
		$this->db->update('album', $data);
	}
	public function show_album($id)
	{
		$data = array(
			'show' => 1
		);

		$this->db->where('id', $id);
		$this->db->update('album', $data);
	}

	public function delete($id){
		$this->db->delete('album', array('id' => $id));
	}

	public function update($id, $title, $year){
		$data = array(
			'title' => $title,
			'year' => $year
		);
		$this->db->where('id', $id);
		$this->db->update('album', $data);
	}

	public function removeImagesFromAlbum($albumId){
		$this->db->where('album_id', $albumId);
		$this->db->delete('image_in_album');
	}

	public function putImageToAlbum($albumId, $imageId, $isCover){

		$data = array(
			'album_id' => $albumId,
			'image_id' => $imageId,
			'isCover' => $isCover
		);

		return $this->db->insert('image_in_album', $data);
	}

	public function get_album_images($id = FALSE)
	{

		$this->db->order_by("id", "desc");
		if ($id === FALSE)
		{
			$query = $this->db->get('image_in_album');
			return $query->result_array();
		}

		$query = $this->db->get_where('image_in_album', array('album_id' => $id));
		return $query->row_array();
	}
	/*
	 * transaction-id
	 */
	public function get_cover_images($albumId)
	{
		$this->db->order_by("id", "desc");
		$query = $this->db->get_where('image_in_album', array('isCover' => '1', 'album_id' => $albumId));
		return $query->row_array();
	}
	public function get_all_image_objects_from_album($id)
	{

		//$query = $this->db->get('select * from image_in_album, images where image_in_album.album_id == '.$id.' and images.id == image_in_album.image_id');
		$this->db->select('*');
		$this->db->from('image_in_album');
		$this->db->join('images', 'image_in_album.image_id = images.id');
		$this->db->where('image_in_album.album_id = '.$id);
		//$this->db->where('image_in_album.isCover = 0');
		$query = $this->db->get();

		return $query->result_array();
	}
	public function get_cover_image_from_album($id){
		$this->db->select('*');
		$this->db->from('image_in_album');
		$this->db->join('images', 'image_in_album.image_id = images.id');
		$this->db->where('image_in_album.album_id = '.$id);
		$this->db->where('image_in_album.isCover = 1');
		$query = $this->db->get();

		return $query->row_array();

	}
}










