<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TambonModel extends Model
{
    use HasFactory;
    protected $table = 'tb_tambon';
    protected $primaryKey = 'id';
    protected $fillable = ['amupur_code','code','postcode','name_th','name_en'];

    public $timestamp = false;
}
