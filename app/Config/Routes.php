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

$routes->get('artwork', 'Artwork::index');
$routes->group('artwork', ['filter' => 'auth'], function ($routes) {
  $routes->get('admin', 'Artwork::admin');
  $routes->post('store', 'Artwork::store');
  $routes->get('edit/(:num)', 'Artwork::edit/$1');
  $routes->post('update/(:num)', 'Artwork::update/$1');
  $routes->get('delete/(:num)', 'Artwork::delete/$1');
  $routes->get('move-up/(:num)', 'Artwork::moveUp/$1');
  $routes->get('move-down/(:num)', 'Artwork::moveDown/$1');
});

$routes->group('image', ['filter' => 'auth'], function ($routes) {
    $routes->get('admin', 'ImageAdmin::admin');
    $routes->post('update/(:num)', 'ImageAdmin::update/$1');
    $routes->get('move-up/(:num)', 'ImageAdmin::moveUp/$1');
    $routes->get('move-down/(:num)', 'ImageAdmin::moveDown/$1');
});

$routes->get('(:segment)/(:segment)', 'Project::imageDetail/$1/$2');
$routes->get('(:segment)', 'Project::detail/$1');

$routes->post('project/update', 'Project::update', ['filter' => 'auth']);
