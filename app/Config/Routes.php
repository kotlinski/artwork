<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('startpage/update', 'Home::update', ['filter' => 'auth']);
$routes->get('users', 'Home::index');
$routes->get('user/(:num)', 'Home::profile/$1');
// above was initial setup

$routes->get('news', 'News::index');
$routes->post('news/store', 'News::store', ['filter' => 'auth']);
$routes->post('news/update/(:num)', 'News::update/$1', ['filter' => 'auth']);
$routes->post('news/delete/(:num)', 'News::delete/$1', ['filter' => 'auth']);


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
  $routes->delete('delete/(:num)', 'Artwork::delete/$1');
  $routes->patch('move-up/(:num)', 'Artwork::moveUp/$1');
  $routes->patch('move-down/(:num)', 'Artwork::moveDown/$1');
  $routes->patch('publish/(:num)', 'Artwork::setPublished/$1');
});

$routes->group('image', ['filter' => 'auth'], function ($routes) {
    $routes->get('admin', 'ImageAdmin::admin');
    $routes->post('update/(:num)', 'ImageAdmin::update/$1');
    $routes->patch('move-up/(:num)', 'ImageAdmin::moveUp/$1');
    $routes->patch('move-down/(:num)', 'ImageAdmin::moveDown/$1');
    $routes->patch('reorder/(:num)', 'ImageAdmin::reorder/$1');
    $routes->post('upload', 'ImageAdmin::upload');
    $routes->post('delete/(:num)', 'ImageAdmin::delete/$1');
});


$routes->get('(:segment)/(:segment)', 'Project::imageDetail/$1/$2');
$routes->get('(:segment)', 'Project::detail/$1');

$routes->post('project/update', 'Project::update', ['filter' => 'auth']);

