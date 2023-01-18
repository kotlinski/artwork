<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:17
 * To change this template use File | Settings | File Templates.
 */

class Startpage extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
		$this->load->library('session');
		$this->load->helper('url');
		$this->load->library('form_validation');
		$this->load->model('startpage_model');
		$this->load->model('Images_model', 'images_model');
    }

    public function index()
    {
		$startpage = $this->startpage_model->get_startpage();
		//$data['startpage'] = $this->prepareText();
		$data['startpage'] = $startpage;
		$data['text'] = $startpage['text'];
		$data['image'] = $this->images_model->get_image($startpage['image_id']);
		$data['images'] = $this->images_model->get_images();
		$data['title'] = 'Startpage';
        $data['menu_item'] = 'startpage';
        $this->load->view('templates/header', $data);
        $this->load->view('startpage/index', $data);
        $this->load->view('templates/footer');
    }

	public function prepareText($data)
	{
		foreach ($data as $key => $startpage_item) {
			$raw_text = $startpage_item['text'];
			$after = nl2br($startpage_item['text']);
			$text = "";


			while (strpos($after, '???') !== FALSE && strpos($after, '!!!') !== FALSE) {
				$start_pos = strpos($after, '???');
				$end_pos = strpos($after, '!!!');
				if ($start_pos !== FALSE || $end_pos !== FALSE) {
					$needle = substr($after, $start_pos + 3, ($end_pos - 3) - $start_pos);
					$image = $this->images_model->get_image($needle);
					if ($image) {
						$image = '<img class="newsImg" src="' . base_url('/statics/img/upload/medium/' . $image->file_name) . '" />';
					} else {
						$image = "";
					}
					$before = substr($after, 0, $start_pos);
					$after = substr($after, ($end_pos + 3));
					$text .= $before . $image;
				}
			}

			$text .= $after;

			$data[$key]['text'] = $text;
			$data[$key]['text_raw'] = $raw_text;
		}
		return $data;
	}


    public function create()
    {


        $this->load->helper('form');
        $this->load->library('form_validation');

        $data['title'] = 'Create a startpage item';
        $data['menu_item'] = 'startpage';

        $this->form_validation->set_rules('text', 'text', 'required');

		
        if ($this->form_validation->run() === FALSE)
        {

        }
        else
        {
			$data = array(
				'text' => $this->input->post('text'),
				'image_id' => $this->input->post('cover')
			);


            $this->startpage_model->set_startpage($data);
        }
		$this->index();

    }

}
