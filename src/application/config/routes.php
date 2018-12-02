<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
// $route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
$route['login'] = 'login';
$route['login/delete/(:any)'] = 'login/delete/$1';
$route['login/create'] = 'login/create';
$route['login/logmein'] = 'login/logmein';
$route['login/logout'] = 'login/logout';


$route['news/create'] = 'news/create';
$route['news/hide/(:any)'] = 'news/hide/$1';
$route['news/show/(:any)'] = 'news/show/$1';
$route['news/delete/(:any)'] = 'news/delete/$1';
$route['news/update/(:any)'] = 'news/update/$1';
$route['news/(:any)'] = 'news/view/$1';
$route['news'] = 'news';

$route['album/(:any)'] = 'album/index/$1';
$route['album'] = 'album';


$route['about'] = 'about';
$route['about/create'] = 'about/create';
$route['contact'] = 'contact';
$route['contact/create'] = 'contact/create';

$route['startpage/create'] = 'startpage/create';


$route['image_admin/create'] = 'image_admin/create';
$route['image_admin/do_upload'] = 'image_admin/do_upload';
$route['image_admin/delete/(:any)'] = 'image_admin/delete/$1';
$route['image_admin/update/(:any)'] = 'image_admin/update/$1';
$route['image_admin/setFilter/(:any)'] = 'image_admin/setFilter/$1';
$route['image_admin/setOrder/(:any)'] = 'image_admin/setOrder/$1';
$route['image_admin/(:any)/(:any)'] = 'image_admin/view/$1/$2';
$route['image_admin'] = 'image_admin';

$route['default_controller'] = 'startpage';
$route['(:any)'] = 'startpage';

//$route['default_controller'] = "welcome";
//$route['404_override'] = '';


/* End of file routes.php */
/* Location: ./application/config/routes.php */
