<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
  /**
   * Be sure to declare properties for any property fetch you initialized.
   * The creation of dynamic property is deprecated in PHP 8.2.
   */
  
  /**
   * @return void
   */
  public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
  {
    // Load here all helpers you want to be available in your controllers that extend BaseController.
    // Caution: Do not put the this below the parent::initController() call below.
    // $this->helpers = ['form', 'url'];
    
    // Caution: Do not edit this line.
    parent::initController($request, $response, $logger);
    
    // Preload any models, libraries, etc, here.
    // $this->session = service('session');
  }
  
  /**
   * Override view() to enforce using renderView() or renderNonPublicView()
   * @deprecated Use renderView() or renderNonPublicView() instead
   */
  final protected function view(string $name, array $data = [], array $options = [])
  {
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $caller = $trace[1]['function'] ?? '';
    
    if (!in_array($caller, ['renderView', 'renderNonPublicView'])) {
      throw new \BadMethodCallException('Direct use of view() is not allowed. Use renderView() or renderNonPublicView() instead.');
    }
    
    return view($name, $data, $options);
  }
  
  protected function renderView(string $view, array $required, array $page_specific = [])
  {
    $data = array_merge(['robots' => 'index,follow'], $required, $page_specific);
    
    if ($data['robots'] === 'index,follow') {
      $requiredKeys = [
        'title',
        'selected_menu_item',
        'description',
        'og_image',
        'og_image_width',
        'og_image_height',
      ];
    } else {
      $requiredKeys = [
        'title',
      ];
    }
    
    foreach ($requiredKeys as $key) {
      if (!isset($data[$key])) {
        throw new \InvalidArgumentException("Missing required view data: {$key}");
      }
    }
    return view($view, $data);
  }
  
  protected function renderNonPublicView(string $view, array $page_specific = [])
  {
    $non_public = [
      'selected_menu_item' => 'login',
      'robots' => 'noindex,nofollow'
    ];
    $data = array_merge($non_public, $page_specific);
    return $this->renderView($view, $data);
  }
}
