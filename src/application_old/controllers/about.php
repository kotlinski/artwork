<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:17
 * To change this template use File | Settings | File Templates.
 */

class About extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('about_model');
    }

    public function index()
    {
        $text = $this->about_model->get_about();


        $text['text'] = str_replace("???", '<span class="aboutHeader">', $text['text']);
        $text['text'] = str_replace("!!!", '</span>',nl2br($text['text']));

        $data['about'] = $text;
        $data['about_raw'] = $this->about_model->get_about();
        $data['title'] = 'About';
        $data['menu_item'] = 'about';

        $this->load->view('templates/header', $data);
        $this->load->view('about/index', $data);
        $this->load->view('templates/footer');
    }

    public function create()
    {
        $this->load->helper('form');
        $this->load->library('form_validation');

        $data['title'] = 'About';
        $data['menu_item'] = 'about';

        $this->form_validation->set_rules('text', 'text', 'required|xss_clean');

        if ($this->form_validation->run() === FALSE)
        {
            $this->load->view('templates/header', $data);
            $this->load->view('pages/fail');
            $this->load->view('templates/footer');

        }
        else
        {
            $this->about_model->set_about();
            $this->load->view('templates/header', $data);
            $this->load->view('pages/success');
            $this->load->view('templates/footer');
        }
    }
}