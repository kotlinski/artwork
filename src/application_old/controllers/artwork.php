<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:17
 * To change this template use File | Settings | File Templates.
 */

class Artwork extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('artwork_model');
    }

    public function index()
    {
        $data['artwork'] = $this->artwork_model->get_artwork();
        $data['title'] = 'Artwork';
        $data['menu_item'] = 'artwork';

        $this->load->view('templates/header', $data);
        $this->load->view('artwork/index', $data);
        $this->load->view('templates/footer');
    }

    public function view($slug)
    {
        $data['artwork_item'] = $this->artwork_model->get_artwork($slug);

        $data['title'] = 'Artwork';
        $data['menu_item'] = 'artwork';

        if (empty($data['artwork_item']))
        {
            show_404();
        }

        $data['title'] = $data['artwork_item']['title'];

        $this->load->view('templates/header', $data);
        $this->load->view('artwork/view', $data);
        $this->load->view('templates/footer');
    }

    public function create()
    {
        $this->load->helper('form');
        $this->load->library('form_validation');

        $data['title'] = 'Create a artwork item';
        $data['menu_item'] = 'artwork';

        $this->form_validation->set_rules('title', 'Title', 'required');
        $this->form_validation->set_rules('text', 'text', 'required');

        if ($this->form_validation->run() === FALSE)
        {
            $this->load->view('templates/header', $data);
            $this->load->view('pages/create');
            $this->load->view('templates/footer');

        }
        else
        {
            $this->artwork_model->set_artwork();
            $this->load->view('templates/header', $data);
            $this->load->view('pages/success');
            $this->load->view('templates/footer');
        }
    }
    public function hide($id)
    {
        $this->artwork_model->hide_artwork($id);

        $data['artwork'] = $this->artwork_model->get_artwork();
        $data['title'] = 'Artwork';
        $data['menu_item'] = 'artwork';

        $this->load->view('templates/header', $data);
        $this->load->view('artwork/index', $data);
        $this->load->view('templates/footer');
    }
    public function show($id)
    {
        $this->artwork_model->show_artwork($id);
        $data['artwork'] = $this->artwork_model->get_artwork();
        $data['title'] = 'Artwork';
        $data['menu_item'] = 'artwork';

        $this->load->view('templates/header', $data);
        $this->load->view('artwork/index', $data);
        $this->load->view('templates/footer');
    }
}