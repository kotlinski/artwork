<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('users', 'Home::index');
$routes->get('user/(:num)', 'Home::profile/$1');
// above was initial setup

$routes->get('login', 'Auth::index');
$routes->post('login/auth', 'Auth::login');
$routes->get('logout', 'Auth::logout');


$routes->get('contact', 'Contact::index');
$routes->post('contact/update', 'Contact::update', ['filter' => 'auth']);

$routes->get('about', 'About::index');
$routes->post('about/update', 'About::update', ['filter' => 'auth']);

