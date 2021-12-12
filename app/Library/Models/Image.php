<?php namespace Image\Models;


use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    const HIDDEN = 3;
    const PRIVATE = 1;
    const PUBLIC = 2;
    protected $table = 'images';
}
