<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:17
 * To change this template use File | Settings | File Templates.
 */

class Contact extends CI_Controller {
    public $session;
    public $contact_model;
    public $form_validation;
    
    public function __construct()
    {
        parent::__construct();
		$this->load->library('session');

		$this->load->model('contact_model');
    }

    public function index()
    {
        $text = $this->contact_model->get_contact();

        // Make emails clickable
        $text['text'] = preg_replace(
            '/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/',
            '<a href="mailto:$1">$1</a>',
            $text['text']
        );

        // Make phone numbers clickable (Swedish format)
        $text['text'] = preg_replace(
            '/(\b0\d{1,3}[- ]?\d{6,8}\b)/',
            '<a href="tel:$1">$1</a>',
            $text['text']
        );

        // Make URLs clickable
        $text['text'] = preg_replace(
            '/(https?:\/\/[^\s]+)/',
            '<a href="$1" target="_blank">$1</a>',
            $text['text']
        );

        $text['text'] = str_replace("???", '<span class="aboutHeader">', $text['text']);
        $text['text'] = str_replace("!!!", '</span>', nl2br($text['text']));

        $data['contact'] = $text;
        $data['contact_raw'] = $this->contact_model->get_contact();
        $data['title'] = 'Contact';
        $data['menu_item'] = 'contact';

        $this->load->view('templates/header', $data);
        $this->load->view('contact/index', $data);
        $this->load->view('templates/footer');
    }

    public function create()
    {
        $this->load->helper('form');
        $this->load->library('form_validation');

        $data['title'] = 'Contact';
        $data['menu_item'] = 'contact';

        $this->form_validation->set_rules('text', 'text', 'required');

        if ($this->form_validation->run() === FALSE)
        {
            $this->load->view('templates/header', $data);
            $this->load->view('pages/fail');
            $this->load->view('templates/footer');

        }
        else
        {
            $this->contact_model->set_contact();
            $this->load->view('templates/header', $data);
            $this->load->view('pages/success');
            $this->load->view('templates/footer');
        }
    }
}
