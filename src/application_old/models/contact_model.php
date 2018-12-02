<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:12
 * To change this template use File | Settings | File Templates.
 */

class Contact_model extends CI_Model {

    public function __construct()
    {
        $this->load->database();
    }

    public function get_contact()
    {
        $this->db->order_by("id", "desc");
        $query = $this->db->get('contact');
        return $query->row_array();
    }

    public function set_contact()
    {
        $this->load->helper('url');

        $data = array(
            'text' => $this->input->post('text')
        );

        return $this->db->insert('contact', $data);
    }
}