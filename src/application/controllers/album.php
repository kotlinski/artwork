<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:17
 * To change this template use File | Settings | File Templates.
 */

class Album extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
		$this->load->library('session');

		$this->load->model('album_model');
		$this->load->model('Images_model', 'images_model');
		$this->load->model('Artwork_model', 'artwork_model');
    }

    public function index($selected_filter = 2)
    {

		$data['images'] = $this->images_model->get_filtered_images($selected_filter);

        $data['menu_item'] = 'album';
		$submenu = $this->artwork_model->get_artwork_filters();
		unset($submenu[0]);
		$data['submenu'] = $submenu;
		$data['selected_filter'] = $selected_filter;
		$sel= $this->artwork_model->get_artwork_filters($selected_filter);
		$data['title'] = $sel['name'];

        $this->load->view('templates/header', $data);
        $this->load->view('album/index', $data);
        $this->load->view('templates/footer');
    }

    public function view($slug, $image_id)
    {
        $data['album_item'] = $this->album_model->get_album($slug);

		if($data['album_item']){
			//$data['cover_image'] = $this->album_model->get_cover_image_from_album($slug);
			$data['images'] = $this->album_model->get_all_image_objects_from_album($slug);
			$lastHalf = array();
			$specialPic = array();
			$firstHalf = array();
			foreach($data['images'] as $image){
				if($image['id'] == $image_id){
					array_push($specialPic, $image);
				}
				else {
					if(count($specialPic) > 0){
						array_push($lastHalf, $image);
					} else {
						array_push($firstHalf, $image);
					}
				}
			}
			$data['images'] = array_merge($lastHalf, $firstHalf);
			$data['images'] = array_merge($data['images'],$specialPic);
/*			var_dump($lastHalf);
			var_dump($firstHalf);
			var_dump($data['images']);
			die();*/
			$data['title'] = 'Album';
			$data['menu_item'] = 'album';

			if (empty($data['album_item']))
			{
				show_404();
			}

			$data['title'] = $data['album_item']['title'];

			$this->load->view('templates/header', $data);
			$this->load->view('album/view', $data);
			$this->load->view('templates/footer');
		} else {
			$this->index();
		}
    }

	public function listView($id)
	{
		$data['album_item'] = $this->album_model->get_album($id);

		if($data['album_item']){
			$data['images'] = $this->album_model->get_all_image_objects_from_album($id);
			$data['menu_item'] = 'album';

			if (empty($data['album_item']))
			{
				show_404();
			}

			$data['title'] = $data['album_item']['title'];

			$this->load->view('templates/header', $data);
			$this->load->view('album/listView', $data);
			$this->load->view('templates/footer');
		} else {
			$this->index();
		}
	}

    public function create()
    {
        $this->load->helper('form');
        $this->load->library('form_validation');

        $data['title'] = 'Create a album item';
        $data['menu_item'] = 'album';

        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('year', 'year', 'required');

        if ($this->form_validation->run() === FALSE)
        {
            $this->load->view('templates/header', $data);
            $this->load->view('pages/create');
            $this->load->view('templates/footer');
        }
        else
        {
			$albumId = $this->album_model->set_album();

			$this->album_model->removeImagesFromAlbum($albumId);

			$inAlbum = $this->input->post('inAlbum');
			$cover 	= $this->input->post('cover');

			if ( $inAlbum ) {
				if(!$cover){
					$cover = $inAlbum[0];
				}
				for ($i=0; $i<count($inAlbum); $i++) {
					$isCover = 0;
					if($cover ==  $inAlbum[$i]){
						$isCover = 1;
					}

					$this->album_model->putImageToAlbum($albumId, $inAlbum[$i], $isCover);
				}
			}


            $this->load->view('templates/header', $data);
            $this->load->view('pages/success');
            $this->load->view('templates/footer');
        }
    }
    public function hide($id)
    {
        $this->album_model->hide_album($id);
		$this->index();
    }
    public function show($id)
    {
        $this->album_model->show_album($id);
		$this->index();
    }

	public function create_album()
	{

		$this->load->model('Images_model', 'images_model');

		$data['title'] = 'Create album';
		$data['menu_item'] = 'album';

		$data['images'] = $this->images_model->get_images();


		$this->load->view('templates/header', $data);
		$this->load->view('album/create_album', $data);
		$this->load->view('templates/footer');
	}

	public function edit_album($id)
	{
		$data['album_item'] = $this->album_model->get_album($id);

		if($data['album_item']){

			$data['cover_image'] = $this->album_model->get_cover_image_from_album($id);
			$data['images'] = $this->images_model->get_images();
			$data['album_images'] = $this->album_model->get_all_image_objects_from_album($id);
			$data['title'] = 'Album';
			$data['menu_item'] = 'album';

			if (empty($data['album_item']))
			{
				show_404();
			}

			$data['title'] = $data['album_item']['title'];

			$this->load->view('templates/header', $data);
			$this->load->view('album/edit_album', $data);
			$this->load->view('templates/footer');
		} else {
			$this->index();
		}
	}

	public function update_album(){

		$this->load->helper('form');
		$this->load->library('form_validation');

		$id = $this->input->post('id');
		$title = $this->input->post('title');
		$year = $this->input->post('year');


		$this->form_validation->set_rules('title', 'Title', 'required');
		$this->form_validation->set_rules('year', 'year', 'required');


		if ($this->form_validation->run() === FALSE)
		{

		}
		else
		{
			//update album
			$this->album_model->update($id, $title, $year);
			//remove old images in album
			$this->album_model->removeImagesFromAlbum($id);

			$inAlbum = $this->input->post('inAlbum');
			$cover 	= $this->input->post('cover');

			if ( $inAlbum ) {
				if(!$cover){
					$cover = $inAlbum[0];
				}
				if(!in_array($cover, $inAlbum)){
					array_push($inAlbum, $cover);
				}
				for ($i=0; $i<count($inAlbum); $i++) {
					$isCover = 0;
					if($cover ==  $inAlbum[$i]){
						$isCover = 1;
					}
					$this->album_model->putImageToAlbum($id, $inAlbum[$i], $isCover);
				}
			}

		}

		redirect(base_url('album/edit_album/'.$id));

	}
}
