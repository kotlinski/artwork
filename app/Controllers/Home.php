<?php

namespace App\Controllers;

class Home extends BaseController
{
  public function index(): string
  {
    // 1. Initialize the Model
    // $userModel = new \App\Models\User();
    
    // 2. Fetch all data (Like $this->db->get('users') in CI3)
    //$data['users'] = $this->userModel->findAll();
    
    // 3. Pass it to a view
    return view('user_list'/*, $data*/);
  }
}
