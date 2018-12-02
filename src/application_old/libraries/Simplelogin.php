<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Simon
 * Date: 2012-01-05
 * Time: 15:06
 * To change this template use File | Settings | File Templates.
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Simplelogin Class
 *
 * Makes authentication simple
 *
 * Simplelogin is released to the public domain
 * (use it however you want to)
 *
 * Simplelogin expects this database setup
 * (if you are not using this setup you may
 * need to do some tweaking)
 *

 *
 */
class Simplelogin
{
    var $CI;
    var $user_table = 'users';

    function Simplelogin()
    {

        // get_instance does not work well in PHP 4
        // you end up with two instances
        // of the CI object and missing data
        // when you call get_instance in the constructor
        //$this->CI =& get_instance();
    }

    /**
     * Create a user account
     *
     * @access    public
     * @param    string
     * @param    string
     * @param    bool
     * @return    bool
     */
    function create($user = '', $password = '', $superuser = false, $auto_login = true) {
        //Put here for PHP 4 users
        $this->CI =& get_instance();

        //Make sure account info was sent
        if($user == '' OR $password == '') {
            return false;
        }

        //Check against user table
        $this->CI->db->where('username', $user);
        $query = $this->CI->db->get_where($this->user_table);

        if ($query->num_rows() > 0) {
            //username already exists
            return false;

        } else {
            //Encrypt password
            $password = hash("sha512",$password);

            //Insert account into the database
            $data = array(
                'username' => $user,
                'password' => $password,
                'superuser' => $superuser
            );
            $this->CI->db->set($data);
            if(!$this->CI->db->insert($this->user_table)) {
                //There was a problem!
                return false;
            }
            $user_id = $this->CI->db->insert_id();

            //Automatically login to created account
            if($auto_login) {
                //Destroy old session
                $this->CI->session->unset_userdata();

                //Set session data
                $this->CI->session->set_userdata(array('id' => $user_id,'username' => $user, 'superuser' => $superuser));

                //Set logged_in to true
                $this->CI->session->set_userdata(array('online' => true));

            }

            //Login was successful
            return true;
        }

    }

    /**
     * Delete user
     *
     * @access    public
     * @param integer
     * @return    bool
     */
    function delete($user_id) {
        //Put here for PHP 4 users
        $this->CI =& get_instance();

        if(!is_numeric($user_id)) {
            //There was a problem
            return false;
        }

        if($this->CI->db->delete($this->user_table, array('id' => $user_id))) {
            //Database call was successful, user is deleted

            return true;
        } else {
            //There was a problem
            return false;
        }
    }


    /**
     * Login and sets session variables
     *
     * @access    public
     * @param    string
     * @param    string
     * @return    bool
     */
    function login($user = '', $password = '') {
        //Put here for PHP 4 users
        $this->CI =& get_instance();

        //Make sure login info was sent
        if($user == '' OR $password == '') {

            return false;
        }

        //echo $user . " " . print_r($this->CI->session->userdata('logged_in')) . " " . $user == print_r($this->CI->session->userdata('logged_in')); die();
        //Check if already logged in
        if($this->CI->session->userdata('username') == $user) {
            //User is already logged in.
            return false;
        }



        //Check against user table
        $this->CI->db->where('username', $user);
        $query = $this->CI->db->get_where($this->user_table);

        if ($query->num_rows() > 0) {

            $row = $query->row_array();

            //Check against password
            if(hash("sha512",$password) != $row['password']) {
                //echo $row['password']. ";    " . hash("sha512",$password) . ";    " .$password; die();
                return false;
            }

            //Destroy old session
            $this->CI->session->sess_destroy();


            //Remove the password field
            unset($row['password']);

            //Set session data
            $this->CI->session->set_userdata($row);

            //Set logged_in to true
            $this->CI->session->set_userdata(array('logged_in' => true));

            //Login was successful
            return true;
        } else {
            //No database result found
            return false;
        }

    }

    /**
     * Logout user
     *
     * @access    public
     * @return    void
     */
    function logout() {
        //Put here for PHP 4 users
        $this->CI =& get_instance();

        //Destroy session
        $this->CI->session->sess_destroy();
    }
}