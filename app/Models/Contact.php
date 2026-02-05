<?php

namespace App\Models;

use CodeIgniter\Model;

class Contact extends Model
{
  protected $table            = 'contact'; // Table name from your SQL
  protected $primaryKey       = 'id';
  protected $allowedFields    = ['text'];  // The column you want to access
  protected $returnType       = 'array';
}