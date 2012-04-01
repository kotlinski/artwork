<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 14:12
 * To change this template use File | Settings | File Templates.
 */

class Startpage_model extends CI_Model {

    public function __construct()
    {
        $this->load->database();
    }

    public function get_startpage()
    {
		$query = $this->db->query("SELECT * FROM startpage order by id desc LIMIT 1;");
        return $query->row_array();
    }

    public function set_startpage($data)
    {
        return $this->db->insert('startpage', $data);
    }

}