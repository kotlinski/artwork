<?php

namespace App\Controllers;

use App\Models\User;

class Auth extends BaseController
{
  public function index()
  {
    return $this->renderNonPublicView('auth/login', [
      'title' => 'Login | Admin',
    ]);
  }
  
  public function login()
  {
    $session = session();
    var_dump($session->get('isLoggedIn'));
    var_dump($session->get('user_id'));
    $model = new User();
    $username = $this->request->getVar('username');
    $password = $this->request->getVar('password');
    
    $user = $model->where('username', $username)->first();
   
    if ($user) {
      $isValid = false;
      
      if (password_verify($password, $user['password'])) {
        $isValid = true;
      } elseif (hash('sha512', $password) === $user['password']) {
        
        $isValid = true;
        $model->update($user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
      }
      if ($isValid) {
        $session->set(['isLoggedIn' => true, 'user_id' => $user['id']]);
        return redirect()->to('/contact');
      }
      print("Didn't verify password.");
      die();
    }
    
    return redirect()->back()->with('error', 'Invalid login credentials.');
  }
  
  public function logout()
  {
    $referrer = $this->request->getServer('HTTP_REFERER') ?? '/';
    session()->destroy();
    return redirect()->to($referrer);
  }

}