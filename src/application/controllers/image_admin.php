<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:17
 * To change this template use File | Settings | File Templates.
 */

class Image_admin extends CI_Controller {
    public $form_validation;
    public $session;
    public $images_model;

	function __construct()
	{
		parent::__construct();
		$this->load->library('session');

		$this->load->helper(array('form', 'url'));
		$this->load->model('Images_model', 'images_model');
	}


    public function index($data = null)
    {
		$data['error'] = '';
        $data['title'] = 'Image Admin';
        $data['menu_item'] = 'image_admin';
		$data['images'] = $this->images_model->get_images();
		$data['artwork_filters'] = $this->images_model->get_artwork_filters();

        $this->load->view('templates/header', $data);
        $this->load->view('image_admin/index', $data);
        $this->load->view('templates/footer');
    }

	function do_upload()
	{
		$config['upload_path'] = 'statics/img/upload/';
		$config['allowed_types'] = 'gif|jpg|png|jpeg|bmp';
		$config['encrypt_name']	= 'TRUE';
		$config['max_size']	= '250000';
		$config['max_width']  = '24000';
		$config['max_height']  = '24000';

		$this->load->library('upload', $config);


		$data['title'] = 'Image Admin';
		$data['menu_item'] = 'image_admin';

		if ( ! $this->upload->do_upload())
		{
			$data['error'] = $this->upload->display_errors();
		}
		else
		{
			$data['error'] = '';
			$upload_data = $this->upload->data();

			//$data['upload_data'] = $upload_data;

			$data_insert = array(
				'title' => $this->input->post('title'),
				'file_name' => $upload_data['file_name'],
				'artwork_filter' => $this->input->post('upload_filter'),
				'order' => $this->input->post('order')
			);

			$data['upload_data'] = array(
				'id' => $this->images_model->insert_image($data_insert),
				'title' => $this->input->post('title'),
				'file_name' => $upload_data['file_name'],
				'filter' => $this->input->post('upload_filter'),
				'order' => $this->input->post('order'),
			);

			$config = array(
				'source_image' => $upload_data['full_path'], //get original image
				'new_image' => 'statics/img/upload/medium/', //save as new image //need to create thumbs first
				'maintain_ratio' => TRUE,
				'image_library' => 'gd2',
				'quality' => 100,
				'master_dim' => 'width',
				'width' => 400,
				'height' => 200
			);

			$this->load->library('image_lib', $config); //load library
			$this->image_lib->resize(); //do whatever specified in config

			$config = array(
				'source_image' => $upload_data['full_path'], //get original image
				'new_image' => 'statics/img/upload/thumb/', //save as new image //need to create thumbs first
				'maintain_ratio' => true,
				'width' => 120,
				'height' => 70,
				'master_dim' => 'auto'

			);

			$this->image_lib->initialize($config);
			$this->image_lib->resize(); //do whatever specified in config


		}
		$data['images'] = $this->images_model->get_images();

		$this->index($data);
	}

	public function delete($id)
	{
		$image = $this->images_model->get_image($id);

		define('PUBPATH',str_replace(SELF,'',FCPATH)); // added

		//unlink(base_url('statics/img/upload/thumb/'.$image->file_name));
		unlink(PUBPATH."statics/img/upload/thumb/".$image->file_name);
		unlink(PUBPATH."statics/img/upload/".$image->file_name);
		unlink(PUBPATH."statics/img/upload/medium/".$image->file_name);
		$this->images_model->delete($id);
		echo "<br /><br /><br /><p>Your image have been removed. </p>";
	}
	public function update($id){
		$newTitle = $this->input->post('title');
		echo "new title: " . $this->input->post('title')."<br/>";
		$data = array(
			'title' => $newTitle
		);
		$this->images_model->update($id, $data);
		echo "<br /><br /><br /><p>Your image have been renamed. </p>";
	}
	public function setFilter($imgId){
		$filter_id = $this->input->post('filter_id');
		$data = array(
			'artwork_filter' => $filter_id
		);
		$this->images_model->update($imgId, $data);
		echo '<br /><br /><br /><p>Filter changed. <a href="'.base_url('image_admin').'">Continue</a> </p>';
	}
	public function setOrder($imgId){
		$order = $this->input->post('order');
		$data = array(
			'order' => $order
		);
		$this->images_model->update($imgId, $data);
		echo '<br /><br /><br /><p>Order saved. <a href="'.base_url('image_admin').'">Continue</a> </p>';
	}

}
