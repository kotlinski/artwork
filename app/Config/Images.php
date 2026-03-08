<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Images\Handlers\GDHandler;
use CodeIgniter\Images\Handlers\ImageMagickHandler;

class Images extends BaseConfig
{
  /**
   * Default handler used if no other handler is specified.
   */
  public string $defaultHandler = 'imagick';
  
  /**
   * The path to the image library.
   * Required for ImageMagick, GraphicsMagick, or NetPBM.
   */
  /*    public string $libraryPath = '/opt/homebrew/bin';*/
  public function __construct()
  {
    parent::__construct();
    
    // Check if we are in the local Homebrew environment
    if (is_dir('/opt/homebrew/bin')) {
      $this->libraryPath = '/opt/homebrew/bin';
    } // Otherwise, assume the standard Linux path used by one.com
    else {
      $this->libraryPath = '/usr/bin';
    }
  }
  
  /**
   * The available handler classes.
   *
   * @var array<string, string>
   */
  public array $handlers = [
    'gd' => GDHandler::class,
    'imagick' => ImageMagickHandler::class,
  ];
}
