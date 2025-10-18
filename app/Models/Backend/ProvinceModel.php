<?php

namespace App\Models\Backend;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvinceModel extends Model
{
    use HasFactory;
    protected $table = 'tb_province';
    protected $primaryKey = 'id';
    protected $fillable = ['code','name_th','name_en','zone'];
    public $timestamp = false;
}
