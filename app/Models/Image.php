<?php
namespace App\Models;

use CodeIgniter\Model;

class Image extends Model
{
    protected $table = 'images';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'datum',
        'updated_at',
        'date_created',
        'file_id',
        'file_name',
        'title',
        'alternate_name',
        'caption',
        'project',
        'artwork_filter',
        'order',
        'width_px',
        'height_px',
        'artform',
        'art_medium',
        'artwork_surface',
        'art_edition',
        'genre',
        'height_cm',
        'width_cm',
        'depth_cm',
        'geo_location',
        'address_locality',
        'address_region',
        'address_country',
        'map_url',
        'photographer_name',
        'slug',
    ];
    public $timestamps = false;
}
