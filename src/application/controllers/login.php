<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:17
 * To change this template use File | Settings | File Templates.
 */

class Login extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        //$this->load->model('login_model');
		$this->load->library('session');
		$this->load->library('form_validation');

		$this->load->library('Simplelogin');
    }

    public function index()
    {
		$data['error'] = '';
        $data['title'] = 'Login';
        $data['menu_item'] = 'login';

        $this->load->view('templates/header', $data);
        $this->load->view('login/index', $data);
        $this->load->view('templates/footer');
    }

    function create()
    {

        //Load
        $this->load->helper('url');
		$this->load->library('form_validation');

        $this->form_validation->set_rules('create_password', 'Password', 'required|min_length[4]|max_length[32]|alpha_dash');

        if ($this->form_validation->run() == false) {
            redirect('/login/');
        } else {
            //Create account
            if($this->simplelogin->create($this->input->post('create_username'), $this->input->post('create_password'), isset($_POST['create_superuser']))) {
                redirect('/login/');
            } else {
                redirect('/login/');
            }
        }
    }

    function delete($user_id)
    {
        /* This method can delete your current user account
           * and you will still be logged in until you click
           * the logout button (then you won't be able to login again')
           */

        //Load
        $this->load->helper('url');

        if($this->simplelogin->delete($user_id)) {
            /*
               //If you are using OBSession you can uncomment these lines
               $flashdata = array('success' => true, 'success_text' => 'Deletion Successful!');
               $this->session->set_flashdata($flashdata);
               */
            redirect('/login/');
        } else {
            /*
               //If you are using OBSession you can uncomment these lines
               $flashdata = array('error' => true, 'error_text' => 'There was a problem creating the account.');
               $this->session->set_flashdata($flashdata);
               $this->session->set_flashdata($_POST);
               */
            redirect('/login/');
        }

    }

    function logmein()
    {

        //Load
        $this->load->helper('url');
        //$this->load->library('validation');

        $this->form_validation->set_rules('login_username', 'Username', 'required|min_length[4]|max_length[32]|alpha_dash');
        $this->form_validation->set_rules('login_password', 'Password', 'required|min_length[4]|max_length[32]|alpha_dash');

        if ($this->form_validation->run() == false) {
            echo "validation false"; die();
            /*
               //If you are using OBSession you can uncomment these lines
               $flashdata = array('error' => true, 'error_text' => $this->validation->error_string);
               $this->session->set_flashdata($flashdata);
               $this->session->set_flashdata($_POST);
               */
            redirect('/login/');
        } else {
            //Create account
            if($this->simplelogin->login($this->input->post('login_username'), $this->input->post('login_password'))) {
                // echo "loged in"; die();
                /*
                    //If you are using OBSession you can uncomment these lines
                    $flashdata = array('success' => true, 'success_text' => 'Login Successful!');
                    $this->session->set_flashdata($flashdata);
                    */

				print("Bacl to login: ");


				print("user data logged in: ");
				print_r($this->session->get_userdata(array('logged_in')));
				print_r($this->session->get_userdata(array('logged_in')));

				print_r($_SESSION);

				redirect('/login/');
            } else {
				print("fail"); die();
                //echo $this->input->post('login_username') . " " . $this->input->post('login_password'); die();
                /*
                    //If you are using OBSession you can uncomment these lines
                    $flashdata = array('error' => true, 'error_text' => 'There was a problem logging into the account.');
                    $this->session->set_flashdata($flashdata);
                    $this->session->set_flashdata($_POST);
                    */
                redirect('/login/');
            }
        }
    }

    function logout()
    {
        //Load
        $this->load->helper('url');

        //Logout
        $this->simplelogin->logout();

        redirect('/login/');
    }
}
