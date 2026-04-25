<?php

namespace App\Models;

use CodeIgniter\Model;

class Startpage extends Model
{
    protected $table = 'startpage';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['text', 'image_path'];
    protected $useTimestamps = false;
}

